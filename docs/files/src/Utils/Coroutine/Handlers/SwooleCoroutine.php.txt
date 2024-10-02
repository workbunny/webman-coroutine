<?php
/**
 * @author workbunny/Chaz6chez
 * @email chaz6chez1993@outlook.com
 */
declare(strict_types=1);

namespace Workbunny\WebmanCoroutine\Utils\Coroutine\Handlers;

use Swoole\Coroutine;

class SwooleCoroutine implements CoroutineInterface
{
    /**
     * @var int|null
     */
    protected ?int $_id;

    /** @inheritdoc
     * @param \Closure $func
     */
    public function __construct(\Closure $func)
    {
        while (1) {
            if ($res = Coroutine::create($func)) {
                $this->_id = $res;
                break;
            }
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
}
