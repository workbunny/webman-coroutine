<?php
/**
 * @author workbunny/Chaz6chez
 * @email chaz6chez1993@outlook.com
 */
declare(strict_types=1);

namespace Workbunny\WebmanSwow;

use Swow\Coroutine;
use Swow\Signal;
use Swow\SignalException;
use Swow\Sync\WaitGroup;
use Workerman\Events\EventInterface;

class SwowEvent implements EventInterface
{
    /** @var Coroutine[] All listeners for read event. */
    protected array $_reads = [];

    /** @var Coroutine[] All listeners for write event. */
    protected array $_writes = [];

    /** @var Coroutine[] Event listeners of signal. */
    protected array $_signals = [];

    /** @var Coroutine[] Timer id to timer info. */
    protected array $_timer = [];

    /** @var int */
    protected int $_timerId = 0;

    protected null|WaitGroup $_waitGroup = null;

    protected null|Coroutine $_mainCoroutine = null;

    public function __construct()
    {
        if (!extension_loaded('swow')) {
            throw new \RuntimeException('Not support ext-swow. ');
        }
    }

    /** @inheritdoc  */
    public function add($fd, $flag, $func, $args = [])
    {
        switch ($flag) {
            case EventInterface::EV_SIGNAL:
                if (!isset($this->_signals[$fd])) {
                    $this->_signals[$fd] = Coroutine::run(function () use ($fd, $func, $args): void {
                        try {
                            Signal::wait($fd);
                            \call_user_func($func, $fd, ...$args);
                        } catch (SignalException) {
                        }
                    });

                    return true;
                }

                return false;
            case EventInterface::EV_TIMER:
            case EventInterface::EV_TIMER_ONCE:
                $timerId = $this->_timerId++;
                $this->_timer[$timerId] = Coroutine::run(function () use ($timerId, $fd, $flag, $func, $args): void {
                    while (1) {
                        msleep((int) ($fd * 1000));
                        \call_user_func($func, ...$args);
                        if ($flag === EventInterface::EV_TIMER_ONCE) {
                            $this->del($timerId, $flag);
                        }
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
                            }
                        } catch (\RuntimeException) {
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
                            }
                        } catch (\RuntimeException) {
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

    /** @inheritdoc  */
    public function loop()
    {
        $this->_waitGroup = new WaitGroup();
        $this->_waitGroup->add();
        $this->_mainCoroutine = Coroutine::run(function (): void {
            while (1) {
                msleep(0);
            }
        });
        $this->_waitGroup->wait();
        exit(0);
    }

    /** @inheritdoc  */
    public function destroy()
    {
        $this->_mainCoroutine?->kill();
        Coroutine::killAll();
        $this->_waitGroup?->done();
        $this->_waitGroup = null;
        $this->_timer = $this->_signals = $this->_reads = $this->_writes = [];
    }

    /** @inheritdoc  */
    public function getTimerCount()
    {
        return count($this->_timer);
    }
}
