<?php

namespace Vladimino\Discoverist\Error;

/**
 * Class SameTeamException
 * @package Vladimino\Discoverist\Error
 */
class SameTeamException extends \Exception
{
    protected $message = 'Как ты можешь бежать от самого себя? Куда бы ты ни пришёл, ты будешь с самим собой.';
}
