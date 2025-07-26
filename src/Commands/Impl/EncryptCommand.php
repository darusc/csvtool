<?php

namespace Csvtool\Commands\Impl;

use Csvtool\Commands\Command;
use Csvtool\Models\CSVFile;
use Csvtool\Services\Cryptography\CryptographyService;
use Csvtool\Services\Cryptography\EncryptionService;
use Exception;
use InvalidArgumentException;

class EncryptCommand extends Command
{
    public static function getDefinition(): array
    {
        return [
            'name' => 'encrypt',
            'description' => 'Encrypt column in CSV file',
            'args' => ['file', 'column', 'publickey', 'outfile']
        ];
    }

    public function run(): void
    {
        try {
            $encryptionService = CryptographyService::forEncryption($this->args['publickey']);

            $input = $this->fileService->open($this->args['file'], CSVFile::MODE_READ);
            $output = $this->fileService->open($this->args['outfile'], CSVFile::MODE_WRITE);

            $header = $input->getHeader();
            if (!in_array($this->args['column'], $header ?? [])) {
                throw new InvalidArgumentException("Column {$this->args['column']} not found");
            }

            $output->setHeader($input->getHeader());

            foreach ($input->read() as $row) {
                $output->write(
                    array_map(function ($key) use ($encryptionService, $row) {
                        if ($key == $this->args['column']) {
                            return $encryptionService->encrypt($row[$key]);
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