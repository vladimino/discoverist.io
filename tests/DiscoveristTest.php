<?php

namespace Vladimino\Discoverist\Tests;

use Pimple\Container;
use Vladimino\Discoverist\App;

/**
 * Class DiscoveristTest
 * @package Vladimino\Discoverist\Tests
 */
class DiscoveristTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test Example
     */
    public function testExample()
    {
        $container = $this->prophesize(Container::class);
        $app       = new App($container->reveal());
        $this->assertTrue(true);
    }
}
