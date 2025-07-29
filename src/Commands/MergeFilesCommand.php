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
    name: 'app:csv:merge',
    description: 'Merge CSV files',
)]
class MergeFilesCommand extends CommandBase
{

    protected function configure(): void
    {
        $this
            ->addOption('files', 'f', InputOption::VALUE_REQUIRED, 'The 2 input files')
            ->addOption('index', 'i', InputOption::VALUE_NONE, 'Update index (id) column after merging')
            ->addOption('output', 'o', InputOption::VALUE_OPTIONAL, 'Output file', 'output.csv');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        try {
            $options = $this->extractOptions($input);

            $files = explode(',', $options["inputs"]);
            $inputs = [];
            // Open all the input files and check merge preconditions (same number of columns and matching headers)
            $header = [];
            $columns = -1;
            foreach ($files as $fileName) {
                $file = $this->fileService->open($fileName, CSVFile::MODE_READ);
                $inputs[] = $file;
                $cols = $file->getColumnCount();
                $h = $file->getHeader();

                // Stop if a file has a different number of columns than the rest
                if ($columns !== -1 && $cols !== $columns) {
                    throw new Exception("File $fileName has different number of columns.");
                } else {
                    $columns = $cols; // update column count initial value
                }

                // Stop if a file has a different header
                if ($header !== [] && $header !== $h) {
                    throw new Exception("File $fileName has different header.");
                } else {
                    $header = $h; // update initial header value
                }
            }

            $output = $this->fileService->open($options['output'], CSVFile::MODE_WRITE);
            $output->setHeader($header);

            // Merge all the input files by appending all the rows at the bottom of the output file
            $id = 1;
            foreach ($inputs as $input) {
                foreach ($input->read() as $row) {
                    // Update the index column if the option was given and if it exists in the csv file
                    if (array_key_exists('index', $options) && array_key_exists($options['index'], $row)) {
                        $row[$options['index']] = $id++;
                    }
                    $output->write($row);
                }
            }

        } catch (Exception $exception) {
            $this->fileService->closeAll();
            $io->error($exception->getMessage());

            return Command::FAILURE;
        }
        return Command::SUCCESS;
    }
}
