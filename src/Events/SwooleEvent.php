<?php
/**
 * @author workbunny/Chaz6chez
 * @email chaz6chez1993@outlook.com
 */

declare(strict_types=1);

namespace Workbunny\WebmanCoroutine\Events;

use Swoole\Event;
use Swoole\Process;
use Swoole\Timer;
use Workerman\Events\EventInterface;

class SwooleEvent implements EventInterface
{
    /** @var int[] All listeners for read event. */
    protected array $_reads = [];

    /** @var int[] All listeners for write event. */
    protected array $_writes = [];

    /** @var callable[] Event listeners of signal. */
    protected array $_signals = [];

    /** @var int[] Timer id to timer info. */
    protected array $_timer = [];

    /** @var int 定时器id */
    protected int $_timerId = 0;

    public function __construct()
    {
        if (!extension_loaded('swoole')) {
            throw new \RuntimeException('Not support ext-swoole. ');
        }
    }

    /** @inheritdoc  */
    public function add($fd, $flag, $func, $args = [])
    {
        switch ($flag) {
            case EventInterface::EV_SIGNAL:
                if (!isset($this->_signals[$fd])) {
                    if ($res = Process::signal($fd, $func)) {
                        $this->_signals[$fd] = $func;
                    }

                    return $res;
                }

                return false;
            case EventInterface::EV_TIMER:
            case EventInterface::EV_TIMER_ONCE:
                $timerId = $this->_timerId++;
                $this->_timer[$timerId] = Timer::after((int) ($fd * 1000), function () use ($timerId, $flag, $func) {
                    call_user_func($func);
                    if ($flag === EventInterface::EV_TIMER_ONCE) {
                        $this->del($timerId, $flag);
                    }
                });

                return $timerId;
            case EventInterface::EV_READ:
                if (\is_resource($fd)) {
                    if ($this->_reads[$key = (int) $fd] ?? null) {
                        $this->del($fd, EventInterface::EV_READ);
                    }
                    if ($res = Event::add($fd, $func, null, SWOOLE_EVENT_READ)) {
                        $this->_reads[$key] = 1;
                    }

                    return (bool) $res;
                }

                return false;
            case self::EV_WRITE:
                if (\is_resource($fd)) {
                    if ($this->_writes[$key = (int) $fd] ?? null) {
                        $this->del($fd, EventInterface::EV_WRITE);
                    }
                    if ($res = Event::add($fd, $func, null, SWOOLE_EVENT_WRITE)) {
                        $this->_writes[$key] = 1;
                    }

                    return (bool) $res;
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
                if ($this->_signals[$fd] ?? null) {
                    if (Process::signal($fd, null)) {
                        unset($this->_signals[$fd]);

                        return true;
                    }
                }

                return false;
            case self::EV_TIMER:
            case self::EV_TIMER_ONCE:
                if ($id = $this->_timer[$fd] ?? null) {
                    if (Timer::clear($id)) {
                        unset($this->_timer[$fd]);

                        return true;
                    }
                }

                return false;
            case self::EV_READ:
                if (
                    \is_resource($fd) and
                    isset($this->_reads[$key = (int) $fd]) and
                    Event::isset($fd, SWOOLE_EVENT_READ)
                ) {
                    if (Event::del($fd)) {
                        unset($this->_reads[$key]);

                        return true;
                    }
                }

                return false;
            case self::EV_WRITE:
                if (
                    \is_resource($fd) and
                    isset($this->_writes[$key = (int) $fd]) and
                    Event::isset($fd, SWOOLE_EVENT_WRITE)
                ) {
                    if (Event::del($fd)) {
                        unset($this->_writes[$key]);

                        return true;
                    }
                }

                return false;
            default:
                return null;
        }
    }

    /** @inheritdoc  */
    public function loop()
    {
        Event::wait();
    }

    /** @inheritdoc  */
    public function destroy()
    {
        $this->clearAllTimer();
        Event::exit();
        $this->_reads = $this->_writes = [];
        posix_kill(posix_getpid(), SIGINT);
    }

    /** @inheritdoc  */
    public function clearAllTimer()
    {
        foreach ($this->_timer as $id) {
            Timer::clear($id);
        }
        $this->_timer = [];
    }

    /** @inheritdoc  */
    public function getTimerCount()
    {
        return count($this->_timer);
    }
}
