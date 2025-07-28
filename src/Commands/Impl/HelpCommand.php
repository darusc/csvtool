<?php

namespace Csvtool\Commands\Impl;

use Csvtool\Application;
use Csvtool\Commands\Command;
use Csvtool\Commands\CommandDefinition;
use Csvtool\Commands\CommandDispatcher;

class HelpCommand extends Command
{

    public static function getDefinition(): CommandDefinition
    {
        return new CommandDefinition(
            'help',
            'Show this help message',
            []
        );
    }

    public function run(): void
    {
        echo "Usage: csvtool <action> <args...> [options...]" . PHP_EOL . PHP_EOL;

        foreach (CommandDispatcher::getCommandMap() as $action) {
            /** @var CommandDefinition $command */
            $command = $action::getDefinition();

            echo $command->name . ' - ' . $command->description . PHP_EOL;
            echo '    csvtool ' . $command->name;
            foreach ($command->args as $arg) {
                if (str_starts_with($arg, '--')) {
                    print ' [' . $arg . ']';
                } else {
                    print ' <' . $arg . '>';
                }
            }
            print PHP_EOL;
        }
    }
}