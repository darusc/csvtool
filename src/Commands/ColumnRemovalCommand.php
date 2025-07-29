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
    name: 'app:csv:rmcol',
    description: 'Remove column by index or name from file',
)]
class ColumnRemovalCommand extends CommandBase
{

    protected function configure(): void
    {
        $this
            ->addOption('file', 'f', InputOption::VALUE_REQUIRED, 'Input file')
            ->addOption('index', 'i', InputOption::VALUE_OPTIONAL, 'Index of column to remove')
            ->addOption('name', 'm', InputOption::VALUE_OPTIONAL, 'Name of column to remove')
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

            $header = $finput->getHeader();
            // Check what option was given and delete the column accordingly
            if (array_key_exists('index', $options)) {
                $index = (int)$options['index'];
                unset($header[$index]);
            }
            if (array_key_exists('name', $options)) {
                $name = $options['name'];
                $header = array_filter($header, fn($item) => $item !== $name);
            }

            $foutput->setHeader($header);
            foreach ($finput->read() as $row) {
                // Filter the row to remove the value corresponding to the deleted column
                $foutput->write(
                    array_filter($row, function ($value, $key) use ($header) {
                        return in_array($key, $header);
                    }, ARRAY_FILTER_USE_BOTH)
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
