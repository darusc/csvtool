<?php

namespace Csvtool\Commands\Impl;

use Csvtool\Commands\Command;

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
        if($this->reader->open($this->args['file'])) {

            if(!$this->reader->hasHeader()) {
                echo 'Input file ' . $this->args['file'] . " doesn't have a header" . PHP_EOL;
                return;
            }

            $header = $this->reader->getHeader();

            // Check what option was given and delete the column accordingly
            if(array_key_exists('index', $this->args)) {
                $index = (int)$this->args['index'];
                unset($header[$index]);
            }
            if(array_key_exists('name', $this->args)) {
                $name = $this->args['name'];
                $header = array_filter($header, fn($item) => $item !== $name);
            }

            if($this->writer->open($this->args['outfile'])) {
                // Write the new header
                $this->writer->write($header);

                foreach ($this->reader->read() as $row) {
                    // Write only the items whose keys are in updated header (after column removal)
                    $this->writer->write(
                        array_filter($row, function($value, $key) use ($header) {
                            return in_array($key, $header);
                        }, ARRAY_FILTER_USE_BOTH)
                    );
                }
            }
        }
    }
}