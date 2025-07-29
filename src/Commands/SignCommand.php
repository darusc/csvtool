<?php

namespace Csvtool\Commands;

use Csvtool\Models\CSVFile;
use Csvtool\Services\Cryptography\SignatureService;
use Exception;
use InvalidArgumentException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:csv:sign',
    description: 'Sign column with provided private key',
)]
class SignCommand extends CommandBase
{
    protected function configure(): void
    {
        $this
            ->addOption('file', 'f', InputOption::VALUE_REQUIRED, 'Input file')
            ->addOption('column', 'c', InputOption::VALUE_REQUIRED, 'Column to decrypt')
            ->addOption('private-key', 'k', InputOption::VALUE_REQUIRED, 'Private key')
            ->addOption('output', 'o', InputOption::VALUE_OPTIONAL, 'Output file', 'output.csv');
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        try {
            $options = $this->extractOptions($input);

            $signatureService = SignatureService::forSigning($options['private-key']);

            $finput = $this->fileService->open($options['file'], CSVFile::MODE_READ);
            $foutput = $this->fileService->open($options['output'], CSVFile::MODE_WRITE);

            $header = $finput->getHeader();
            $column = $options['column'];
            if (!in_array($column, $header ?? [])) {
                throw new InvalidArgumentException("Column $column not found");
            }

            // Append a new column to hold the signature
            $header[] = $column . '_signature';
            $foutput->setHeader($header);

            foreach ($finput->read() as $row) {
                // Sign the corresponding row value and append it under the new column
                $signature = $signatureService->sign($row[$column]);
                $row[$column . '_signature'] = $signature;
                $foutput->write($row);
            }

        } catch (Exception $exception) {
            $this->fileService->closeAll();
            $io->error($exception->getMessage());

            return Command::FAILURE;
        }
        return Command::SUCCESS;
    }
}
