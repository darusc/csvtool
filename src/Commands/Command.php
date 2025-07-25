<?php

namespace Csvtool\Commands;

use Csvtool\Services\CSVFileService;

abstract class Command implements CommandInterface
{
    public static function create(string $name, array $args, CSVFileService $fileService): static
    {
        return new $name($args, $fileService);
    }

    protected function __construct(
        protected readonly array          $args,
        protected readonly CSVFileService $fileService
    )
    {

    }
}