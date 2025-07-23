<?php

namespace Csvtool\Services;

interface CSVWriterInterface
{
    public function open(string $filename, ?array $header = null): bool;
    public function write(array $row);
}