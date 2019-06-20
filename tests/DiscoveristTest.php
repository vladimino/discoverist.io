<?php

namespace Vladimino\Discoverist\Tests;

use PHPUnit\Framework\TestCase;
use Pimple\Container;
use Vladimino\Discoverist\App;

class DiscoveristTest extends TestCase
{
    public function testExample()
    {
        $container = $this->prophesize(Container::class);
        $app       = new App($container->reveal(), 'aaa');
        $this->doesNotPerformAssertions();
    }
}
