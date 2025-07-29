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
    name: 'app:csv:decrypt',
    description: 'Decrypt column with provided private key',
)]
class DecryptCommand extends CommandBase
{
    protected function configure(): void
    {
        $this
            ->addOption('file', 'f', InputOption::VALUE_REQUIRED, 'Input file')
            ->addOption('column', 'c', InputOption::VALUE_REQUIRED, 'Column to decrypt')
            ->addOption('private-key', 'k', InputOption::VALUE_REQUIRED, 'Private key')
            ->addOption('output', 'o', InputOption::VALUE_OPTIONAL, 'Output file', 'output.csv');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        try {
            $options = $this->extractOptions($input);

            $decryptionService = CryptographyService::forDecryption($options['private-key']);

            $finput = $this->fileService->open($options['file'], CSVFile::MODE_READ);
            $foutput = $this->fileService->open($options['output'], CSVFile::MODE_WRITE);

            $header = $finput->getHeader();
            if (!in_array($options['column'], $header ?? [])) {
                throw new InvalidArgumentException("Column {$options['column']} not found");
            }

            $foutput->setHeader($finput->getHeader());

            foreach ($finput->read() as $row) {
                $foutput->write(
                    array_map(function ($key) use ($decryptionService, $row, $options) {
                        if ($key == $options['column']) {
                            return $decryptionService->decrypt($row[$key]);
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
