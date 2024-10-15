<?php
/**
 * @author workbunny/Chaz6chez
 * @email chaz6chez1993@outlook.com
 */
declare(strict_types=1);

namespace Workbunny\WebmanCoroutine\Events;

use Swow\Coroutine;
use Swow\Signal;
use Swow\SignalException;
use Swow\Sync\WaitGroup;
use Workbunny\WebmanCoroutine\Exceptions\EventLoopException;
use Workerman\Events\EventInterface;

class SwowEvent implements EventInterface
{
    /**
     * @codeCoverageIgnore
     * @var bool
     */
    public static $debug = false;

    /** @var Coroutine[] All listeners for read event. */
    protected array $_reads = [];

    /** @var Coroutine[] All listeners for write event. */
    protected array $_writes = [];

    /** @var Coroutine[] Event listeners of signal. */
    protected array $_signals = [];

    /** @var Coroutine[] Timer id to timer info. */
    protected array $_timer = [];

    /** @var int 定时器id */
    protected int $_timerId = 1;

    /** @var WaitGroup|null 阻塞 */
    protected null|WaitGroup $_waitGroup = null;

    /**
     * @throws EventLoopException 如果没有启用拓展
     */
    public function __construct()
    {
        if (!self::$debug and !extension_loaded('swow')) {
            throw new EventLoopException('Not support ext-swow. ');
        }
    }

    /** @inheritdoc  */
    public function add($fd, $flag, $func, $args = [])
    {
        switch ($flag) {
            case EventInterface::EV_SIGNAL:
                if (!isset($this->_signals[$fd])) {
                    $this->_signals[$fd] = Coroutine::run(function () use ($fd, $func, $args): void
                    {
                        try {
                            Signal::wait($fd);
                            \call_user_func($func, $fd, ...$args);
                        }
                        // @codeCoverageIgnoreStart
                        catch (SignalException) {}
                        // @codeCoverageIgnoreEnd
                    });

                    return true;
                }

                return false;
            case EventInterface::EV_TIMER:
            case EventInterface::EV_TIMER_ONCE:
                $timerId = $this->_timerId++;
                $this->_timer[$timerId] = Coroutine::run(function () use ($timerId, $fd, $flag, $func, $args): void
                {
                    while (1) {
                        usleep((int) ($fd * 1000 * 1000));
                        \call_user_func($func, ...$args);
                        if ($flag === EventInterface::EV_TIMER_ONCE) {
                            $this->del($timerId, $flag);
                        }
                        // @codeCoverageIgnoreStart
                        if (self::$debug) {
                            break;
                        }
                        // @codeCoverageIgnoreEnd
                    }
                });

                return $timerId;
            case EventInterface::EV_READ:
                if (\is_resource($fd)) {
                    if ($this->_reads[$key = (int) $fd] ?? null) {
                        $this->del($fd, EventInterface::EV_READ);
                    }
                    $this->_reads[$key] = Coroutine::run(function () use ($fd, $func, $key, $args): void {
                        try {
                            while (1) {
                                $event = stream_poll_one($fd, STREAM_POLLIN | STREAM_POLLHUP);
                                if ($event !== STREAM_POLLNONE) {
                                    \call_user_func($func, $fd, ...$args);
                                }
                                if ($event !== STREAM_POLLIN) {
                                    $this->del($fd, EventInterface::EV_READ);
                                    break;
                                }
                                // @codeCoverageIgnoreStart
                                if (self::$debug) {
                                    break;
                                }
                                // @codeCoverageIgnoreEnd
                            }
                        } catch (\Throwable) {
                            $this->del($fd, EventInterface::EV_READ);
                        }
                    });

                    return true;
                }

                return false;
            case self::EV_WRITE:
                if (\is_resource($fd)) {
                    if ($this->_writes[$key = (int) $fd] ?? null) {
                        $this->del($fd, EventInterface::EV_WRITE);
                    }
                    $this->_writes[$key] = Coroutine::run(function () use ($fd, $func, $key, $args): void {
                        try {
                            while (1) {
                                $event = stream_poll_one($fd, STREAM_POLLOUT | STREAM_POLLHUP);
                                if ($event !== STREAM_POLLNONE) {
                                    \call_user_func($func, $fd, ...$args);
                                }
                                if ($event !== STREAM_POLLOUT) {
                                    $this->del($fd, EventInterface::EV_WRITE);
                                    break;
                                }
                                // @codeCoverageIgnoreStart
                                if (self::$debug) {
                                    break;
                                }
                                // @codeCoverageIgnoreEnd
                            }
                        } catch (\Throwable) {
                            $this->del($fd, EventInterface::EV_WRITE);
                        }
                    });

                    return true;
                }

                return false;
            default:
                return null;
        }
    }

    /** @inheritdoc  */
    public function del($fd, $flag)
    {
        switch ($flag) {
            case self::EV_SIGNAL:
                if ($coroutine = $this->_signals[$fd] ?? null) {
                    if ($coroutine->isExecuting()) {
                        $coroutine->kill();
                    }
                    unset($this->_signals[$fd]);

                    return true;
                }

                return false;
            case self::EV_TIMER:
            case self::EV_TIMER_ONCE:
                if ($coroutine = $this->_timer[$fd] ?? null) {
                    if ($coroutine->isExecuting()) {
                        $coroutine->kill();
                    }
                    unset($this->_timer[$fd]);

                    return true;
                }

                return false;
            case self::EV_READ:
                if (
                    \is_resource($fd) and
                    ($coroutine = $this->_reads[$key = (int) $fd] ?? null)
                ) {
                    if ($coroutine->isExecuting()) {
                        $coroutine->kill();
                    }
                    unset($this->_reads[$key]);

                    return true;
                }

                return false;
            case self::EV_WRITE:
                if (
                    \is_resource($fd) and
                    ($coroutine = $this->_writes[$key = (int) $fd] ?? null)
                ) {
                    if ($coroutine->isExecuting()) {
                        $coroutine->kill();
                    }
                    unset($this->_writes[$key]);

                    return true;
                }

                return false;
            default:
                return null;
        }
    }

    /** @inheritdoc  */
    public function clearAllTimer()
    {
        foreach ($this->_timer as $timer) {
            if ($timer->isExecuting()) {
                $timer->kill();
            }
        }
        $this->_timer = [];
    }

    /**
     * @codeCoverageIgnore 忽略覆盖
     * @inheritdoc
     */
    public function loop()
    {
        // workerman 4.x时，如果不阻塞，则会返回event-loop exited错误
        // waitAll方法并不会阻塞
        // 目前使用waitGroup->wait阻塞等待
        $this->_waitGroup = new WaitGroup();
        $this->_waitGroup->add();
        $this->_waitGroup->wait();
        // 保证当前进程在此退出
        exit(0);
        // 不执行之后逻辑
    }

    /** @inheritdoc  */
    public function destroy()
    {
        // 杀死所有协程
        Coroutine::killAll();
        // 退出阻塞等待
        $this->_waitGroup?->done();
        $this->_waitGroup = null;
        // 清理数据
        $this->_timer = $this->_signals = $this->_reads = $this->_writes = [];
    }

    /** @inheritdoc  */
    public function getTimerCount()
    {
        return count($this->_timer);
    }
}
