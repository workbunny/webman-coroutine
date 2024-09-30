<?php
/**
 * @author workbunny/Chaz6chez
 * @email chaz6chez1993@outlook.com
 */
declare(strict_types=1);

namespace Workbunny\WebmanCoroutine\Utils\Coroutine\Handlers;

use Swow\Coroutine;

class SwowCoroutine implements CoroutineInterface
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
        $coroutine = Coroutine::run($func);
        return (string)$coroutine->getId();
    }

    /** @inheritdoc  */
    public function query(string $id): ?Coroutine
    {
        return Coroutine::get((int)$id);
    }
}
