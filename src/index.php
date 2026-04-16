<?php

use Zack\PhpDsAlgo\DataStructure\LinkedList\Single\SingleLinkedList;

require_once "vendor/autoload.php";



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
