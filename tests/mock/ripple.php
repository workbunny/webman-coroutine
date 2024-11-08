<?php

namespace Co;

use Mockery;

/**
 * @return \Revolt\EventLoop\Suspension
 */
function getSuspension(): \Revolt\EventLoop\Suspension
{
    $mock = Mockery::mock('alias:\Revolt\EventLoop\Suspension');
    $mock->shouldReceive('suspend')->andReturnNull();
    $mock->shouldReceive('resume')->andReturnNull();

    return $mock;
}

function delay(\Closure $closure, float $timeout)
{
    call_user_func($closure);
}

function defer(\Closure $closure)
{
    call_user_func($closure);
}
