<?php
/**
 * @author workbunny/Chaz6chez
 * @email chaz6chez1993@outlook.com
 */
declare(strict_types=1);

namespace Workbunny\WebmanSwow;

use Swow\Channel;
use Swow\Coroutine;
use Swow\Sync\WaitGroup;
use Throwable;
use Webman\App;
use Webman\Route;
use Workerman\Worker;

class SwowWebServer extends App
{
    public function onWorkerStart($worker)
    {
        if ($worker instanceof Worker) {
            if ($worker::$globalEvent !== SwowEvent::class) {
                throw new \RuntimeException('Non-support event ' . $worker::$globalEvent);
            }
        }
        parent::onWorkerStart($worker);
    }

    public function onMessage($connection, $request)
    {
        $requestChannel = new Channel(1);
        $requestChannel->push([
            $connection,
            $request,
        ]);
        $waitGroup = new WaitGroup();
        $waitGroup->add();
        Coroutine::run(function () use ($requestChannel, $waitGroup) {
            while (1) {
                if (!$data = $requestChannel->pop()) {
                    break;
                }
                [$connection, $request] = $data;
                try {
                    $path = $request->path();
                    $key = $request->method() . $path;
                    if (isset(static::$callbacks[$key])) {
                        [$callback, $request->plugin, $request->app, $request->controller, $request->action, $request->route] = static::$callbacks[$key];
                        static::send($connection, call_user_func($callback, $request), $request);

                        return null;
                    }

                    $status = 200;
                    if (
                        static::unsafeUri($connection, $path, $request) ||
                        static::findFile($connection, $path, $key, $request) ||
                        static::findRoute($connection, $path, $key, $request, $status)
                    ) {
                        return null;
                    }

                    $controllerAndAction = static::parseControllerAction($path);
                    $plugin = $controllerAndAction['plugin'] ?? static::getPluginByPath($path);
                    if (!$controllerAndAction || Route::hasDisableDefaultRoute($plugin)) {
                        $request->plugin = $plugin;
                        $callback = static::getFallback($plugin);
                        $request->app = $request->controller = $request->action = '';
                        static::send($connection, call_user_func($callback, $request), $request);

                        return null;
                    }
                    $app = $controllerAndAction['app'];
                    $controller = $controllerAndAction['controller'];
                    $action = $controllerAndAction['action'];
                    $callback = static::getCallback($plugin, $app, [$controller, $action]);
                    static::collectCallbacks($key, [$callback, $plugin, $app, $controller, $action, null]);
                    [$callback, $request->plugin, $request->app, $request->controller, $request->action, $request->route] = static::$callbacks[$key];
                    static::send($connection, call_user_func($callback, $request), $request);
                } catch (Throwable $e) {
                    static::send($connection, static::exceptionResponse($e, $request), $request);
                } finally {
                    $waitGroup->done();
                }
            }
            $waitGroup->done();
        });
        $waitGroup->wait();

        // 交还控制权给event-loop
        return null;
    }
}
