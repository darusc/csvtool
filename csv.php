<?php

require __DIR__ . '/vendor/autoload.php';

use \Csvtool\Application;
use Csvtool\Exceptions\InvalidActionException;
use Csvtool\Exceptions\MissingArgumentException;

try {
    $app = Application::create($argc, $argv);
    $app->run();
} catch (InvalidActionException $e) {
    echo "Action {$e->getAction()} is not supported. See help for more details" . PHP_EOL;
} catch (MissingArgumentException $e) {
    echo $e->getMessage() . " See help for more details" . PHP_EOL;
} catch (Exception $e) {
    echo "in main: " . $e->getMessage() . PHP_EOL;
}