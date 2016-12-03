<?php

return [
    'type'   => 'twig',
    'params' => [
        'views_path'    => realpath('../templates'),
        'compiled_path' => realpath('../cache/twig'),
        'debug'         => true,
    ],
];
