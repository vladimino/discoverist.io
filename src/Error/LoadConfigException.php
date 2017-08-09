<?php

namespace Vladimino\Discoverist\Error;

/**
 * Class LoadConfigException
 *
 * @package Vladimino\Discoverist\Error
 */
class LoadConfigException extends \RuntimeException
{
    const ERROR_MESSAGE = 'Ошибка загрузки конфигурации, список турниров пуст';

    /**
     * LoadConfigException constructor.
     */
    public function __construct()
    {
        parent::__construct(self::ERROR_MESSAGE);
    }
}
