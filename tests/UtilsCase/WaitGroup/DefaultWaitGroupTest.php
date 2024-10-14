<?php

declare(strict_types=1);

namespace Workbunny\Tests\UtilsCase\WaitGroup;

use Workbunny\Tests\TestCase;
use Workbunny\WebmanCoroutine\Utils\WaitGroup\Handlers\DefaultWaitGroup;

class DefaultWaitGroupTest extends TestCase
{
    public function testAdd()
    {
        $wg = new DefaultWaitGroup();
        $this->assertTrue($wg->add());
    }

    public function testDone()
    {
        $wg = new DefaultWaitGroup();
        $this->assertTrue($wg->done());
    }

    public function testCount()
    {
        $wg = new DefaultWaitGroup();
        $this->assertEquals(0, $wg->count());
    }

    public function testWait()
    {
        $wg = new DefaultWaitGroup();
        $wg->wait();
        $this->assertTrue(true);
    }
}
