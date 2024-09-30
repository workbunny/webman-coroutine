<?php
/**
 * @author workbunny/Chaz6chez
 * @email chaz6chez1993@outlook.com
 */
declare(strict_types=1);

namespace Workbunny\WebmanCoroutine\Utils\Worker;

/**
 * 网络进程
 *
 * 1. http server
 * 2. websocket server
 * 3. tcp server
 * 4. udp server
 */
class Server extends AbstractWorker
{
    use ServerMethods;
}
