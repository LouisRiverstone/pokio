<?php

declare(strict_types=1);

namespace Pokio\Runtime\Sync;

use Closure;
use Pokio\Contracts\Future;
use Pokio\Exceptions\FutureAlreadyAwaited;
use Pokio\Promise;

/**
 * @internal
 *
 * @template TResult
 *
 * @implements Future<TResult>
 */
final class SyncFuture implements Future
{
    /**
     * Indicates whether the result has been awaited.
     */
    private bool $awaited = false;

    /**
     * Whether the future has been cancelled.
     */
    private bool $cancelled = false;

    /**
     * Creates a new sync result instance.
     *
     * @param  Closure(): TResult  $callback
     */
    public function __construct(private Closure $callback)
    {
        //
    }

    /**
     * Awaits the result of the future.
     *
     * @return TResult
     */
    public function await(): mixed
    {
        if ($this->cancelled) {
            throw new \Pokio\Exceptions\FutureCancelled();
        }

        if ($this->awaited) {
            throw new FutureAlreadyAwaited();
        }

        $this->awaited = true;

        $result = ($this->callback)();

        if ($result instanceof Promise) {
            return await($result);
        }

        return $result;
    }

    /**
     * Cancels the future.
     *
     * @return bool Whether the future was successfully cancelled
     */
    public function cancel(): bool
    {
        if ($this->awaited || $this->cancelled) {
            return false;
        }

        $this->cancelled = true;

        return true;
    }

    /**
     * Whether the result has been awaited.
     */
    public function awaited(): bool
    {
        return $this->awaited;
    }
}
