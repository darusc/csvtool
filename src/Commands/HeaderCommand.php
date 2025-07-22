<?php

namespace Csvtool\Commands;

final class HeaderCommand extends Command
{
    public static function getDefinition(): array
    {
        return [
            'name' => 'header',
            'description' => 'Prepend CSV file with a header row',
            'args' => ['file', 'header', '--new::']
        ];
    }

    public function run(): void
    {

    }
}