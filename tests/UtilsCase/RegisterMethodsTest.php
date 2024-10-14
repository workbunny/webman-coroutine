<?php

declare(strict_types=1);

namespace Workbunny\Tests\UtilsCase;

use Workbunny\Tests\TestCase;
use Workbunny\Tests\mock\TestRegisterMethods;

class RegisterMethodsTest extends TestCase
{
    public function testRegisterAndUnregister()
    {
        $result = TestRegisterMethods::register(__METHOD__, 'value');
        $this->assertTrue($result);
        $result = TestRegisterMethods::register(__METHOD__, 'value');
        $this->assertNull($result);
        $result = TestRegisterMethods::unregister(__METHOD__);
        $this->assertTrue($result);

        $result = TestRegisterMethods::register(__METHOD__, 123);
        $this->assertFalse($result);
    }

    public function testGetHandler()
    {
        TestRegisterMethods::register(__METHOD__, 'value');
        $handler = TestRegisterMethods::getHandler(__METHOD__);
        $this->assertEquals('value', $handler);
    }
}
