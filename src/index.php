<?php

use Zack\PhpDsAlgo\Algorithmes\ArraySortAlgorythmes;
use Zack\PhpDsAlgo\Algorithmes\SlidingWindow;
use Zack\PhpDsAlgo\DataStructure\Graph\DirectedGraph;
use Zack\PhpDsAlgo\DataStructure\LinkedList\Doubly\DoublyLinkedList;
use Zack\PhpDsAlgo\DataStructure\LinkedList\Single\SingleLinkedList;

require_once "vendor/autoload.php";


/* $list = DoublyLinkedList::of([1, 3]);

$result = $list->insert(2, 2);

//$middle = $result->get(1);
print_r($result->toArrayValues()); */


$array_big = [2, 34, 1, 2, 7, 6, 1, 9, 22, 75, 222];
$array = [23, 451, 7, 892, 134];
$array_small = [2, 34, 1, 2];

//$sorted = ArraySortAlgorythmes::insertionSort($array_big);
//print_r($sorted);

$data = [
    "a" => ["b", "c"],
    "b" => ["d"],
    "c" => ["e"],
    "d" => [],
    "e" => ["b"],
    "f" => ["d"]
];
$graph = new DirectedGraph();
$graph->buildFromAdjencyList($data);
$graph->display();

/* SlidingWindow::processFixedSizeSlidingWindow(
    $array,
    3,
    function (array $window, int $index) {
        // Here, you can process the data however you like.
        echo "Init Window ----" . PHP_EOL;
        echo "Window {$index}: ";
        print_r($window);
        echo "Ending Window ----" . PHP_EOL;
    }
); */
/* 


$list = SingleLinkedList::of([1, 2, 3])
    ->insert(99, 2)
    ->append(45)
    ->prepend(11);

echo "Normal list loop start " . PHP_EOL;
foreach ($list as $node) {
    echo $node->getValue() . PHP_EOL;
}
echo "Normal list loop end " . PHP_EOL;
echo "Linked list length is : " . $list->getLength() . PHP_EOL;


echo "Valus of index is : " . $list->get(2)->getValue() . PHP_EOL;
echo "Valus of Tail is : " . $list->getTail()->getValue() . PHP_EOL;
echo "Linkedlist contains value is : " . ($list->contains(112)?->getValue() ?? "Not found") . PHP_EOL;
echo "Linkedlist index value is : " . ($list->indexOf(11) ?? "Not found") . PHP_EOL;
$list->reverse();

echo "reverse list loop start " . PHP_EOL;

foreach ($list as $node) {
    echo $node->getValue() . PHP_EOL;
}
echo "reverse list loop end " . PHP_EOL;
 */