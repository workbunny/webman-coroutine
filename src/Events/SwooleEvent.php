<?php
/**
 * @author workbunny/Chaz6chez
 * @email chaz6chez1993@outlook.com
 */

declare(strict_types=1);

namespace Workbunny\WebmanSwow\Events;

use Swoole\Event;
use Swoole\Runtime;
use Workerman\Events\Swoole;

class SwooleEvent extends Swoole
{
    public function __construct()
    {
        if (!extension_loaded('swoole')) {
            throw new \RuntimeException('Not support ext-swoole. ');
        }
    }

    public function loop()
    {
        parent::loop();
        dump('loop');
    }

    public function destroy()
    {
        Event::exit();
        Runtime::enableCoroutine(false);
        posix_kill(posix_getpid(), SIGINT);
    }
}
