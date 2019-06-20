<?php

use Pimple\Container;
use Vladimino\Discoverist\App;

require __DIR__ . '/../constants.php';
require __DIR__ . '/../vendor/autoload.php';

$container = new Container();
require __DIR__ . '/../config/services.php';

$app = new App($container, CONFIG_DIR);
$app->run();
