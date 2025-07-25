<?php

namespace Csvtool\Commands\Impl;

use Csvtool\Commands\Command;
use Csvtool\Models\CSVFile;
use Csvtool\Services\Cryptography\SignatureService;
use Exception;

class SignCommand extends Command
{

    public static function getDefinition(): array
    {
        return [
            'name' => 'sign',
            'description' => 'Sign given column in CSV file',
            'args' => ['file', 'column', 'privatekey', 'outfile']
        ];
    }

    public function run(): void
    {
        try {
            $signatureService = SignatureService::forSigning($this->args['privatekey']);

            $input = $this->fileService->open($this->args['file'], CSVFile::MODE_READ);
            $output = $this->fileService->open($this->args['outfile'], CSVFile::MODE_WRITE);

            $header = $input->getHeader();
            $column = $this->args['column'];
            if (!in_array($column, $header ?? [])) {
                echo "Column $column not found" . PHP_EOL;
                return;
            }

            // Append a new column to hold the signature
            $header[] = $column . '_signature';
            $output->setHeader($header);

            foreach ($input->read() as $row) {
                // Sign the corresponding row value and append it under the new column
                $signature = $signatureService->sign($row[$column]);
                $row[$column . '_signature'] = $signature;
                $output->write($row);
            }

        } catch (Exception $e) {
            $this->fileService->closeAll();
            echo $e->getMessage() . PHP_EOL;
        }
    }
}