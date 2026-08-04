<?php

use Zack\PhpDsAlgo\Algorithmes\GraphBreadthFirstTraversal;
use Zack\PhpDsAlgo\Algorithmes\GraphDepthFirstTraversal;
use Zack\PhpDsAlgo\DataStructure\Graph\Graph;

require_once "vendor/autoload.php";




$array_big = [2, 34, 1, 2, 7, 6, 1, 9, 22, 75, 222];
$array = [23, 451, 7, 892, 134];
$array_small = [2, 34, 1, 2];


$data = [
    "a" => ["b", "c"],
    "b" => ["d"],
    "c" => ["e"],
    "d" => [],
    "e" => ["b"],
    "f" => ["d"]
];
$dataCycle = [
    'A' => ['B'],
    'B' => ['C'],
    'C' => ['A'],
];
$graph = new Graph();
$graph->buildFromAdjencyList($dataCycle);
$graph->display();
/* echo "BFS : " . PHP_EOL;
foreach (GraphBreadthFirstTraversal::traverse($graph, 'a') as $node) {
    echo $node . PHP_EOL;
}
echo "DFS : " . PHP_EOL;
foreach (GraphDepthFirstTraversal::traverse($graph, 'a') as $node) {
    echo $node . PHP_EOL;
}
 */