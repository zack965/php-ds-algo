<?php


namespace Zack\PhpDsAlgo\DataStructure\HashTabe;

use Countable;
use InvalidArgumentException;
use IteratorAggregate;
use OutOfBoundsException;
use Traversable;
use Zack\PhpDsAlgo\Contracts\IHashMap;

/**
 * @template K
 * @template V
 *
 * @implements IHashMap<K, V>
 */
class HashMap implements IHashMap, IteratorAggregate, Countable
{
    /**
     * Array of buckets containing key-value nodes.
     *
     * @var array<int, list<HashMapNode<K, V>>>
     */
    private array $table = [];

    /**
     * Creates an empty hash map with the given number of buckets.
     *
     * @param int $capacity Number of buckets in the hash map.
     *
     * @throws InvalidArgumentException If the capacity is less than or equal to zero.
     */
    public function __construct(
        private int $capacity = 10,
    ) {
        if ($this->capacity <= 0) {
            throw new InvalidArgumentException(
                'Capacity must be greater than 0.'
            );
        }
        $table = array_fill(0, $this->capacity, []);

        $this->table = $table;
    }

    /**
     * Converts a key into a deterministic string representation.
     *
     * @param K $key
     *
     * @return string
     *
     * @throws InvalidArgumentException If the key cannot be hashed.
     */
    private function serializeKey(mixed $key): string
    {
        if (is_string($key)) {
            return $key;
        }

        if (is_resource($key)) {
            throw new InvalidArgumentException(
                'Resources cannot be used as hash map keys.'
            );
        }

        if (is_float($key) && $key === 0.0) {
            // Canonicalize -0.0 to 0.0: `-0.0 === 0.0` is true in PHP (the
            // comparison this class uses for all key equality checks), but
            // serialize() encodes them differently ("d:-0;" vs "d:0;"),
            // which would otherwise put them in different buckets and break
            // hasKey()/get()/delete()/update()/put() for one of them.
            $key = 0.0;
        }

        try {
            return serialize($key);
        } catch (\Throwable $e) {
            throw new InvalidArgumentException(
                sprintf(
                    'Value of type %s cannot be used as a hash map key.',
                    get_debug_type($key)
                ),
                previous: $e
            );
        }
    }
    /**
     * Calculates the bucket index for a key.
     *
     * @param K $key
     *
     * @return int
     */
    private function tableHash(mixed $key): int
    {
        $hash = hash('xxh3', $this->serializeKey($key));

        // Use the first 8 hexadecimal characters as an unsigned integer.
        $hashInteger = hexdec(substr($hash, 0, 8));

        return $hashInteger % $this->capacity;
    }
    private function addToBucket(HashMapNode $node): void
    {
        $index = $this->tableHash($node->getKey());

        $this->table[$index][] = $node;
    }


    /**
     * Associates a key with a value.
     *
     * If the key already exists, its associated value is replaced in place
     * (no new bucket entry, no resize check). Otherwise a new entry is
     * added, and the table is grown via {@see resize()} if that pushes the
     * load factor past 0.7.
     *
     * @param K $key
     * @param V $value
     *
     * @throws InvalidArgumentException If the key cannot be hashed.
     */
    public function put(mixed $key, mixed $value): void
    {
        $position = $this->getKeyPosition($key);

        if ($position !== false) {
            $this->table[$position['bucketIndex']][$position['itemIndex']]->setValue($value);

            return;
        }

        $this->addToBucket(new HashMapNode($key, $value));


        $loadFactor = $this->getLoadFactor();

        if ($loadFactor > 0.7) {
            $this->resize();
        }
    }
    /**
     * Doubles the capacity and re-buckets every existing entry against it.
     *
     * Called automatically by {@see put()} once the load factor exceeds
     * 0.7; can also be called directly to grow the map pre-emptively.
     */
    public function resize(): void
    {

        $entries = $this->getAllEntries();

        $this->capacity *= 2;

        $this->table = array_fill(0, $this->capacity, []);

        foreach ($entries as $value) {
            $this->addToBucket($value);
        }
    }
    /**
     * @param K $key
     *
     * @throws InvalidArgumentException If the key cannot be hashed.
     */
    public function delete(mixed $key): bool
    {
        $index = $this->tableHash($key);
        foreach ($this->table[$index] as $itemIndex => $item) {
            if ($item->getKey() === $key) {
                array_splice(
                    $this->table[$index],
                    $itemIndex,
                    1
                );
                return true;
            }
        }
        return false;
    }
    /**
     * @param K $key
     *
     * @return array{bucketIndex: int, itemIndex: int}|false
     *
     * @throws InvalidArgumentException If the key cannot be hashed.
     */
    public function getKeyPosition(mixed $key): bool|array
    {
        $index = $this->tableHash($key);
        foreach ($this->table[$index] as $itemIndex => $item) {
            if ($item->getKey() === $key) {

                return [
                    "bucketIndex" => $index,
                    "itemIndex" => $itemIndex
                ];
            }
        }
        return false;
    }
    public function getValuePosition(mixed $value): bool|array
    {
        foreach ($this->table as $index => $item) {
            foreach ($item as $itemIndex => $subItem) {
                if ($subItem->getValue() === $value) {
                    return [
                        "bucketIndex" => $index,
                        "itemIndex" => $itemIndex
                    ];
                }
            }
        }
        return false;
    }
    /**
     * @param K $key
     * @param V $value
     *
     * @throws InvalidArgumentException If the key cannot be hashed.
     */
    public function update(mixed $key, mixed $value): bool
    {
        $position = $this->getKeyPosition($key);

        if ($position === false) {
            return false;
        }

        $this->table[$position['bucketIndex']][$position['itemIndex']]->setValue($value);

        return true;
    }

