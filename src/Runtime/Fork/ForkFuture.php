<?php

declare(strict_types=1);

namespace Pokio\Runtime\Fork;

use Closure;
use Pokio\Contracts\Future;
use Pokio\Exceptions\FutureAlreadyAwaited;
use Pokio\Exceptions\FutureCancelled;

/**
 * Represents the result of a forked process.
 *
 * @template TResult
 *
 * @implements Future<TResult>
 *
 * @internal
 */
final class ForkFuture implements Future
{
    /**
     * Whether the result has been awaited.
     */
    private bool $awaited = false;

    /**
     * Whether the future has been cancelled.
     */
    private bool $cancelled = false;

    /**
     * Creates a new fork result instance.
     */
    public function __construct(
        private readonly int $pid,
        private readonly IPC $memory,
        private readonly Closure $onWait,
    ) {
        //
    }

    /**
     * Awaits the result of the future.
     *
     * @return TResult|null
     */
    public function await(): mixed
    {
        if ($this->cancelled) {
            throw new FutureCancelled();
        }

        if ($this->awaited) {
            throw new FutureAlreadyAwaited();
        }

        $this->awaited = true;

        pcntl_waitpid($this->pid, $status);

        if (! file_exists($this->memory->path()) || filesize($this->memory->path()) === 0) {
            return null;
        }

        $this->onWait->__invoke($this->pid);

        /** @var TResult $result */
        $result = unserialize($this->memory->pop());

        return $result;
    }

    /**
     * Cancels the forked process running the future.
     *
     * @return bool Whether the process was signaled
     */
    public function cancel(): bool
    {
        if ($this->awaited || $this->cancelled) {
            return false;
        }

        // If posix_kill is available, attempt to terminate the child process.
        if (function_exists('posix_kill')) {
            $sent = posix_kill($this->pid, SIGTERM);

            if (! $sent) {
                // Signal failed; surface an explicit exception for callers
                throw new \Pokio\Exceptions\FutureCancellationFailed();
            }
        } else {
            $sent = false;
        }

        $this->cancelled = true;

        return (bool) $sent;
    }

    /**
     * Whether the result has been awaited.
     */
    public function awaited(): bool
    {
        return $this->awaited;
    }
}
