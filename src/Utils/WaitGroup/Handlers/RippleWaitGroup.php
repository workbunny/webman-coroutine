<?php
/**
 * @author workbunny/Chaz6chez
 * @email chaz6chez1993@outlook.com
 */
declare(strict_types=1);

namespace Workbunny\WebmanCoroutine\Utils\WaitGroup\Handlers;

use Workbunny\WebmanCoroutine\Handlers\RippleHandler;

class RippleWaitGroup extends RevoltWaitGroup
{
    /** @inheritdoc  */
    public function __construct()
    {
        parent::__construct();
        $this->_handlerClass = RippleHandler::class;
    }

}
