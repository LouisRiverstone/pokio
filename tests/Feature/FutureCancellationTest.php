<?php

use Pokio\Runtime\Sync\SyncFuture;
use Pokio\Exceptions\FutureCancelled;

// Force using the sync runtime for these tests to avoid FFI/dynamic loading issues
pokio()->useSync();

test('sync future can be cancelled before awaiting', function (): void {
    $future = new SyncFuture(fn () => 1);

    expect($future->cancel())->toBeTrue();

    expect(function () use ($future): void {
        $future->await();
    })->toThrow(FutureCancelled::class);
});

test('promise cancel before defer causes await to throw', function (): void {
    $promise = async(fn () => 1);

    expect($promise->cancel())->toBeTrue();

    expect(function () use ($promise): void {
        await($promise);
    })->toThrow(FutureCancelled::class);
});

test('cancel returns false after already awaited', function (): void {
    $promise = async(fn () => 1);

    $result = await($promise);

    expect($result)->toBe(1);
    expect($promise->cancel())->toBeFalse();
});

test('double cancel returns false on second call', function (): void {
    $promise = async(function () {
        sleep(1);
        return 1;
    });

    expect($promise->cancel())->toBeTrue();
    expect($promise->cancel())->toBeFalse();
});

test('unwaited future manager ignores cancelled futures', function (): void {
    $future = new SyncFuture(static fn () => throw new FutureCancelled());

    Pokio\UnwaitedFutureManager::instance()->schedule($future);

    // Should not throw
    Pokio\UnwaitedFutureManager::instance()->run();

    expect(true)->toBeTrue();
});
