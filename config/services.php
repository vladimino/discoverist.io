<?php

use Pimple\Container;
use Vladimino\Discoverist\Model\Face2FaceModel;
use Vladimino\Discoverist\Model\ResultsModel;
use Vladimino\Discoverist\Rating\Connector;
use Vladimino\Discoverist\Rating\Geo;

//Core
$container['core.cache'] = function () {
    $memcached = new \Memcached;
    $memcached->addServer('/home/vladimin/.system/memcache/socket', 0);

    return $memcached;
};

// Rating
$container['rating.geo'] = function () {
    return new Geo();
};

$container['rating.connector'] = function (Container $container) {
    return new Connector($container['rating.geo'], $container['core.cache']);
};

// Models
$container['model.results'] = function (Container $container) {
    return new ResultsModel($container['rating.connector']);
};

$container['model.face2face'] = function (Container $container) {
    return new Face2FaceModel($container['rating.connector']);
};
