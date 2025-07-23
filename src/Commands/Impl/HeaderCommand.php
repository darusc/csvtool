<?php

namespace Csvtool\Commands\Impl;

use Csvtool\Commands\Command;

final class HeaderCommand extends Command
{
    public static function getDefinition(): array
    {
        return [
            'name' => 'header',
            'description' => 'Prepend CSV file with a header row',
            'args' => ['file', 'header', 'outfile']
        ];
    }

    public function run(): void
    {
        // Open the input file
        if($this->reader->open($this->args['file'])) {

            // Open the output file and write the header
            $header = explode(',', $this->args['header']);
            if($this->writer->open($this->args['outfile'], $header)) {

                // Copy the content from the input file to the output file
                foreach ($this->reader->read() as $row) {
                    $this->writer->write($row);
                }
            }
        }
    }
}