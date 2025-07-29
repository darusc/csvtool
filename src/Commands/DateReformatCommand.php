<?php

namespace Csvtool\Commands;

use Carbon\Carbon;
use Csvtool\Models\CSVFile;
use Csvtool\Validators\DateValidator;
use Exception;
use InvalidArgumentException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:csv:refdate',
    description: 'Reformat date column with provided format',
)]
class DateReformatCommand extends CommandBase
{

    protected function configure(): void
    {
        $this
            ->addOption('file', 'f', InputOption::VALUE_REQUIRED, 'Input file')
            ->addOption('column', 'c', InputOption::VALUE_REQUIRED, 'Column name')
            ->addOption('format', null, InputOption::VALUE_REQUIRED, 'New date format')
            ->addOption('output', 'o', InputOption::VALUE_OPTIONAL, 'Output file', 'output.csv');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        try {
            $options = $this->extractOptions($input);

            $finput = $this->fileService->open($options['file'], CSVFile::MODE_READ);
            $foutput = $this->fileService->open($options['output'], CSVFile::MODE_WRITE);

            if ($finput->hasHeader()) {
                $foutput->write($finput->getHeader());
            }

            $format = $options['format'];
            if (!DateValidator::isValidFormat($format)) {
                throw new InvalidArgumentException("Specified format '$format' is not a valid date format.");
            }

            $column = $options['column'];
            if (!in_array($column, $finput->getHeader())) {
                throw new InvalidArgumentException("Column '$column' not found in file.");
            }

            foreach ($finput->read() as $row) {
                $foutput->write(
                    array_map(function ($key) use ($format, $column, $row) {
                        // If the current value is a valid datetime reformat it with the new specified format
                        if ($key === $column && DateValidator::isValidDateTime($row[$key])) {
                            return Carbon::parse($row[$key])->format($format);
                        } else {
                            return $row[$key];
                        }
                    }, array_keys($row))
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
