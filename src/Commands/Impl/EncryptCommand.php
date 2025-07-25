<?php

namespace Csvtool\Commands\Impl;

use Csvtool\Commands\Command;
use Csvtool\Services\Cryptography\CryptographyService;
use Csvtool\Services\Cryptography\EncryptionService;
use Exception;

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

            $header = $this->reader->getHeader();
            if (!in_array($this->args['column'], $header ?? [])) {
                echo "Column {$this->args['column']} not found" . PHP_EOL;
                return;
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

        } catch (Exception $e) {
            $this->fileService->closeAll();
            echo $e->getMessage() . PHP_EOL;
        }
    }
}