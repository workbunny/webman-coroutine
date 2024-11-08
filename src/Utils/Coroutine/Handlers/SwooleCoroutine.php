<?php
/**
 * @author workbunny/Chaz6chez
 * @email chaz6chez1993@outlook.com
 */
declare(strict_types=1);

namespace Workbunny\WebmanCoroutine\Utils\Coroutine\Handlers;

use Swoole\Coroutine;
use Throwable;
use Workbunny\WebmanCoroutine\Handlers\SwooleHandler;

class SwooleCoroutine implements CoroutineInterface
{
    /**
     * @var int|null
     */
    protected ?int $_id = null;

    /** @inheritdoc */
    public function __construct(\Closure $func)
    {
        while (1) {
            if ($id = Coroutine::create(function () use ($func) {
                try {
                    $this->_id = Coroutine::getCid();
                    call_user_func($func, $this->_id);
                } finally {
                    $this->_id = null;
                }
            })) {
                $this->_id = $id;
                break;
            }
            // 保证协程切换
            SwooleHandler::sleep(rand(0, 2) / 1000);
        }
    }

    /** @inheritdoc  */
    public function __destruct()
    {
        $this->_id = null;
    }

    /** @inheritdoc  */
    public function origin(): ?int
    {
        return $this->_id;
    }

    /** @inheritdoc  */
    public function id(): ?int
    {
        return $this->_id;
    }

    /** @inheritdoc  */
    public function kill(Throwable $throwable): void
    {
        if ($this->id()) {
            // todo swoole目前没有throw方法，暂时只能静默退出
            Coroutine::cancel($this->id());
        }
    }
}
