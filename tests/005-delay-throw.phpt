--TEST--
Test delayed throw
--SKIPIF--
<?php include __DIR__ . '/include/skip-if.php';
--FILE--
<?php

require dirname(__DIR__) . '/scripts/bootstrap.php';

$loop = new Loop;

$timeout = 100;

$start = $loop->now();

try {
    echo Fiber::suspend(function (Fiber $fiber) use ($loop, $timeout): void {
        $loop->delay($timeout, fn() => $fiber->throw(new Exception('test')));
    }, $loop);
    throw new Exception('Fiber::suspend() did not throw');
} catch (Exception $exception) {
    echo $exception->getMessage();
}

if ($loop->now() - $start < $timeout) {
    throw new Exception(sprintf('Test took less than %dms', $timeout));
}

--EXPECT--
test
