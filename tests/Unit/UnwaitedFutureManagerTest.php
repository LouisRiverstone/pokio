<?php

use Pokio\UnwaitedFutureManager;

test('unwaited future manager ignores exceptions thrown during await', function (): void {
    $future = new class implements \Pokio\Contracts\Future {
        public function await(): mixed
        {
            throw new RuntimeException('boom');
        }

        public function cancel(): bool
        {
            return false;
        }

        public function awaited(): bool
        {
            return false;
        }
    };

    UnwaitedFutureManager::instance()->schedule($future);

    // Should not throw
    UnwaitedFutureManager::instance()->run();

    expect(true)->toBeTrue();
});
