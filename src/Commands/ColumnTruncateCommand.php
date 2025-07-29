<?php

namespace Csvtool\Commands;

use Csvtool\Models\CSVFile;
use Exception;
use InvalidArgumentException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:csv:truncate',
    description: 'RTruncate string column values to given length',
)]
class ColumnTruncateCommand extends CommandBase
{
    protected function configure(): void
    {
        $this
            ->addOption('file', 'f', InputOption::VALUE_REQUIRED, 'Input file')
            ->addOption('column', 'c', InputOption::VALUE_REQUIRED, 'Column to truncate')
            ->addOption('length', 'l', InputOption::VALUE_REQUIRED, 'Length to truncate to')
            ->addOption('output', 'o', InputOption::VALUE_OPTIONAL, 'Output file', 'output.csv')
            ->addOption('ellipsis', null, InputOption::VALUE_NONE, 'Add ... after truncated value');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        try {
            $options = $this->extractOptions($input);

            $finput = $this->fileService->open($options['file'], CSVFile::MODE_READ);
            $foutput = $this->fileService->open($options['output'], CSVFile::MODE_WRITE);

            if ($finput->hasHeader()) {
                $foutput->setHeader($finput->getHeader());
            }

            $length = $options['length'];
            if (!ctype_digit($length)) {
                throw new InvalidArgumentException('Argument "length" must be an integer');
            }

            $column = $options['column'];
            if (!in_array($column, $finput->getHeader())) {
                throw new InvalidArgumentException("Column '$column' not found in file.");
            }

            foreach ($finput->read() as $row) {
                $foutput->write(
                    array_map(function ($key) use ($row, $column, $length, $options) {
                        if ($key !== $column) {
                            return $row[$key];
                        }

                        // Truncate string values to given length and ignore the rest
                        // and add ... if option was specified
                        if (is_string($row[$key]) && !is_numeric($row[$key])) {
                            if (array_key_exists('ellipsis', $options)) {
                                return substr($row[$key], 0, $length) . '..';
                            } else {
                                return substr($row[$key], 0, $length);
                            }
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
