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
    /**
     * @codeCoverageIgnore
     * @var bool
     */
    public static $debug = false;

    /** @var callable[] Event listeners of signal. */
    protected array $_signals = [];

    /** @var int[]|string[] Timer id to timer info. */
    protected array $_timer = [];

    /** @var int 定时器id */
    protected int $_timerId = 1;

    /**
     * @throws EventLoopException 如果没有启用拓展
     */
    public function __construct()
    {
        if (!static::$debug and !extension_loaded('swoole')) {
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
                            call_user_func($func, ...$args);
                            if ($flag === EventInterface::EV_TIMER_ONCE) {
                                $this->del($timerId, $flag);
                                break;
                            }
                            // @codeCoverageIgnoreStart
                            if (self::$debug) {
                                break;
                            }
                            // @codeCoverageIgnoreEnd
                        }
                    });
                    $res = is_int($res) ? (string)$res : false;
                }

                if ($res === false) {
                    $this->_timerId--;

                    return false;
                }
                $this->_timer[$timerId] = $res;

                return $timerId;
            case EventInterface::EV_READ:
                if (!\is_resource($fd)) {

                    return false;
                }

                return Event::isset($fd, SWOOLE_EVENT_READ | SWOOLE_EVENT_WRITE)
                    ? Event::set($fd, $func, null, SWOOLE_EVENT_READ | SWOOLE_EVENT_WRITE)
                    : Event::add($fd, $func, null, SWOOLE_EVENT_READ);
            case EventInterface::EV_WRITE:
                if (!\is_resource($fd)) {

                    return false;
                }

                return Event::isset($fd, SWOOLE_EVENT_READ | SWOOLE_EVENT_WRITE)
                    ? Event::set($fd, null, $func, SWOOLE_EVENT_READ | SWOOLE_EVENT_WRITE)
                    : Event::add($fd, null, $func, SWOOLE_EVENT_WRITE);
            default:
                return null;
        }
    }

    /** @inheritdoc  */
    public function del($fd, $flag)
    {
        switch ($flag) {
            case EventInterface::EV_SIGNAL:
                if ($this->_signals[$fd] ?? null) {
                    if (Process::signal($fd, null)) {
                        unset($this->_signals[$fd]);

                        return true;
                    }
                }

                return false;
            case EventInterface::EV_TIMER:
            case EventInterface::EV_TIMER_ONCE:
                if ($id = $this->_timer[$fd] ?? null) {
                    if (is_string($id) and Coroutine::cancel(intval($id))) {
                        unset($this->_timer[$fd]);

                        return true;
                    }
                    if (is_int($id) and Timer::clear($id)) {
                        unset($this->_timer[$fd]);

                        return true;
                    }
                }

                return false;
            case EventInterface::EV_READ:
                if (!\is_resource($fd)) {

                    return false;
                }

                if (!Event::isset($fd, SWOOLE_EVENT_WRITE)) {

                    return Event::del($fd);
                }
                return Event::set($fd, null, null, SWOOLE_EVENT_WRITE);
            case EventInterface::EV_WRITE:
                if (!\is_resource($fd)) {

                    return false;
                }

                if (!Event::isset($fd, SWOOLE_EVENT_READ)) {

                    return Event::del($fd);
                }

                return Event::set($fd, null, null, SWOOLE_EVENT_READ);
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
    }

    /** @inheritdoc  */
    public function clearAllTimer()
    {
        foreach ($this->_timer as $id) {
            if (is_int($id)) {
                Timer::clear($id);
            }
            if (is_string($id)) {
                Coroutine::cancel(intval($id));
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
