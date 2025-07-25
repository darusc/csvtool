<?php

namespace Csvtool\Commands\Impl;

use Csvtool\Commands\Command;
use Csvtool\Models\CSVFile;
use Exception;
use InvalidArgumentException;

class JoinFilesCommand extends Command
{
    public static function getDefinition(): array
    {
        return [
            'name' => 'join',
            'description' => 'Join 2 files on column name',
            'args' => ['inputs', 'on', 'output']
        ];
    }

    public function run(): void
    {
        try {
            $files = explode(',', $this->args["inputs"]);
            if (count($files) !== 2) {
                throw new InvalidArgumentException("2 files are need for join");
            }

            $on = explode(',', $this->args["on"]);
            if (count($on) !== 2) {
                throw new InvalidArgumentException("2 column names need to be specified for join");
            }

            $input1 = $this->fileService->open($files[0], CSVFile::MODE_READ);
            $input2 = $this->fileService->open($files[1], CSVFile::MODE_READ);

            if (!in_array($on[0], $input1->getHeader()) || !in_array($on[1], $input2->getHeader())) {
                throw new InvalidArgumentException("On column names not found");
            }

            $output = $this->fileService->open($this->args['output'], CSVFile::MODE_WRITE);

            // Merge the 2 headers together as the join will output all columns
            $header1 = $input1->getHeader();
            $header2 = $input2->getHeader();
            $output->setHeader([...$header1, ...$header2]);

            // Determine the largest and smallest files and update their join conditions
            // The smallest file is going to be $input1, the largest file will be $input2
            if ($input1->getFileSize() > $input2->getFileSize()) {
                [$input1, $input2] = [$input2, $input1];
                [$on[0], $on[1]] = [$on[1], $on[0]];
            }

            // Load the smaller file in a temporary file (php://temp) if its size is less than 2MB
            // If it is greater than 2MB it will be automatically made a temporary file on disk
            if ($input1->getFileSize() < 2 * 1024 * 1024) {
                $tmp = $this->fileService->openTemporary(2 * 1024 * 1024);
                $tmp->setHeader($input1->getHeader());
                foreach ($input1->read() as $row) {
                    $tmp->write($row);
                }
                $input1 = $tmp;
            }

            // Iterate sequentially over the largest file one time
            // and iterate multiple times over the smaller file
            // which is loaded in memory
            foreach ($input2->read() as $row2) {
                // Seek to the line 1 to read again and skip line 0 which is the header
                $input1->seek(1);
                foreach ($input1->read() as $row1) {
                    if($row1[$on[0]] == $row2[$on[1]]) {
                        $output->write([...$row1, ...$row2]);
                    }
                }
            }

        } catch (Exception $e) {
            $this->fileService->closeAll();
            echo $e->getMessage();
        }
    }
}