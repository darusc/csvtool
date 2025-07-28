<?php

namespace Csvtool\Commands\Impl;

use Csvtool\Commands\Command;
use Csvtool\Commands\CommandDefinition;
use Csvtool\Models\CSVFile;
use Csvtool\Services\Cryptography\SignatureService;
use Exception;
use http\Exception\InvalidArgumentException;

class VerifyCommand extends Command
{
    public static function getDefinition(): CommandDefinition
    {
        return new CommandDefinition(
            'verify',
            'Verify signature of given column in CSV file',
            ['--file:', '--column:', '--publickey:']
        );
    }

    public function run(): void
    {
        try {
            $signatureService = SignatureService::forVerification($this->args['publickey']);

            $input = $this->fileService->open($this->args['file'], CSVFile::MODE_READ);

            $header = $input->getHeader();
            $column = $this->args['column'];
            if (!in_array($column, $header ?? [])) {
                throw new InvalidArgumentException("Column $column not found");
            }

            if (!in_array($column . '_signature', $header ?? [])) {
                throw new InvalidArgumentException("Signature column $column\_signature not found");
            }

            foreach ($input->read() as $row) {
                $result = $signatureService->verify($row[$column], $row[$column . '_signature']);
                if ($result === false) {
                    echo "Incorrect signature!" . PHP_EOL;
                    return;
                }
            }

            echo "Correct signature!" . PHP_EOL;

        } catch (Exception $exception) {
            $this->fileService->closeAll();
            throw $exception;
        }
    }
}