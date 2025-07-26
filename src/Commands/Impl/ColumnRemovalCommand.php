<?php

namespace Csvtool\Commands\Impl;

use Csvtool\Commands\Command;
use Csvtool\Models\CSVFile;
use Exception;

class ColumnRemovalCommand extends Command
{

    public static function getDefinition(): array
    {
        return [
            'name' => 'rmcol',
            'description' => 'Remove column by name or index',
            'args' => ['file', 'outfile', '--index', '--name']
        ];
    }

    public function run(): void
    {
        try {
            $input = $this->fileService->open($this->args['file'], CSVFile::MODE_READ);
            $output = $this->fileService->open($this->args['outfile'], CSVFile::MODE_WRITE);

            if (!$input->hasHeader()) {
                throw new Exception('Input file ' . $this->args['file'] . " doesn't have a header");
            }

            $header = $input->getHeader();
            // Check what option was given and delete the column accordingly
            if (array_key_exists('index', $this->args)) {
                $index = (int)$this->args['index'];
                unset($header[$index]);
            }
            if (array_key_exists('name', $this->args)) {
                $name = $this->args['name'];
                $header = array_filter($header, fn($item) => $item !== $name);
            }

            $output->setHeader($header);
            foreach ($input->read() as $row) {
                // Filter the row to remove the value corresponding to the deleted column
                $output->write(
                    array_filter($row, function ($value, $key) use ($header) {
                        return in_array($key, $header);
                    }, ARRAY_FILTER_USE_BOTH)
                );
            }

        } catch (Exception $exception) {
            $this->fileService->closeAll();
            throw $exception;
        }
    }
}