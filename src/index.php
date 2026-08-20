<?php

use Zack\PhpDsAlgo\Algorithmes\ArraySearchAlogorthme;
use Zack\PhpDsAlgo\Algorithmes\DijkstraAlgorithm\DijkstraAlgorithm;
use Zack\PhpDsAlgo\Algorithmes\GraphBreadthFirstTraversal;
use Zack\PhpDsAlgo\Algorithmes\GraphDepthFirstTraversal;
use Zack\PhpDsAlgo\DataStructure\Graph\Graph;
use Zack\PhpDsAlgo\DataStructure\Heap\MaxHeap;
use Zack\PhpDsAlgo\DataStructure\Heap\MinHeap;

require_once "vendor/autoload.php";




$array_big = [2, 34, 1, 2, 7, 6, 1, 9, 22, 75, 222];
$array = [7, 23, 134, 451, 892];
$array_small = [2, 34, 1, 2];



$data = [
    'A' => [
        ['destination' => 'B', 'weight' => 2],
        ['destination' => 'C', 'weight' => 6],
    ],
    'B' => [
        ['destination' => 'D', 'weight' => 5],
        ['destination' => 'A', 'weight' => 2],
        ['destination' => 'C', 'weight' => 9],
    ],
    'C' => [
        ['destination' => 'A', 'weight' => 6],
        ['destination' => 'D', 'weight' => 8],
        ['destination' => 'B', 'weight' => 9],
    ],
    'D' => [
        ['destination' => 'B', 'weight' => 5],
        ['destination' => 'C', 'weight' => 8],

    ],
];
$graph = new Graph();
$graph->buildFromAdjencyList($data);
$graph->display();
$dj = new DijkstraAlgorithm();
$dj->calculateDistances($graph, "A");

$dj->display();
print_r($dj->findShortestPath("D"));
/*
print_r( */
/* $heap = new MinHeap([1, 3, 5, 7, 9, 11, 13]);
$heap->insert(2); */
/* print_r($heap->getData()); */
