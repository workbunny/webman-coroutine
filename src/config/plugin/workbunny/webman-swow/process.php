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

use support\Log;
use Webman\Http\Request;
use Workbunny\WebmanSwow\SwowWebServer;

return config('plugin.workbunny.webman-swow.app.enable', false) ? [
    'swow-web-server' => [
        'handler'     => SwowWebServer::class,
        'listen'      => 'http://[::]:' . config('plugin.workbunny.webman-swow.app.port', 8717),
        'count'       => cpu_count(),
        'user'        => '',
        'group'       => '',
        'reusePort'   => true,
        'constructor' => [
            'request_class' => Request::class,
            'logger'        => Log::channel(), // 日志实例
            'app_path'      => app_path(), // app目录位置
            'public_path'   => public_path(), // public目录位置
        ],
    ],
] : [];
