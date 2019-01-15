<?php

require __DIR__ . '/../constants.php';
require __DIR__ . '/../vendor/autoload.php';

$container = new \Pimple\Container();
require __DIR__ . '/../config/services.php';

$app = new \Vladimino\Discoverist\App($container);
$app->run();
