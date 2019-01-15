<?php

namespace Vladimino\Discoverist\Error;

/**
 * Class SameTeamException
 *
 * @package Vladimino\Discoverist\Error
 */
class SameTeamException extends \LogicException
{
    const ERROR_MESSAGE = 'Как ты можешь бежать от самого себя? Куда бы ты ни пришёл, ты будешь с самим собой.';

    /**
     * SameTeamException constructor.
     */
    public function __construct()
    {
        parent::__construct(self::ERROR_MESSAGE);
    }
}
