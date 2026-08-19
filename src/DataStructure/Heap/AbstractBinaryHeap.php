<?php


namespace Zack\PhpDsAlgo\DataStructure\Heap;

use RuntimeException;
use Zack\PhpDsAlgo\Contracts\IHeap;
use Zack\PhpDsAlgo\Helpers\Algorythmes\AlgorythmesGlobalHelpers;

abstract class AbstractBinaryHeap implements IHeap
{
    /**
     * @var array<mixed> The heap data stored in array
     */
    protected array $heap = [];
    /**
     * @var int Current number of elements in the heap
     */
    protected int $size = 0;
    /**
     * @var int Maximum capacity of the heap
     */
    protected int $capacity = 0;

    /**
     * @var callable|null Custom comparator function
     */
    protected $comparator;
    /**
     * @var int Default capacity if not specified
     */
    protected const DEFAULT_CAPACITY = 16;

    /**
     * @var int Growth factor when expanding
     */
    protected const GROWTH_FACTOR = 2;

    public function __construct(array $data = [], int $capacity = 0, ?callable $comparator = null)
    {
        $this->capacity = $capacity > 0 ? $capacity : self::DEFAULT_CAPACITY;
        $this->heap = array_fill(0, $this->capacity, null);
        $this->size = 0;
        $this->comparator = $comparator ?? $this->getDefaultComparator();

        if (!empty($data)) {
            $this->buildFromArray($data);
        }
    }

    abstract protected function getDefaultComparator(): callable;
    protected function compare(mixed $a, mixed $b): int
    {
        return ($this->comparator)($a, $b);
    }

    /**
     * Get parent index
     */
    protected function getParentIndex(int $index): int
    {
        return (int) floor(($index - 1) / 2);
    }

    /**
     * Get left child index
     */
    protected function getLeftChildIndex(int $index): int
    {
        return 2 * $index + 1;
    }

    /**
     * Get right child index
     */
    protected function getRightChildIndex(int $index): int
    {
        return 2 * $index + 2;
    }

    /**
     * Check if node has left child
     */
    protected function hasLeftChild(int $index): bool
    {
        return $this->getLeftChildIndex($index) < $this->size;
    }

    /**
     * Check if node has right child
     */
    protected function hasRightChild(int $index): bool
    {
        return $this->getRightChildIndex($index) < $this->size;
    }

    /**
     * Check if node has any children
     */
    protected function hasChildren(int $index): bool
    {
        return $this->hasLeftChild($index);
    }

    public function insert(mixed $value): void
    {
        $this->ensureCapacity($this->size + 1);
        $this->heap[$this->size] = $value;
        $this->size++;
        $this->heapifyUp($this->size - 1);
    }
    public function peek(): mixed
    {
        if ($this->isEmpty()) {
            throw new RuntimeException("Heap is empty");
        }
        return $this->heap[0];
    }
    public function extract(): mixed
    {
        if ($this->isEmpty()) {
            throw new RuntimeException("Heap is empty");
        }

        $root = $this->heap[0];
        $lastElement = $this->heap[$this->size - 1];

        $this->heap[0] = $lastElement;
        $this->heap[$this->size - 1] = null; // Clear reference
        $this->size--;

        if ($this->size > 0) {
            $this->heapifyDown(0);
        }

        return $root;
    }
    public function isEmpty(): bool
    {
        return $this->size === 0;
    }
    public function size(): int
    {
        return $this->size;
    }
    public function getCapacity(): int
    {
        return $this->capacity;
    }
    public function ensureCapacity(int $minCapacity): void
    {
        if ($minCapacity > $this->capacity) {
            $newCapacity = max($minCapacity, $this->capacity * self::GROWTH_FACTOR);
            $this->resize($newCapacity);
        }
    }
    protected function resize(int $newCapacity): void
    {
        $newHeap = array_fill(0, $newCapacity, null);

        // Copy existing elements
        for ($i = 0; $i < $this->size; $i++) {
            $newHeap[$i] = $this->heap[$i];
        }

        $this->heap = $newHeap;
        $this->capacity = $newCapacity;
    }
    protected function buildFromArray(array $data): void
    {
        $count = count($data);
        $this->ensureCapacity($count);

        // Copy data
        for ($i = 0; $i < $count; $i++) {
            $this->heap[$i] = $data[$i];
        }
        $this->size = $count;

        // Build heap
        $this->buildHeap();
    }
    public function buildHeap(): void
    {
        if ($this->size <= 1) {
            return;
        }
        $lastElement = count($this->heap) - 1;
        $parentIndex = $this->getParentIndex($lastElement);
        $start = $parentIndex;
        while ($start >= 0) {
            $this->heapifyDown($start);
            $start--;
        }
    }
    public function clear(): void
    {
        $this->heap = array_fill(0, $this->capacity, null);
        $this->size = 0;
    }
    public function isValid(): bool
    {
        if ($this->size <= 1) {
            return true;
        }
        return false;
    }
    public function toArray(): array
    {
        $result = [];
        for ($i = 0; $i < $this->size; $i++) {
            $result[] = $this->heap[$i];
        }
        return $result;
    }
}
