<?php

namespace Csvtool\Commands\Impl;

use Csvtool\Commands\Command;
use Csvtool\Commands\CommandDefinition;
use Csvtool\Models\CSVFile;
use Csvtool\Services\QuerySelectService;
use Exception;
use InvalidArgumentException;

class SelectCommand extends Command
{

    public static function getDefinition(): CommandDefinition
    {
        return new CommandDefinition(
            'select',
            "Select column from csv file and apply where clauses with the following format: or(age > 28, and(age = 28, name = 'John Doe'))",
            ['--file:', '--columns:', '--outfile', '--where']
        );
    }

    /**
     * @throws Exception
     */
    public function run(): void
    {
        try {
            $input = $this->fileService->open($this->args['file'], CSVFile::MODE_READ);
            $output = $this->fileService->open($this->args['outfile'], CSVFile::MODE_WRITE);

            $columns = explode(',', $this->args['columns']);
            $header = $input->getHeader();

            // Check if the required columns exist in the csv file
            foreach ($columns as $column) {
                if (!in_array($column, $header ?? [])) {
                    throw new InvalidArgumentException("Column $column not found");
                }
            }

            $qss = new QuerySelectService($columns);
            $root = $qss->parse($this->args['where'] ?? "");

            $output->setHeader($columns);
            foreach ($input->read() as $row) {
                if ($root !== null && $root->evaluate($row)) {
                    $output->write($qss->select($row));
                }
            }

        } catch (Exception $exception) {
            $this->fileService->closeAll();
            throw $exception;
        }
    }
}