<?php


namespace Zack\PhpDsAlgo\Helpers\Algorythmes;

class DijkstraAlgorithmTable
{

    private array $table = [];

    public function addNode(string $node): void
    {
        $this->table[$node] = [
            'source_node' => $node,
            'distance' => INF,
            'previous_node' => null,
            'visited' => false,
        ];
    }

    public function setDistance(string $node, int|float $distance): void
    {
        $this->table[$node]['distance'] = $distance;
    }

    public function getDistance(string $node): int|float
    {
        return $this->table[$node]['distance'];
    }

    public function setPreviousNode(string $node, ?string $previousNode): void
    {
        $this->table[$node]['previous_node'] = $previousNode;
    }

    public function getPreviousNode(string $node): ?string
    {
        return $this->table[$node]['previous_node'];
    }

    public function setVisited(string $node, bool $visited = true): void
    {
        $this->table[$node]['visited'] = $visited;
    }

    public function isVisited(string $node): bool
    {
        return $this->table[$node]['visited'];
    }

    public function get(string $node): array
    {
        return $this->table[$node];
    }

    public function all(): array
    {
        return $this->table;
    }
}
