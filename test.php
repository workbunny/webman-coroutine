<?php
require_once __DIR__ . '/vendor/autoload.php';

/** @desc 任务1  */
function task1(): void
{
    // 总共3s
    for ($i = 0; $i < 3; $i++) {
        // 写入文件
        sleep(1);
        echo '[x] [🕷️] [写入文件] [' . $i . '] ' . date('Y-m-d H:i:s') . PHP_EOL;
    }
}

/** @desc 任务2 */
function task2(): void
{
    // 总共5s
    for ($i = 0; $i < 5; $i++) {
        // 发送邮件给50名会员,
        sleep(1);
        echo '[x] [🍁] [发送邮件] [' . $i . '] ' . date('Y-m-d H:i:s') . PHP_EOL;
    }
}

/** @desc 任务3  */
function task3(): void
{
    // 总共10s
    for ($i = 0; $i < 10; $i++) {
        // 模拟插入10
        sleep(1);
        echo '[x] [🌾] [插入数据] [' . $i . '] ' . date('Y-m-d H:i:s') . PHP_EOL;
    }
}


//$timeOne = microtime(true);
//task1();
//task2();
//task3();
//$timeTwo = microtime(true);
//echo '[x] [运行时间] ' . ($timeTwo - $timeOne) . PHP_EOL;

$wg = new \Swow\Sync\WaitGroup();
$wg->add(3);
$timeOne = microtime(true);
\Swow\Coroutine::run(function () use ($wg) {
    task1();
    $wg->done();
});
\Swow\Coroutine::run(function () use ($wg) {
    task2();
    $wg->done();
});
\Swow\Coroutine::run(function () use ($wg) {
    task3();
    $wg->done();
});
// 等待协程完毕
$wg->wait();
$timeTwo = microtime(true);

echo '[x] [运行时间] ' . ($timeTwo - $timeOne) . PHP_EOL;