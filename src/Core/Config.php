<?php

namespace Vladimino\Discoverist\Core;

class Config
{
    /** @todo yaml */
    public const CONFIG_EXTENSION = '.php';
    public const CONFIG_PATH = __DIR__.'/../../config/';

    /** @var array */
    public static $configRegistry = [];

    public static function get(string $configName): array
    {
        $configName = \strtolower($configName);
        $configPath = self::CONFIG_PATH.$configName.self::CONFIG_EXTENSION;

        /** @todo use proper config load */
        /** @psalm-suppress UnresolvableInclude */
        self::$configRegistry[$configName] = require $configPath;

        return (array)self::$configRegistry[$configName];
    }
}
