<?php
/**
 * @author workbunny/Chaz6chez
 * @email chaz6chez1993@outlook.com
 */
declare(strict_types=1);

namespace Workbunny\WebmanCoroutine\Utils\Coroutine\Handlers;

use Swoole\Coroutine;

class SwooleCoroutine implements CoroutineInterface
{
    /**
     * @var array
     */
    protected array $_promise;

    /** @inheritdoc  */
    public function __construct()
    {
        $this->_promise = [];
    }

    /** @inheritdoc  */
    public function __destruct()
    {
        $this->_promise = [];
    }

    /** @inheritdoc  */
    public function create(\Closure $func): string
    {
        while (1) {
            if ($coroutine = Coroutine::create($func)) {
                break;
            }
        }
        return (string)$coroutine;
    }

    /** @inheritdoc  */
    public function query(string $id): bool
    {
        return false;
    }
}
