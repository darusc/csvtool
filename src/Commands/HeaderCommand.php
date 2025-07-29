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
    name: 'app:csv:header',
    description: 'Prepend header to file',
)]
class HeaderCommand extends CommandBase
{
    protected function configure(): void
    {
        $this
            ->addOption('file', 'f', InputOption::VALUE_REQUIRED, 'Input file')
            ->addOption('header', 'h', InputOption::VALUE_REQUIRED, 'Header to prepend to file')
            ->addOption('output', 'o', InputOption::VALUE_OPTIONAL, 'Output file', 'output.csv');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        try {
            $options = $this->extractOptions($input);

            $header = explode(',', $options['header']);

            // Open the input file
            $finput = $this->fileService->open($options['file'], CSVFile::MODE_READ);

            // Open the output file and write the header
            $foutput = $this->fileService->open($options['output'], CSVFile::MODE_WRITE);

            // Write the header and copy the contents
            $foutput->setHeader($header);
            foreach ($finput->read() as $row) {
                $foutput->write($row);
            }

        } catch (Exception $exception) {
            $this->fileService->closeAll();
            $io->error($exception->getMessage());

            return Command::FAILURE;
        }
        return Command::SUCCESS;
    }
}
