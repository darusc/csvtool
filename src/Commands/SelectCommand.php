<?php

namespace Csvtool\Commands;

use Csvtool\Models\CSVFile;
use Csvtool\Services\QuerySelectService;
use Exception;
use InvalidArgumentException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:csv:select',
    description: "Select from CSV file with where clause with the following format: or(age > 28, and(age = 28, name = 'John Doe'))",
)]
class SelectCommand extends CommandBase
{

    protected function configure(): void
    {
        $this
            ->addOption('file', 'f', InputOption::VALUE_REQUIRED, 'Input file')
            ->addOption('columns', 'c', InputOption::VALUE_REQUIRED, 'Selected columns')
            ->addOption('where', 'w', InputOption::VALUE_REQUIRED, 'Where clause')
            ->addOption('output', 'o', InputOption::VALUE_OPTIONAL, 'Output file', 'output.csv');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        try {
            $options = $this->extractOptions($input);

            $input = $this->fileService->open($options['file'], CSVFile::MODE_READ);
            $output = $this->fileService->open($options['output'], CSVFile::MODE_WRITE);

            $columns = explode(',', $options['columns']);
            $header = $input->getHeader();

            // Check if the required columns exist in the csv file
            foreach ($columns as $column) {
                if (!in_array($column, $header ?? [])) {
                    throw new InvalidArgumentException("Column $column not found");
                }
            }

            $qss = new QuerySelectService($columns);
            $root = $qss->parse($options['where'] ?? "");

            $output->setHeader($columns);
            foreach ($input->read() as $row) {
                if ($root !== null && $root->evaluate($row)) {
                    $output->write($qss->select($row));
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
