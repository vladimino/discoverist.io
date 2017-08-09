<?php

namespace Vladimino\Discoverist\Error;

/**
 * Class TeamNotFound
 *
 * @package Vladimino\Discoverist\Error
 */
class TeamNotFoundException extends \InvalidArgumentException
{
    const ERROR_MESSAGE = 'Команда c ID %d не найдена!';

    /**
     * TeamNotFoundException constructor.
     *
     * @param int $teamId
     */
    public function __construct(int $teamId)
    {
        parent::__construct(\sprintf(self::ERROR_MESSAGE, $teamId));
    }
}
