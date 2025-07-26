<?php

namespace Csvtool\Commands\Impl;

use Csvtool\Application;
use Csvtool\Commands\Command;

class HelpCommand extends Command
{

    public static function getDefinition(): array
    {
        return [
            'name' => 'help',
            'description' => 'Show this help message',
            'args' => []
        ];
    }

    public function run(): void
    {
        echo "Usage: php csv.php <action> <args...> [options...]" . PHP_EOL . PHP_EOL;
        foreach (Application::$actionMap as $action) {
            $command = $action::getDefinition();

            echo $command['name'] . ' - ' . $command['description'] . PHP_EOL;
            echo '    php csv.php ' . $command['name'];
            foreach ($command['args'] as $arg) {
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