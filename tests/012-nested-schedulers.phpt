--TEST--
Nested schedulers
--SKIPIF--
<?php include __DIR__ . '/include/skip-if.php';
--FILE--
<?php

require dirname(__DIR__) . '/scripts/bootstrap.php';

$loop1 = new Loop;
$loop2 = new Loop;

$fiber = Fiber::create(function () use ($loop1, $loop2): void {
    $promise1 = new Promise($loop1);
    $promise2 = new Promise($loop2);
    $promise3 = new Promise($loop2);
    $promise4 = new Promise($loop1);

    $loop1->delay(10, fn() => $promise1->resolve(1));
    $loop2->delay(20, fn() => $promise2->resolve(2));
    $loop2->delay(50, fn() => $promise3->resolve(3));
    $loop1->delay(30, fn() => $promise4->resolve(4));

    echo Fiber::suspend($promise1, $loop1);
    echo Fiber::suspend($promise2, $loop2);
    echo Fiber::suspend($promise3, $loop2);
    echo Fiber::suspend($promise4, $loop1);
});

$loop1->defer(fn() => $fiber->start());

$fiber = Fiber::create(function () use ($loop1, $loop2): void {
    $promise5 = new Promise($loop1);
    $promise6 = new Promise($loop2);
    $promise7 = new Promise($loop1);

    $loop1->delay(15, fn() => $promise5->resolve(5));
    $loop2->delay(25, fn() => $promise6->resolve(6));
    $loop1->delay(35, fn() => $promise7->resolve(7));

    echo Fiber::suspend($promise5, $loop1);
    echo Fiber::suspend($promise6, $loop2);
    echo Fiber::suspend($promise7, $loop1);
});

$loop1->defer(fn() => $fiber->start());

Fiber::suspend(new Success($loop1), $loop1);

// Note that $loop2 blocks $loop1 until $promise3 is resolved, which is why the timers appear to finish out of order.

--EXPECT--
1235647
