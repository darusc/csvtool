<?php

namespace Csvtool\Commands\Impl;

use Csvtool\Commands\Command;
use Csvtool\Commands\CommandDefinition;
use Csvtool\Models\CSVFile;
use Csvtool\Services\Cryptography\CryptographyService;
use Csvtool\Services\Cryptography\DecryptionService;
use Exception;
use InvalidArgumentException;

class DecryptCommand extends Command
{
    public static function getDefinition(): CommandDefinition
    {
        return new CommandDefinition(
            'decrypt',
            'decrypt column in CSV file',
            ['--file:', '--column:', '--privatekey:', '--outfile']
        );
    }

    public function run(): void
    {
        try {
            $decryptionService = CryptographyService::forDecryption($this->args['privatekey']);

            $input = $this->fileService->open($this->args['file'], CSVFile::MODE_READ);
            $output = $this->fileService->open($this->args['outfile'], CSVFile::MODE_WRITE);

            $header = $input->getHeader();
            if (!in_array($this->args['column'], $header ?? [])) {
                throw new InvalidArgumentException("Column {$this->args['column']} not found" );
            }

            $output->setHeader($input->getHeader());

            foreach ($input->read() as $row) {
                $output->write(
                    array_map(function ($key) use ($decryptionService, $row) {
                        if ($key == $this->args['column']) {
                            return $decryptionService->decrypt($row[$key]);
                        } else {
                            return $row[$key];
                        }
                    }, array_keys($row))
                );
            }

        } catch (Exception $exception) {
            $this->fileService->closeAll();
            throw $exception;
        }
    }
}