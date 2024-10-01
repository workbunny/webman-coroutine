<?php
/**
 * @author workbunny/Chaz6chez
 * @email chaz6chez1993@outlook.com
 */
declare(strict_types=1);

namespace Workbunny\WebmanCoroutine\Utils\Coroutine\Handlers;

class DefaultCoroutine implements CoroutineInterface
{
    /** @inheritdoc  */
    public function __construct(\Closure $func)
    {
        call_user_func($func);
    }

    /** @inheritdoc  */
    public function __destruct()
    {
    }

    /** @inheritdoc  */
    public function origin(): mixed
    {
        return null;
    }
}
