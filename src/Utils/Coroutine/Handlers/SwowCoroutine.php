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
     * @var null|Coroutine
     */
    protected ?Coroutine $_coroutine;

    /**
     * @var int
     */
    protected int $_id;

    /** @inheritDoc */
    public function __construct(\Closure $func)
    {
        $this->_coroutine = Coroutine::run(function () use ($func) {
            call_user_func($func, $this->_coroutine->getId());
        });
        $this->_id = $this->_coroutine->getId();
    }

    /** @inheritdoc  */
    public function __destruct()
    {
        $this->_coroutine = null;
    }

    /** @inheritdoc  */
    public function origin(): ?Coroutine
    {
        return $this->_coroutine;
    }

    /** @inheritdoc  */
    public function id(): int
    {
        return $this->_id;
    }
}
