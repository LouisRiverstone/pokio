<?php

declare(strict_types=1);

namespace Pokio\Exceptions;

use RuntimeException;

final class FutureCancelled extends RuntimeException
{
    public function __construct(string $message = 'Future was cancelled')
    {
        parent::__construct($message);
    }
}
