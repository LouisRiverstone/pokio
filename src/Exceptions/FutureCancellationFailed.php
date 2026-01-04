<?php

declare(strict_types=1);

namespace Pokio\Exceptions;

use RuntimeException;

final class FutureCancellationFailed extends RuntimeException
{
    public function __construct(string $message = 'Failed to cancel future')
    {
        parent::__construct($message);
    }
}
