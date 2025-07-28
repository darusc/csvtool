<?php

namespace Csvtool\Commands\Impl;

use Csvtool\Commands\Command;
use Csvtool\Commands\CommandDefinition;
use Csvtool\Models\CSVFile;
use Exception;

class IndexCommand extends Command
{
    public static function getDefinition(): CommandDefinition
    {
        return new CommandDefinition(
            'index',
            'Add index column to CSV file',
            ['--file:', '--outfile']
        );
    }

    public function run(): void
    {
        try {
            $input = $this->fileService->open($this->args['file'], CSVFile::MODE_READ);
            $output = $this->fileService->open($this->args['outfile'], CSVFile::MODE_WRITE);

            $header = $input->getHeader();
            $output->setHeader(['id', ...($header ?? [])]);

            $id = 1;
            foreach ($input->read() as $row) {
                $output->write([$id++, ...$row]);
            }

        } catch (Exception $exception) {
            $this->fileService->closeAll();
            throw $exception;
        }
    }
}