<?php
/**
 * This file is part of workbunny.
 *
 * Redistributions of files must retain the above copyright notice.
 *
 * @author    chaz6chez<chaz6chez1993@outlook.com>
 * @copyright chaz6chez<chaz6chez1993@outlook.com>
 * @link      https://github.com/workbunny/webman-push-server
 * @license   https://github.com/workbunny/webman-push-server/blob/main/LICENSE
 */
declare(strict_types=1);

return [
    // coroutine-web-server 开关
    'enable'        => true,
    // coroutine-web-server 监听端口
    'port'          => 8717,
    // 每stream回调的channel容量
    'channel_size'  => 1
];
