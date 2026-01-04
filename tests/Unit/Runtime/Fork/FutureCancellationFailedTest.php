<?php

declare(strict_types=1);

namespace Pokio\Runtime\Fork;

use Pokio\Runtime\Fork\ForkFuture;
use Pokio\Exceptions\FutureCancellationFailed;

// Simulate posix_kill failing
function posix_kill(int $pid, int $sig): bool
{
    return false;
}

test('fork future cancel throws when posix_kill fails', function (): void {
    $rc = new \ReflectionClass(ForkFuture::class);

    // Create instance without running constructor
    $future = $rc->newInstanceWithoutConstructor();

    // Create a fake IPC instance without constructor
    $ipcC = new \ReflectionClass(IPC::class);
    $ipc = $ipcC->newInstanceWithoutConstructor();

    // Set required private properties
    $pidProp = $rc->getProperty('pid');
    $pidProp->setAccessible(true);
    $pidProp->setValue($future, 12345);

    $memProp = $rc->getProperty('memory');
    $memProp->setAccessible(true);
    $memProp->setValue($future, $ipc);

    $onWaitProp = $rc->getProperty('onWait');
    $onWaitProp->setAccessible(true);
    $onWaitProp->setValue($future, static fn (int $pid) => null);

    expect(function () use ($future): void {
        $future->cancel();
    })->toThrow(FutureCancellationFailed::class);
});
