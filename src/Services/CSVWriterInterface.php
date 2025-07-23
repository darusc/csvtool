<?php

namespace Csvtool\Services;

interface CSVWriterInterface
{
    public function open(string $filename, ?array $header = null): bool;

    /**
     * @param array $row Needs to be an array of values, without explicit keys
     * @return void
     */
    public function write(array $row): void;
}