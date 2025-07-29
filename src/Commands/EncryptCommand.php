<?php

namespace Csvtool\Commands;

use Csvtool\Models\CSVFile;
use Csvtool\Services\Cryptography\CryptographyService;
use Exception;
use InvalidArgumentException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:csv:encrypt',
    description: 'Encrypt column with provided public key',
)]
class EncryptCommand extends CommandBase
{
    protected function configure(): void
    {
        $this
            ->addOption('file', 'f', InputOption::VALUE_REQUIRED, 'Input file')
            ->addOption('column', 'c', InputOption::VALUE_REQUIRED, 'Column to encrypt')
            ->addOption('public-key', 'k', InputOption::VALUE_REQUIRED, 'Public key')
            ->addOption('output', 'o', InputOption::VALUE_OPTIONAL, 'Output file', 'output.csv');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        try {
            $options = $this->extractOptions($input);

            $encryptionService = CryptographyService::forEncryption($options['public-key']);

            $finput = $this->fileService->open($options['file'], CSVFile::MODE_READ);
            $foutput = $this->fileService->open($options['output'], CSVFile::MODE_WRITE);

            $header = $finput->getHeader();
            if (!in_array($options['column'], $header ?? [])) {
                throw new InvalidArgumentException("Column {$options['column']} not found");
            }

            $foutput->setHeader($finput->getHeader());

            foreach ($finput->read() as $row) {
                $foutput->write(
                    array_map(function ($key) use ($encryptionService, $row, $options) {
                        if ($key == $options['column']) {
                            return $encryptionService->encrypt($row[$key]);
                        } else {
                            return $row[$key];
                        }
                    }, array_keys($row))
                );
            }

        } catch (Exception $exception) {
            $this->fileService->closeAll();
            $io->error($exception->getMessage());

            return Command::FAILURE;
        }
        return Command::SUCCESS;
    }
}
