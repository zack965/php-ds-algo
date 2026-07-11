<?php


namespace Zack\PhpDsAlgo\DataStructure\LinkedList\Doubly;

use InvalidArgumentException;
use IteratorAggregate;

class DoublyLinkedList implements IteratorAggregate
{
    private ?DoublyLinkedListNode $head = null;
    private int $length = 0;
    private function __construct(?DoublyLinkedListNode $head = null, ?int $length = 0)
    {
        $this->head = $head;
        $this->length = $length;
    }
    public function getLength(): int
    {
        return $this->length;
    }
    public function getHead(): ?DoublyLinkedListNode
    {
        return $this->head;
    }
    public function getIterator(): \Traversable
    {
        $current = $this->head;

        while ($current !== null) {
            yield $current; // or yield $current->getValue();
            $current = $current->getNext();
        }
    }
    public static function empty(): self
    {
        return new self(null, 0);
    }
    /**
     * fromNodes
     *
     * @param  DoublyLinkedListNode[] $nodes
     * @return self
     */
    public static function fromNodes(array $nodes): self
    {
        if ($nodes === []) {
            return self::empty();
        }
        foreach ($nodes as $node) {
            if (!$node instanceof DoublyLinkedListNode) {
                throw new InvalidArgumentException(
                    'All elements must be instances of SingleLinkedListNode'
                );
            }
        }
        for ($i = 0; $i < count($nodes) - 1; $i++) {
            $nodes[$i]->setNext($nodes[$i + 1]);
            if (isset($nodes[$i - 1])) {
                $nodes[$i]->setPrevious($nodes[$i - 1]);
            }
        }
        return new self($nodes[0], count($nodes));
    }
}
