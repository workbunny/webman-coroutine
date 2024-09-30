<?php
/**
 * @author workbunny/Chaz6chez
 * @email chaz6chez1993@outlook.com
 */
declare(strict_types=1);

namespace Workbunny\WebmanCoroutine\Utils\Coroutine\Handlers;

class RippleCoroutine implements CoroutineInterface
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
        $promise = \Co\async(function () use (&$promise, $func) {
            call_user_func($func);
            // 移除协程id及promise
            unset($this->_promise[spl_object_hash($promise)]);
        });
        return spl_object_hash($promise);
    }

    /** @inheritdoc  */
    public function query(string $id): mixed
    {
        return $this->_promise[$id] ?? null;
    }
}
