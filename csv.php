<?php

require __DIR__ . '/vendor/autoload.php';

use \Csvtool\Application;

try {
    $app = Application::create($argc, $argv);
    $app->run();
} catch (InvalidArgumentException $e) {
    print "Error: " . $e->getMessage() . PHP_EOL;
    print "Usage: php csvtool.php <action> [options]\n";
}
