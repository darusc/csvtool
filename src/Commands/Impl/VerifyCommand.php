<?php

namespace Csvtool\Commands\Impl;

use Csvtool\Commands\Command;
use Csvtool\Models\CSVFile;
use Csvtool\Services\Cryptography\SignatureService;
use Exception;

class VerifyCommand extends Command
{
    public static function getDefinition(): array
    {
        return [
            'name' => 'verify',
            'description' => 'Verify signature of given column in CSV file',
            'args' => ['file', 'column', 'publickey']
        ];
    }

    public function run(): void
    {
        try {
            $signatureService = SignatureService::forVerification($this->args['publickey']);

            $input = $this->fileService->open($this->args['file'], CSVFile::MODE_READ);

            $header = $input->getHeader();
            $column = $this->args['column'];
            if (!in_array($column, $header ?? [])) {
                echo "Column $column not found" . PHP_EOL;
                return;
            }

            if (!in_array($column . '_signature', $header ?? [])) {
                echo "Signature column $column\_signature not found" . PHP_EOL;
                return;
            }

            foreach ($input->read() as $row) {
                $result = $signatureService->verify($row[$column], $row[$column . '_signature']);
                if ($result === false) {
                    echo "Incorrect signature!" . PHP_EOL;
                    return;
                }
            }

            echo "Correct signature!" . PHP_EOL;

        } catch (Exception $e) {
            $this->fileService->closeAll();
            echo $e->getMessage() . PHP_EOL;
        }
    }
}