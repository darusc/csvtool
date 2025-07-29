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
    name: 'app:csv:join',
    description: 'Join CSV files',
)]
class JoinFilesCommand extends CommandBase
{

    protected function configure(): void
    {
        $this
            ->addOption('files', 'f', InputOption::VALUE_REQUIRED, 'The 2 input files')
            ->addOption('on', null, InputOption::VALUE_REQUIRED, 'On condition for joining')
            ->addOption('output', 'o', InputOption::VALUE_OPTIONAL, 'Output file', 'output.csv');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        try {
            $options = $this->extractOptions($input);

            $files = explode(',', $options["files"]);
            if (count($files) !== 2) {
                throw new InvalidArgumentException("2 files are need for join");
            }

            $on = explode(',', $options["on"]);
            if (count($on) !== 2) {
                throw new InvalidArgumentException("2 column names need to be specified for join");
            }

            $finput1 = $this->fileService->open($files[0], CSVFile::MODE_READ);
            $finput2 = $this->fileService->open($files[1], CSVFile::MODE_READ);

            if (!in_array($on[0], $finput1->getHeader()) || !in_array($on[1], $finput2->getHeader())) {
                throw new InvalidArgumentException("On column names not found");
            }

            $foutput = $this->fileService->open($options['output'], CSVFile::MODE_WRITE);

            // Merge the 2 headers together as the join will output all columns
            $header1 = $finput1->getHeader();
            $header2 = $finput2->getHeader();
            $foutput->setHeader([...$header1, ...$header2]);

            // Determine the largest and smallest files and update their join conditions
            // The smallest file is going to be $input1, the largest file will be $input2
            if ($finput1->getFileSize() > $finput2->getFileSize()) {
                [$finput1, $finput2] = [$finput2, $finput1];
                [$on[0], $on[1]] = [$on[1], $on[0]];
            }

            // Load the smaller file in a temporary file (php://temp) if its size is less than 2MB
            // If it is greater than 2MB it will be automatically made a temporary file on disk
            if ($finput1->getFileSize() < 2 * 1024 * 1024) {
                $tmp = $this->fileService->openTemporary(2 * 1024 * 1024);
                $tmp->setHeader($finput1->getHeader());
                foreach ($finput1->read() as $row) {
                    $tmp->write($row);
                }
                $finput1 = $tmp;
            }

            // Iterate sequentially over the largest file one time
            // and iterate multiple times over the smaller file
            // which is loaded in memory
            foreach ($finput2->read() as $row2) {
                // Seek to the line 1 to read again and skip line 0 which is the header
                $finput1->seek(1);
                foreach ($finput1->read() as $row1) {
                    if ($row1[$on[0]] == $row2[$on[1]]) {
                        $foutput->write([...$row1, ...$row2]);
                    }
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
