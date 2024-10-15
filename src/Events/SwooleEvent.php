<?php
/**
 * @author workbunny/Chaz6chez
 * @email chaz6chez1993@outlook.com
 */

declare(strict_types=1);

namespace Workbunny\WebmanCoroutine\Events;

use Swoole\Coroutine;
use Swoole\Event;
use Swoole\Process;
use Swoole\Timer;
use Workbunny\WebmanCoroutine\Exceptions\EventLoopException;
use Workerman\Events\EventInterface;

class SwooleEvent implements EventInterface
{
    /** @var int[] All listeners for read event. */
    protected array $_reads = [];

    /** @var int[] All listeners for write event. */
    protected array $_writes = [];

    /** @var callable[] Event listeners of signal. */
    protected array $_signals = [];

    /** @var int[]|true[] Timer id to timer info. */
    protected array $_timer = [];

    /** @var int 定时器id */
    protected int $_timerId = 1;

    /**
     * @param bool $debug 测试用
     * @throws EventLoopException 如果没有启用拓展
     */
    public function __construct(bool $debug = false)
    {
        if (!$debug and !extension_loaded('swoole')) {
            throw new EventLoopException('Not support ext-swoole. ');
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
                if (($interval = (int) ($fd * 1000)) >= 1) {
                    $res = Timer::tick($interval, function () use ($timerId, $flag, $func, $args) {
                        call_user_func($func, ...$args);
                        if ($flag === EventInterface::EV_TIMER_ONCE) {
                            $this->del($timerId, $flag);
                        }
                    });
                } else {
                    $res = Coroutine::create(function () use ($fd, $timerId, $flag, $func, $args) {
                        while (true) {
                            usleep((int) ($fd * 1000 * 1000));
                            if (!isset($this->_timer[$timerId])) {
                                return;
                            }
                            call_user_func($func, ...$args);
                            if ($flag === EventInterface::EV_TIMER_ONCE) {
                                $this->del($timerId, $flag);

                                return;
                            }
                        }
                    });
                    $res = $res !== false;
                }

                if ($res === false) {
                    $this->_timerId--;

                    return false;
                }
                $this->_timer[$timerId] = $res;

                return $timerId;
            case EventInterface::EV_READ:
                if (\is_resource($fd)) {
                    if (
                        ($this->_reads[$key = (int) $fd] ?? null) or
                        Event::isset($fd, SWOOLE_EVENT_READ)
                    ) {
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
                    if (
                        ($this->_writes[$key = (int) $fd] ?? null) or
                        Event::isset($fd, SWOOLE_EVENT_WRITE)
                    ) {
                        $this->del($fd, EventInterface::EV_WRITE);
                    }
                    if ($res = Event::add($fd, null, $func, SWOOLE_EVENT_WRITE)) {
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
                    if ($id === true or Timer::clear($id)) {
                        unset($this->_timer[$fd]);

                        return true;
                    }
                }

                return false;
            case self::EV_READ:
                if (\is_resource($fd)) {
                    $key = (int) $fd;
                    if (Event::isset($fd, SWOOLE_EVENT_READ)) {
                        if (Event::del($fd)) {
                            return false;
                        }
                    }
                    unset($this->_reads[$key]);

                    return true;
                }

                return false;
            case self::EV_WRITE:
                if (\is_resource($fd)) {
                    $key = (int) $fd;
                    if (Event::isset($fd, SWOOLE_EVENT_WRITE)) {
                        if (Event::del($fd)) {
                            return false;
                        }
                    }
                    unset($this->_writes[$key]);

                    return true;
                }

                return false;
            default:
                return null;
        }
    }

    /**
     * @inheritdoc
     * @codeCoverageIgnore 忽略覆盖
     */
    public function loop()
    {
        // 阻塞等待
        Event::wait();
        // 确定loop为退出状态
        exit(0);
    }

    /** @inheritdoc  */
    public function destroy()
    {
        // 移除所有定时器
        $this->clearAllTimer();
        // 退出所有协程
        foreach (Coroutine::listCoroutines() as $coroutine) {
            Coroutine::cancel($coroutine);
        }
        // 退出event loop
        Event::exit();
        $this->_reads = $this->_writes = [];
    }

    /** @inheritdoc  */
    public function clearAllTimer()
    {
        foreach ($this->_timer as $id) {
            if (is_int($id)) {
                Timer::clear($id);
            }
        }
        $this->_timer = [];
    }

    /** @inheritdoc  */
    public function getTimerCount()
    {
        return count($this->_timer);
    }
}
