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
    name: 'app:csv:verify',
    description: 'Verify column signature',
)]
class VerifyCommand extends CommandBase
{
    protected function configure(): void
    {
        $this
            ->addOption('file', 'f', InputOption::VALUE_REQUIRED, 'Input file')
            ->addOption('column', 'c', InputOption::VALUE_REQUIRED, 'Column to decrypt')
            ->addOption('public-key', 'k', InputOption::VALUE_REQUIRED, 'Public key');
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        try {
            $options = $this->extractOptions($input);

            $signatureService = SignatureService::forVerification($options['public-key']);

            $finput = $this->fileService->open($options['file'], CSVFile::MODE_READ);

            $header = $finput->getHeader();
            $column = $options['column'];
            if (!in_array($column, $header ?? [])) {
                throw new InvalidArgumentException("Column $column not found");
            }

            if (!in_array($column . '_signature', $header ?? [])) {
                throw new InvalidArgumentException("Signature column $column\_signature not found");
            }

            foreach ($finput->read() as $row) {
                $result = $signatureService->verify($row[$column], $row[$column . '_signature']);
                if ($result === false) {
                    $io->error("Invalid signature! Column: $column");
                    return Command::FAILURE;
                }
            }

        } catch (Exception $exception) {
            $this->fileService->closeAll();
            $io->error($exception->getMessage());

            return Command::FAILURE;
        }

        $io->success("The signature has been verified!");
        return Command::SUCCESS;
    }
}
