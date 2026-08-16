<?php


namespace Zack\PhpDsAlgo\DataStructure\Heap;

use RuntimeException;
use Zack\PhpDsAlgo\Contracts\IHeap;
use Zack\PhpDsAlgo\Helpers\Algorythmes\AlgorythmesGlobalHelpers;

class MinHeap implements IHeap
{

    /**
     * @var int[]
     */
    private array $data = [];


    public function __construct(array $data)
    {
        $this->data = $data;
        $this->checker();
    }




    private function checker()
    {
        $smallest = $this->data[0];
        for ($i = 0; $i < count($this->data); $i++) {
            $item = $this->data[$i];
            if ($item < $smallest) {
                throw new RuntimeException("Unordered Data");
            }
        }
    }
    private function getParent(int $i): int
    {
        return floor(($i - 1) / 2);
    }
    private function getLeftChild(int $i): int
    {
        return 2 * $i + 1;
    }
    private function getRightChild(int $i): int
    {
        return 2 * $i + 2;
    }
    public function insert(mixed $value): void
    {
        if (empty($this->data)) {
            $this->data[] = $value;
            return;
        }
        $this->data[] = $value;
        $currentIndex = count($this->data) - 1;
        $parentIndex = $this->getParent($currentIndex);

        while ($this->data[$currentIndex] < $this->data[$parentIndex]) {

            if ($this->data[$parentIndex] <= $this->data[$currentIndex]) {
                break;
                //   return;
            } else {
                AlgorythmesGlobalHelpers::swapValuesOfArray($this->data, $parentIndex, $currentIndex);
                $currentIndex = $parentIndex;
                if ($currentIndex == 0) {
                    break;
                }
                $parentIndex = $this->getParent($currentIndex);
            }
        }

        $this->checker();
    }
    private function hasLeftChild(int $i): bool
    {
        return $this->getLeftChild($i) < count($this->data);
    }
    public function extract(): mixed
    {
        if (empty($this->data)) {
            throw new RuntimeException("The heap is empty");
        }
        if (count($this->data) == 1) {
            $root = $this->data[0];

            $this->data = [];
            return $root;
        }
        $root = $this->data[0];
        $lastElement = $this->data[count($this->data) - 1];
        // starting process
        $this->data[0] = $lastElement;
        array_pop($this->data);
        //        $this->data = array_pop($this->data);
        $currentIndex = 0;
        while (($this->hasLeftChild($currentIndex))) {

            $leftChild = $this->getLeftChild($currentIndex);
            $rightChild = $this->getRightChild($currentIndex);

            if (!isset($this->data[$rightChild])) {
                $smallest = $leftChild;
            } else {
                $smallest = $this->data[$leftChild] <= $this->data[$rightChild]
                    ? $leftChild
                    : $rightChild;
            }
            if ($this->data[$currentIndex] <= $this->data[$smallest]) {
                break;
            }
            AlgorythmesGlobalHelpers::swapValuesOfArray($this->data, $currentIndex, $smallest);
            $currentIndex = $smallest;
        }



        return $root;
    }

    public function peek(): mixed
    {
        if (empty($this->data)) {
            throw new RuntimeException("The heap is empty");
        }
        return $this->data[0];
    }



    public function isEmpty(): bool
    {
        return count($this->data) == 0;
    }

    public function size(): int
    {
        return count($this->data);
    }

    /**
     * Get the value of data
     *
     * @return array<mixed>
     */
    public function getData()
    {
        return $this->data;
    }
}
