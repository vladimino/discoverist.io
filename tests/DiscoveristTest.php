<?php

namespace Vladimino\Discoverist\Tests;

use Vladimino\Discoverist\App;

class DiscoveristTest extends \PHPUnit_Framework_TestCase
{
    public function testExample()
    {
        $app = new App();
        $app->hello();

        $this->expectOutputString("Discoverist. The only place to find Composer virtual packages.");
    }
}
