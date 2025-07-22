<?php

namespace Csvtool\Commands;

interface ICommand
{
    /**
     * Return an array with the following structure
     * [
     *     'name' => 'command name',
     *     'description' => 'command description',
     *     'args' => [arg1, arg2, --arg3]
     * ]
     * Arguments prefixed with -- are optional.
     * Whether they require or not a value is decide similar to getopt function
     * https://www.php.net/manual/en/function.getopt.php
     */
    public static function getDefinition(): array;

    public function run();
}