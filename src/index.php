<?php

use Zack\PhpDsAlgo\Algorithmes\ArraySearchAlogorthme;
use Zack\PhpDsAlgo\Algorithmes\GraphBreadthFirstTraversal;
use Zack\PhpDsAlgo\Algorithmes\GraphDepthFirstTraversal;
use Zack\PhpDsAlgo\DataStructure\Graph\Graph;

require_once "vendor/autoload.php";




$array_big = [2, 34, 1, 2, 7, 6, 1, 9, 22, 75, 222];
$array = [7, 23, 134, 451, 892];
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
/* $graph->display();
print_r($graph->getAdjencyMetrix());
$graph->printAdjacencyMatrix(); */

echo "result is : " . ArraySearchAlogorthme::FibonacciSearch($array, 451) . PHP_EOL;
