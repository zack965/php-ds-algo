<?php


namespace Zack\PhpDsAlgo\DataStructure\HashTabe;

use Closure;
use InvalidArgumentException;
use Traversable;
use Zack\PhpDsAlgo\Algorithmes\GeneralArrayAlgorithms;
use Zack\PhpDsAlgo\Contracts\IHashSet;
use Zack\PhpDsAlgo\DataStructure\Set\Set;

/**
 * @phpstan-type HashSetValue scalar|object
 * @psalm-type HashSetValue scalar|object
 *
 * @template T of scalar|object
 */
class HashSet implements IHashSet
{
    /**
     * @var array<int, Set<T>>
     */
    private array $table;
    public function __construct(
        private int $capacity = 10,
    ) {
        if ($this->capacity <= 0) {
            throw new InvalidArgumentException(
                'Capacity must be greater than 0.'
            );
        }
        $this->table = [];

        for ($i = 0; $i < $this->capacity; $i++) {
            $this->table[$i] = new Set();
        }
    }
    public function getIterator(): Traversable
    {
        foreach ($this->table as $bucket) {
            foreach ($bucket as $value) {
                yield $value;
            }
        }
    }

    public function count(): int
    {
        return $this->getSize();
    }
    private function serializeKey(mixed $value): string
    {
        if ($value === null) {
            throw new InvalidArgumentException(
                'Null cannot be used as a hash table value.'
            );
        }
        if (is_string($value)) {
            return $value;
        }
        if (is_array($value)) {
            throw new InvalidArgumentException(
                'Arrays cannot be used as hash table values.'
            );
        }

        if (is_resource($value)) {
            throw new InvalidArgumentException(
                'Resources cannot be used as hash table values.'
            );
        }
        if ($value instanceof Closure) {
            throw new InvalidArgumentException(
                'Closures cannot be used as hash table values.'
            );
        }
        if (is_object($value)) {
            return 'object:' . spl_object_id($value);
        }
        if (is_float($value) && $value === 0.0) {
            // Canonicalize -0.0 to 0.0: `-0.0 === 0.0` is true in PHP (the
            // comparison this class uses for all equality checks), but
            // serialize() encodes them differently ("d:-0;" vs "d:0;"),
            // which would otherwise put them in different buckets and break
            // hasValue()/delete()/getValuePosition()/update() for one of them.
            $value = 0.0;
        }

        try {
            return serialize($value);
        } catch (\Throwable $e) {
            throw new InvalidArgumentException(
                sprintf('Value of type %s cannot be hashed.', get_debug_type($value)),
                previous: $e
            );
        }
    }
    private function tableHash(mixed $value): int
    {
        $hash = hash('xxh3', $this->serializeKey($value));

        // Convert the hexadecimal hash into an integer.
        $hashInteger = hexdec(substr($hash, 0, 8));

        // Map the hash to a valid bucket index.
        return $hashInteger % $this->capacity;
    }
    private function addToBucket(mixed $value): bool
    {
        $index = $this->tableHash($value);
        return $this->table[$index]->add($value);
    }
    public function insert(mixed $value): void
    {
        if (!$this->addToBucket($value)) {
            return;
        }
        if ($this->getLoadFactor() > 0.7) {
            $this->resize();
        }
    }

    public function delete(mixed $value): bool
    {
        $index = $this->tableHash($value);
        return $this->table[$index]->remove($value);
    }
    /**
     * @param T $oldValue
     * @param T $newValue
     *
     * @return bool
     *
     * @throws InvalidArgumentException If either value cannot be used as a hash table value.
     */
    public function update(mixed $oldValue, mixed $newValue): bool
    {
        try {
            $position = $this->getValuePosition($oldValue);
            if ($position === false) {
                return false;
            }

            if (GeneralArrayAlgorithms::equals($oldValue, $newValue)) {
                return true;
            }
            $newIndex = $this->tableHash($newValue);
        } catch (InvalidArgumentException) {
            return false;
        }

        if ($this->table[$newIndex]->contains($newValue)) {
            return false;
        }

        $this->table[$position['bucketIndex']]->remove($oldValue);
        $this->table[$newIndex]->add($newValue);

        return true;
    }
    public function hasValue(mixed $value): bool
    {
        $index = $this->tableHash($value);
        return $this->table[$index]->contains($value);
    }

    public function getValuePosition(mixed $value): bool|array
    {
        $index = $this->tableHash($value);

        $itemIndex = $this->table[$index]->indexOf($value);

        if ($itemIndex === false) {
            return false;
        }

        return [
            'bucketIndex' => $index,
            'itemIndex' => $itemIndex,
        ];
    }



    public function getAllValues(): array
    {
        $values = [];
        foreach ($this->table as $bucket) {
            foreach ($bucket->getIterator() as $value) {
                $values[] = $value;
            }
        }
        return $values;
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
        return $this->getSize() === 0;
    }

    public function clear(): void
    {
        $this->table = [];

        for ($i = 0; $i < $this->capacity; $i++) {
            $this->table[$i] = new Set();
        }
    }

    public function reset(): void
    {
        $this->capacity = 10;
        $this->table = [];

        for ($i = 0; $i < $this->capacity; $i++) {
            $this->table[$i] = new Set();
        }
    }

    public function resize(): void
    {
        $values = $this->getAllValues();

        $this->capacity *= 2;

        $this->table = [];

        for ($i = 0; $i < $this->capacity; $i++) {
            $this->table[$i] = new Set();
        }
        foreach ($values as $value) {
            $this->addToBucket($value);
        }
    }
}
