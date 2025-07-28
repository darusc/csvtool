<?php

namespace Csvtool\Commands;

use Csvtool\Services\CSVFileService;
use Exception;

abstract class Command
{
    public static function create(string $name, array $args, CSVFileService $fileService): static
    {
        return new $name($args, $fileService);
    }

    /**
     * Returns the command's definition
     */
    public abstract static function getDefinition(): CommandDefinition;

    protected function __construct(
        protected readonly array          $args,
        protected readonly CSVFileService $fileService
    )
    {

    }

    /**
     * @throws Exception
     */
    public abstract function run();
}