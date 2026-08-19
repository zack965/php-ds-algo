<?php

namespace Tests\Support\Fixtures;

/**
 * Minimal value object used to exercise heaps with a custom comparator over
 * a non-scalar payload (e.g. a priority-queue use case).
 */
class Task
{
    public function __construct(
        public readonly string $name,
        public readonly int $priority
    ) {
    }
}
