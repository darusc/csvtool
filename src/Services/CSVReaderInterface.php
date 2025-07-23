<?php

namespace Csvtool\Services;

use Generator;

interface CSVReaderInterface
{
    public function open(string $filename): bool;
    public function hasHeader(): bool;
    public function getHeader(): ?array;
    public function read(): Generator;
}