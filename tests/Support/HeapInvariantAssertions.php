<?php

namespace Tests\Support;

/**
 * Shared assertions for verifying the binary-heap invariant against an
 * arbitrary comparator, rather than against a hardcoded operator. This lets
 * the same assertion be reused for the default comparator and for any
 * custom comparator a test supplies.
 */
trait HeapInvariantAssertions
{
    /**
     * Asserts that compare(parent, child) <= 0 holds for every parent/child
     * pair in the array representation of the heap, according to the given
     * comparator.
     *
     * @param array<int, mixed> $heapArray
     */
    protected function assertHeapProperty(array $heapArray, callable $comparator, string $message = ''): void
    {
        $size = count($heapArray);

        for ($parent = 0; $parent < $size; $parent++) {
            $left = 2 * $parent + 1;
            $right = 2 * $parent + 2;

            if ($left < $size) {
                $this->assertLessThanOrEqual(
                    0,
                    $comparator($heapArray[$parent], $heapArray[$left]),
                    $message !== '' ? $message : "Heap property violated: index {$parent} vs left child {$left}"
                );
            }

            if ($right < $size) {
                $this->assertLessThanOrEqual(
                    0,
                    $comparator($heapArray[$parent], $heapArray[$right]),
                    $message !== '' ? $message : "Heap property violated: index {$parent} vs right child {$right}"
                );
            }
        }
    }
}
