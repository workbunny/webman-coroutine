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

    /** @inheritdoc
     * @param \Closure $func
     */
    public function __construct(\Closure $func)
    {
        $this->_coroutine = Coroutine::run($func);
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
}
