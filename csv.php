<?php

require __DIR__ . '/vendor/autoload.php';

use \Csvtool\Application;

if ($argv[1] == '--help') {
    print_help(Application::$actionMap);
} else {
    try {
        $app = Application::create($argc, $argv);
        $app->run();
    } catch (Exception $e) {
        print "Error: " . $e->getMessage() . PHP_EOL;
        print "Usage: php csv.php <action> [options]" . PHP_EOL;
    }
}

function print_help(array $actions): void
{
    print "Usage: php csv.php <action> <args...> [options...]" . PHP_EOL . PHP_EOL;
    foreach ($actions as $action) {
        $command = $action::getDefinition();

        print $command['name'] . ' - ' . $command['description'] . PHP_EOL;
        print '    php csv.php' . $command['name'];
        foreach ($command['args'] as $arg) {
            if (str_starts_with($arg, '--')) {
                print ' [' . $arg . ']';
            } else {
                print ' ' . $arg;
            }
        }
        print PHP_EOL;
    }
}