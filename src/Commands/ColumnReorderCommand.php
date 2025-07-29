<?php

namespace Csvtool\Commands;

use Csvtool\Models\CSVFile;
use Exception;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:csv:reorder',
    description: 'Reorder columns based on given sequence',
)]
class ColumnReorderCommand extends CommandBase
{
    protected function configure(): void
    {
        $this
            ->addOption('file', 'f', InputOption::VALUE_REQUIRED, 'Input file')
            ->addOption('sequence', 's', InputOption::VALUE_REQUIRED, 'Sequence of ordered columns')
            ->addOption('removenotinseq', 'r', InputOption::VALUE_NONE, 'Remove columns that are not in the given sequence')
            ->addOption('output', 'o', InputOption::VALUE_OPTIONAL, 'Output file', 'output.csv');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        try {
            $options = $this->extractOptions($input);

            $finput = $this->fileService->open($options['file'], CSVFile::MODE_READ);
            $foutput = $this->fileService->open($options['output'], CSVFile::MODE_WRITE);

            if (!$finput->hasHeader()) {
                throw new Exception('Input file ' . $options['file'] . " doesn't have a header");
            }

            $oldHeader = $finput->getHeader();
            $sequence = explode(',', $options['sequence']);

            // Create a new array containing the existing header columns in the required order
            // The reference sequence may contain additional columns
            $ordered = array_filter($sequence, function ($item) use ($oldHeader) {
                return in_array($item, $oldHeader);
            });

            // Append the columns that were not found in the reference sequence at the end
            // if not disabled by the removenotinseq option
            if (!array_key_exists('removenotinseq', $options)) {
                foreach ($oldHeader as $value) {
                    if (!in_array($value, $ordered)) {
                        $ordered[] = $value;
                    }
                }
            }

            $foutput->setHeader($ordered);
            foreach ($finput->read() as $row) {
                // Create a new array that has the values in the new required order by mapping the
                // ordered array values to the row data. (The keys of the $row array are the column names)
                $foutput->write(
                    array_map(function ($item) use ($row) {
                        return $row[$item];
                    }, $ordered)
                );
            }

        } catch (Exception $exception) {
            $this->fileService->closeAll();
            $io->error($exception->getMessage());

            return Command::FAILURE;
        }
        return Command::SUCCESS;
    }
}
