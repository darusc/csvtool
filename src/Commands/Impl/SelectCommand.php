<?php

namespace Csvtool\Commands\Impl;

use Csvtool\Commands\Command;
use Csvtool\Exceptions\MetadataNotMatch;
use Csvtool\Models\CSVFile;
use Csvtool\Services\QuerySelectService;
use Exception;

class SelectCommand extends Command
{

    public static function getDefinition(): array
    {
        return [
            'name' => 'select',
            'description' => "Select column from csv file and apply where clauses with the following format: or(age > 28, and(age = 28, name = 'John Doe'))",
            'args' => ['from', 'columns', 'output', '--query']
        ];
    }

    public function run(): void
    {
        try {
            $input = $this->fileService->open($this->args['from'], CSVFile::MODE_READ);
            $output = $this->fileService->open($this->args['output'], CSVFile::MODE_WRITE);

            $columns = explode(',', $this->args['columns']);
            $header = $input->getHeader();

            // Check if the required columns exist in the csv file
            foreach ($columns as $column) {
                if(!in_array($column, $header ?? [])) {
                    throw new MetadataNotMatch("Column $column not found");
                }
            }

            $qss = new QuerySelectService($columns);
            $root = $qss->parse($this->args['query']);

            $output->setHeader($columns);
            foreach ($input->read() as $row) {
                if($root->evaluate($row)) {
                    $output->write($qss->select($row));
                }
            }

        } catch (Exception $exception) {
            $this->fileService->closeAll();
            echo $exception->getMessage();
        }
    }
}