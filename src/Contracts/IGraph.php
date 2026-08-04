<?php


namespace Zack\PhpDsAlgo\Contracts;

use Zack\PhpDsAlgo\DataStructure\Graph\GraphEdge;
use Traversable;

interface IGraph
{
    /*--------------------------------------------------------------------------
    | Nodes
    *-------------------------------------------------------------------------*/

    public function addNode(int|string $value): static;

    public function removeNode(int|string $value): static;

    public function hasNode(int|string $value): bool;

    public function getNode(int|string $value): int|string;
    public function hasEdge(int|string $source, int|string $destination): bool;


    public function getNodes(): array;
    public function clearNodes(): static;

    /**
     * Build a graph from an adjacency list.
     *
     * @param array<int|string, array<int, int|string>> $data
     */
    public function buildFromAdjencyList(array $data): void;
    /*--------------------------------------------------------------------------
    | Edges
    *-------------------------------------------------------------------------*/
    /**
     * Returns all outgoing edges for the given node.
     *
     * @param int|string $node
     * @return GraphEdge[]
     */
    public function getNeighbors(int|string $node): array;


    public function getEdge(
        mixed $source,
        mixed $destination
    ): GraphEdge;

    public function getOutgoingEdges(int|string $node): array;

    public function getIncomingEdges(int|string $node): array;

    public function getIncidentEdges(int|string $node): array;


    public function clearEdges(): static;
    public function clear(): static;
    public function display(): void;



    public function getNodesCount(): int;

    public function getEdgeCount(): int;

    public function isDirected(): bool;

    public function isWeighted(): bool;

    public function getAdjency(): array;

    /**
     * Build a square adjacency matrix from the graph's nodes.
     *
     * Row 0 / column 0 hold the node labels; cell [i][j] is 1 when an edge
     * exists from the i-th node to the j-th node, 0 otherwise.
     *
     * @return array<int, array<int, int|string>>
     */
    public function getAdjencyMetrix(): array;

    /**
     * Print the adjacency matrix built by getAdjencyMetrix() to stdout.
     */
    public function printAdjacencyMatrix(): void;

    public function getIterator(): Traversable;
}