    /**
     * @param K $key
     *
     * @throws InvalidArgumentException If the key cannot be hashed.
     */
    public function hasKey(mixed $key): bool
    {
        $index = $this->tableHash($key);
        foreach ($this->table[$index] as $item) {
            if ($item->getKey() === $key) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param V $value
     */
    public function hasValue(mixed $value): bool
    {
        foreach ($this->table as $item) {
            foreach ($item as $subItem) {
                if ($subItem->getValue() === $value) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * @param K $key
     *
     * @return V|null The associated value, or null if the key doesn't exist.
     *
     * @throws InvalidArgumentException If the key cannot be hashed.
     */
    public function get(mixed $key): mixed
    {
        $index = $this->tableHash($key);

        foreach ($this->table[$index] as $item) {
            if ($item->getKey() === $key) {
                return $item->getValue();
            }
        }

        return null;
    }


    /**
     * @return list<HashMapNode<K, V>>
     */
    public function getAllEntries(): array
    {
        $entries = [];
        foreach ($this->table as $item) {
            foreach ($item as $subItem) {
                $entries[] = $subItem;
            }
        }
        return $entries;
    }

    /**
     * @return list<K>
     */
    public function getAllKeys(): array
    {
        $keys = [];
        foreach ($this->table as $item) {
            foreach ($item as $subItem) {
                $keys[] = $subItem->getKey();
            }
        }
        return $keys;
    }

    /**
     * @return list<V>
     */
    public function getAllValues(): array
    {
        $values = [];
        foreach ($this->table as $item) {
            foreach ($item as $subItem) {
                $values[] = $subItem->getValue();
            }
        }
        return $values;
    }

    /**
     * @param int $bucketIndex
     *
     * @return list<HashMapNode<K, V>>
     *
     * @throws OutOfBoundsException If `$bucketIndex` is outside
     *  `0` .. `getCapacity() - 1`.
     */
    public function getBucket(int $bucketIndex): array
    {
        if (!isset($this->table[$bucketIndex])) {
            throw new OutOfBoundsException(
                "Bucket index {$bucketIndex} does not exist."
            );
        }
        return $this->table[$bucketIndex];
    }

    /**
     * @return list<int>
     */
    public function getBuckets(): array
    {
        $buckets = [];
        foreach ($this->table as $bucket => $items) {
            $buckets[] = $bucket;
        }
        return $buckets;
    }

    public function getSize(): int
    {
        $size = 0;

        foreach ($this->table as $bucket) {
            $size += count($bucket);
        }

        return $size;
    }

    public function getCapacity(): int
    {
        return $this->capacity;
    }

    public function getLoadFactor(): float
    {
        return $this->getSize() / $this->capacity;
    }

    public function isEmpty(): bool
    {
        return $this->getSize() == 0;
    }

    public function clear(): void
    {
        $this->table = array_fill(0, $this->capacity, []);
    }

    public function reset(): void
    {
        $this->capacity = 10;
        $this->table = array_fill(0, $this->capacity, []);
    }

    /**
     * @return Traversable<K, V> Every key-value pair, keyed by K (not
     *  restricted to int|string the way a native PHP array's keys are) —
     *  enables `foreach ($map as $key => $value)`.
     */
    public function getIterator(): Traversable
    {
        foreach ($this->getAllEntries() as $entry) {
            yield $entry->getKey() => $entry->getValue();
        }
    }

    /**
     * Alias for {@see getSize()}, wired to PHP's `count()` via `Countable`.
     */
    public function count(): int
    {
        return $this->getSize();
    }
}
