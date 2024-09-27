<?php
/**
 * @author workbunny/Chaz6chez
 * @email chaz6chez1993@outlook.com
 */
declare(strict_types=1);

namespace Workbunny\WebmanCoroutine\Handlers;

use Workbunny\WebmanCoroutine\CoroutineServerInterface;
use Workerman\Worker;
use function Co\await;
use function Co\async;
use function Workbunny\WebmanCoroutine\package_installed;

class RippleHandler implements HandlerInterface
{
    /** @inheritdoc  */
    public static function available(): bool
    {
        return package_installed('cclilshy/p-ripple-drive');
    }

    /** @inheritdoc  */
    public static function run(CoroutineServerInterface $app, mixed $connection, mixed $request): mixed
    {

        try {
            return await(
                async(function () use ($app, $connection, $request) {
                    return $app->parentOnMessage($connection, $request);
                })
            );
        } catch (\Throwable $e) {
            Worker::log($e->getMessage());
        }
        return null;
    }
}
