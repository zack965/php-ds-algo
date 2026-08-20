<?php

namespace Zack\PhpDsAlgo\DataStructure\HashTabe;

/**
 * @template K
 * @template V
 */
class HashMapNode
{
    /**
     * @param K $key
     * @param V $value
     */
    public function __construct(
        public readonly mixed $key,
        public mixed $value,
    ) {}

    /**
     * @return K
     */
    public function getKey(): mixed
    {
        return $this->key;
    }

    /**
     * @return V
     */
    public function getValue(): mixed
    {
        return $this->value;
    }

    /**
     * @param V $value
     */
    public function setValue(mixed $value): void
    {
        $this->value = $value;
    }
}
