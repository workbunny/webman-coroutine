<?php

declare(strict_types=1);

function set_config(string $key, mixed $value)
{
    global $configData;
    $configData[$key] = $value;
}

function config(?string $key = null, $default = null)
{
    global $configData;
    return $key === null ? ($configData ?: $default) : ($configData[$key] ?? $default);
}

function set_stream_poll_one_return(int $return)
{
    global $streamPollOneReturn;
    $streamPollOneReturn = $return;
}

function stream_poll_one($fd, $int)
{
    global $streamPollOneReturn;
    if ($streamPollOneReturn === -1) {
        throw new Exception('测试异常');
    }
    return $streamPollOneReturn ?: 0;
}