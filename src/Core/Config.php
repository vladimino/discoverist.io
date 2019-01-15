<?php

namespace Vladimino\Discoverist\Core;

/**
 * Class Config loads and registers configuration files.
 */
class Config
{
    /** @todo yaml */
    const CONFIG_EXTENSION = '.php';

    /** @var array */
    public static $configRegistry = [];

    /**
     * @param string $configName
     *
     * @return array
     */
    public static function get(string $configName): array
    {
        $configName = \strtolower($configName);

        /** @todo use proper config load */
        /** @noinspection PhpIncludeInspection */
        self::$configRegistry[$configName] = require \CONFIG_DIR . $configName . self::CONFIG_EXTENSION;

        return self::$configRegistry[$configName];
    }
}
