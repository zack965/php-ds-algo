<?php


namespace Zack\PhpDsAlgo\DataStructure\Heap;

use Zack\PhpDsAlgo\Helpers\Algorythmes\AlgorythmesGlobalHelpers;

class MinHeap extends AbstractBinaryHeap
{
    protected function getDefaultComparator(): callable
    {
        return function ($a, $b): int {
            if ($a == $b) {
                return 0;
            }
            return $a < $b ? -1 : 1;
        };
    }

    public function heapifyUp(int $index): void
    {
        $currentIndex = $index;
        while ($currentIndex != 0) {

            $parentIndex = $this->getParentIndex($currentIndex);
            if ($this->compare($this->heap[$currentIndex], $this->heap[$parentIndex]) >= 0) {
                break;
            }
            AlgorythmesGlobalHelpers::swapValuesOfArray($this->heap, $currentIndex, $parentIndex);

            $currentIndex = $parentIndex;
        }
    }
    public function heapifyDown(int $index): void
    {
        $currentIndex = $index;
        while (($this->hasLeftChild($currentIndex))) {
            $leftChild = $this->getLeftChildIndex($currentIndex);
            $rightChild = $this->getRightChildIndex($currentIndex);
            // determine the smallest child index
            if (!$this->hasRightChild($currentIndex)) {
                $smallestChildIndex = $leftChild;
            } else {
                $smallestChildIndex = $this->heap[$leftChild] <= $this->heap[$rightChild]
                    ? $leftChild
                    : $rightChild;
            }
            //      if ($this->heap[$currentIndex] <= $this->heap[$smallestChildIndex]) {
            if ($this->compare($this->heap[$currentIndex], $this->heap[$smallestChildIndex]) <= 0) {
                break;
            } else {
                AlgorythmesGlobalHelpers::swapValuesOfArray($this->heap, $currentIndex, $smallestChildIndex);
            }
            $currentIndex = $smallestChildIndex;
        }
    }
}
