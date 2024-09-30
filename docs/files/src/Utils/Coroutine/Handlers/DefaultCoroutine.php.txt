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
    public function __construct()
    {
    }

    /** @inheritdoc  */
    public function __destruct()
    {
    }

    /** @inheritdoc  */
    public function create(\Closure $func): string
    {
        call_user_func($func);
        return 'coroutine_id';
    }

    /** @inheritdoc  */
    public function query(string $id): bool
    {
        return false;
    }
}
