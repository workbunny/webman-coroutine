<?php
/**
 * @author workbunny/Chaz6chez
 * @email chaz6chez1993@outlook.com
 */
declare(strict_types=1);

namespace Workbunny\WebmanCoroutine\Exceptions;

/**
 * 特殊异常类，用于跳过 WaitGroup的done，用于特殊情况的阻塞
 */
class SkipWaitGroupDoneException extends RuntimeException
{
}
