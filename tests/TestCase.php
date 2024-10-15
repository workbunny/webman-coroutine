<?php

declare(strict_types=1);

namespace Workbunny\Tests;

use Workbunny\WebmanCoroutine\Handlers\DefaultHandler;

class TestCase extends \PHPUnit\Framework\TestCase
{
    protected function tearDown(): void
    {
        parent::tearDown();
        /*
         * @see DefaultHandler::$debug
         */
        DefaultHandler::$debug = true;
    }

    /**
     * @afterClass
     */
    public static function tearDownAfterClass(): void
    {
        /*
         * @see DefaultHandler::$debug
         */
        DefaultHandler::$debug = true;
    }
}
