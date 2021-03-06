--TEST--
Fiber that is never resumed with throw in finally block
--SKIPIF--
<?php
include __DIR__ . '/include/skip-if.php';
--FILE--
<?php

require dirname(__DIR__) . '/scripts/bootstrap.php';

$loop = new Loop;

$loop->defer(function () use ($loop): void {
    Fiber::create(function () use ($loop): void {
        try {
            try {
                try {
                    echo "fiber\n";
                    echo Fiber::suspend(new Promise($loop), $loop);
                    echo "after await\n";
                } catch (Throwable $exception) {
                     echo "inner exit exception caught!\n";
                }
            } catch (Throwable $exception) {
                echo "exit exception caught!\n";
            } finally {
                echo "inner finally\n";
                throw new \Exception("finally exception");
            }
        } catch (Exception $exception) {
            echo $exception->getMessage(), "\n";
            echo \get_class($exception->getPrevious()), "\n";
        } finally {
            echo "outer finally\n";
        }

        try {
            echo Fiber::suspend(new Promise($loop), $loop);
        } catch (FiberError $exception) {
            echo $exception->getMessage(), "\n";
        }
    })->start();
});

Fiber::suspend(new Success($loop), $loop);

echo "done\n";

--EXPECT--
fiber
done
inner finally
finally exception
FiberExit
outer finally
Cannot suspend during shutdown
