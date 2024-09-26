<?php
/**
 * @author workbunny/Chaz6chez
 * @email chaz6chez1993@outlook.com
 */
declare(strict_types=1);

namespace Workbunny\WebmanSwow\Handlers;

use Webman\App;

class DefaultHandler implements HandlerInterface
{
    /** @inheritdoc  */
    public static function run(App $app, mixed $connection, mixed $request): mixed
    {
        return $app->onMessage($connection, $request);
    }

    /** @inheritdoc  */
    public static function available(): bool
    {
        return true;
    }
}
