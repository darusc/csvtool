<?php

namespace Csvtool\Commands;

interface CommandInterface
{
    /**
     * Return an array with the following structure
     * [
     *     'name' => 'command name',
     *     'description' => 'command description',
     *     'args' => [arg1, arg2, --arg3]
     * ]
     * Arguments prefixed with -- are optional.
     */
    public static function getDefinition(): array;

    public function run();
}