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

            if ($this->reader->open($this->args['file'])) {

                $header = $this->reader->getHeader();
                if (!in_array($this->args['column'], $header ?? [])) {
                    echo "Column {$this->args['column']} not found" . PHP_EOL;
                    return;
                }

                if ($this->writer->open($this->args['outfile'])) {

                    $this->writer->write($this->reader->getHeader());

                    foreach ($this->reader->read() as $row) {
                        $this->writer->write(
                            array_map(function ($key) use ($encryptionService, $row) {
                                if ($key == $this->args['column']) {
                                    return $encryptionService->encrypt($row[$key]);
                                } else {
                                    return $row[$key];
                                }
                            }, array_keys($row))
                        );
                    }
                }
            }
        } catch (Exception $e) {
            echo $e->getMessage() . PHP_EOL;
        }
    }
}