<?php
/**
 * @author workbunny/Chaz6chez
 * @email chaz6chez1993@outlook.com
 */
declare(strict_types=1);

namespace Workbunny\WebmanCoroutine\Exceptions;

use Throwable;

/**
 * 协程或挂起事件被杀死
 */
class KilledException extends RuntimeException
{
    /**
     * @var string|null
     */
    protected null|string $event;

    /**
     * @param string $message
     * @param int $code
     * @param string|null $event
     * @param Throwable|null $previous
     */
    public function __construct(string $message = "", int $code = 0, ?string $event = null, ?Throwable $previous = null)
    {
        $this->event = $event;
        parent::__construct($message, $code, $previous);
    }

    /**
     * @return string|null
     */
    public function getEvent(): ?string
    {
        return $this->event;
    }
}
