<?php

namespace Csvtool\Commands;

use Csvtool\Services\CSVReaderInterface;
use Csvtool\Services\CSVWriterInterface;

abstract class Command implements CommandInterface
{
    public static function create(string $name, array $args, CSVReaderInterface $reader, CSVWriterInterface $writer): static
    {
        return new $name($args, $reader, $writer);
    }

    protected function __construct(
        protected readonly array              $args,
        protected readonly CSVReaderInterface $reader,
        protected readonly CSVWriterInterface $writer
    )
    {

    }
}