<?php

declare(strict_types=1);

use Workbunny\WebmanCoroutine\Exceptions\PoolDebuggerException;
use Workbunny\WebmanCoroutine\Utils\Pool\Debugger;

require_once __DIR__ . '/vendor/autoload.php';

function debugger(mixed $value): bool
{
    $debugger = new Debugger();
    $res = $debugger->cloneValidate($value);
    if ($res instanceof Generator) {
        $r = true;
        foreach ($res as $item) {
            $r = $item->getReturn();
        }
        return $r;
    } else {
        return $res;
    }
}


//
//$a = new class {
//    public $a;
//    protected string $mane = 'A';
//    public function __construct()
//    {
//        $this->a = new class {
//            protected string $mane = 'B';
//            public $a;
//            public $b;
//            public function __construct()
//            {
////                $this->b = $this;
//                $this->a = new class {
//                    protected string $mane = 'C';
//                    public $a;
//                    public static $b;
//                    public function __construct()
//                    {
////                        static::$b = [];
////                        $this->a = new class {
////                            public $a;
////                            public function __construct() {
////                                $this->a = fopen('php://memory', 'w+');
////                            }
////                        };
//                    }
//                };
//            }
//        };
//    }
//};
//
//try {
//    dump(debugger($a));
//
//} catch (PoolDebuggerException $exception) {
//    dump($exception->getMessage(), $exception->getCode());
//}
//
//// 查看生命周期缓存
//dump(Debugger::getSeen());
//unset($a);
//// 查看生命周期缓存
//dump(Debugger::getSeen());
