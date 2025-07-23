<?php

namespace Csvtool\Commands\Impl;

use Csvtool\Commands\Command;

class IndexCommand extends Command
{
    public static function getDefinition(): array
    {
        return [
            'name' => 'index',
            'description' => 'Add index column to CSV file',
            'args' => ['file', 'outfile']
        ];
    }

    public function run(): void
    {
        if ($this->reader->open($this->args['file'])) {
            if ($this->writer->open($this->args['outfile'])) {

                if($this->reader->hasHeader()) {
                    // Get the original header of the file and
                    // add the new index column at the beginning
                    $header = $this->reader->getHeader();
                    $this->writer->write(['id', ...$header]);
                } else {
                    // Otherwise add just the index column header
                    $this->writer->write(['id']);
                }

                $id = 1;
                foreach ($this->reader->read() as $row) {
                    $this->writer->write([$id++, ...$row]);
                }
            }
        }
    }
}