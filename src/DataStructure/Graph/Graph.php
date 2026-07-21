<?php


namespace Zack\PhpDsAlgo\DataStructure\Graph;

use Traversable;
use InvalidArgumentException;
use Zack\PhpDsAlgo\Contracts\IGraph;
use Zack\PhpDsAlgo\Exception\DuplicateNodeException;
use Zack\PhpDsAlgo\Exception\EdgeNotFoundException;
use Zack\PhpDsAlgo\Exception\NotFoundException;

final class Graph implements IGraph
{

    public function getNodesCount(): int
    {
        return count($this->nodes);
    }
    /**
     * @var array<int|string>
     */
    private array $nodes;
    /**
     * Adjacency list.
     *
     * @var array<int|string, array<GraphEdge>>
     */
    private array $adjacency;

    /**
     * Get adjacency list.

     *
     * @return array<int|string, array<int|string>>
     */
    public function getAdjency()
    {
        return $this->adjacency;
    }


    /**
     * @var GraphEdge[]
     */
    public function getEdges(): array
    {
        $edges = [];

        foreach ($this->adjacency as $nodeEdges) {
            foreach ($nodeEdges as $edge) {
                $edges[] = $edge;
            }
        }

        return $edges;
    }
    public function getNodes(): array
    {
        return $this->nodes;
    }

    private bool $directed;
    private bool $weighted;
    public function __construct(
        bool $directed = true,
        bool $weighted = false
    ) {
        $this->directed = $directed;
        $this->weighted = $weighted;
        $this->nodes = [];
        $this->adjacency = [];
    }
    /**
     * isDirected
     *
     * @return bool
     */
    public function isDirected(): bool
    {
        return $this->directed;
    }

    /**
     * isWeighted
     *
     * @return bool
     */
    public function isWeighted(): bool
    {
        return $this->weighted;
    }
    /**
     * isEmpty
     *
     * @return bool
     */
    public function isEmpty(): bool
    {
        return count($this->nodes) === 0;
    }
    public function clearNodes(): static
    {
        $this->nodes = [];
        return $this;
    }
    public function clear(): static
    {
        $this->adjacency = [];
        $this->nodes = [];
        return $this;
    }


    public function getIterator(): Traversable
    {
        yield from $this->adjacency;
    }
    /**
     * removeNode
     *
     * @param  mixed $value
     * @return static
     */
    public function removeNode(int|string $value): static
    {
        if (!$this->hasNode($value)) {
            throw new InvalidArgumentException("No node with this value : `{$value}`");
        }
        $key = array_search($value, $this->nodes, true);
        if ($key === false) {
            throw new InvalidArgumentException("No node with value '{$value}'.");
        }

        unset($this->nodes[$key]);
        $this->nodes = array_values($this->nodes);

        // Remove all outgoing edges.
        unset($this->adjacency[$value]);

        foreach ($this->adjacency as &$edges) {
            $edges = array_values(array_filter(
                $edges,
                fn(GraphEdge $edge) =>
                $edge->getDestinationNode() !== $value
            ));
        }
        return $this;
    }

    /**
     * Build a graph from an adjacency list.
     *
     * @param array<int|string, array<int, int|string>> $data
     * @return void
     */
    public function buildFromAdjencyList(array $data): void
    {
        $this->clear();

        // First pass: add every node.
        foreach ($data as $source => $neighbors) {
            if (!$this->hasNode($source)) {
                $this->addNode($source);
            }

            foreach ($neighbors as $destination) {
                if (!$this->hasNode($destination)) {
                    $this->addNode($destination);
                }

                $this->addEdge($source, $destination);
            }
        }
    }

    /**
     * addNode
     *
     * @param  int|string $value
     * @return static
     */
    public function addNode(int|string $value): static
    {
        if ($this->hasNode($value)) {
            throw DuplicateNodeException::nodeDuplicate($value);
        }
        $this->nodes[] = $value;
        $this->adjacency[$value] = [];
        return $this;
    }


    /**
     * hasNode
     *
     * @param  int|string $value
     * @return bool
     */
    public function hasNode(int|string $value): bool
    {
        return in_array($value, $this->nodes, true);
    }
    /**
     * addEdge
     *
     * @param  int|string $sourceNode
     * @param  int|string $destinationNode
     * @return GraphEdge
     */
    public function addEdge(
        int|string $sourceNode,
        int|string $destinationNode,
        int|float|null $weight = null,
        array $metadata = [],
    ): GraphEdge {
        $newEdge =  new GraphEdge($sourceNode, $destinationNode, $weight, $metadata);



        $this->CheckNodesExists($sourceNode, $destinationNode);
        $this->adjacency[$sourceNode][] = $newEdge;
        return $newEdge;
    }

    public function removeEdge(int|string $source, int|string $destination): void
    {
        $this->CheckNodesExists($source, $destination);


        $this->adjacency[$source] = array_values(array_filter(
            $this->adjacency[$source] ?? [],
            fn(GraphEdge $edge) =>
            $edge->getDestinationNode() !== $destination
        ));

        if (!$this->directed) {
            $this->adjacency[$destination] = array_values(array_filter(
                $this->adjacency[$destination] ?? [],
                fn(GraphEdge $edge) =>
                $edge->getDestinationNode() !== $source
            ));
        }
    }
    public function hasEdge(int|string $source, int|string $destination): bool
    {
        $this->CheckNodesExists($source, $destination);
        foreach ($this->getNeighbors($source) as $edge) {
            if ($edge->getDestinationNode() === $destination) {
                return true;
            }
        }
        return false;
    }



    public function getNode(int|string $value): int|string
    {
        if (!$this->hasNode($value)) {
            throw new NotFoundException("No node with value '{$value}' was found.");
        }

        return $value;
    }

    private function CheckNodesExists(int|string $source, int|string $destination): void
    {
        if (!$this->hasNode($source)) {
            throw new InvalidArgumentException("Source node does not exist.");
        }
        if (!$this->hasNode($destination)) {
            throw new InvalidArgumentException("Destination node does not exist.");
        }
    }
    /**
     * getNeighbors
     *
     * @param  mixed $value
     * @return GraphEdge[]
     */
    public function getNeighbors(int|string $value): array
    {
        return $this->adjacency[$value] ?? [];
    }
    /**
     * getEdge
     *
     * @return GraphEdge
     */
    public function getEdge(
        mixed $source,
        mixed $destination
    ): GraphEdge {
        $this->CheckNodesExists($source, $destination);
        foreach ($this->adjacency[$source] as $edge) {
            if ($edge->getDestinationNode() === $destination) {
                return $edge;
            }
        }
        throw EdgeNotFoundException::edgeNotFound($source, $destination);
    }
    /**
     * Returns all outgoing edges (edges leaving the given node).
     *
     * @param  int|string $node
     * @return array<GraphEdge>
     */
    public function getOutgoingEdges(int|string $node): array
    {
        $edges = [];
        foreach ($this->adjacency as $source => $sourceEdges) {
            foreach ($sourceEdges as $edge) {
                if ($edge->getSourceNode() === $node) {
                    $edges[] = $edge;
                }
            }
        }
        return $edges;
    }
    /**
     * Returns all incoming edges (edges entering the specified node).
     *
     * @param  int|string $node
     * @return array<GraphEdge>
     */
    public function getIncomingEdges(int|string $node): array
    {
        $edges = [];
        foreach ($this->adjacency as $source => $sourceEdges) {
            foreach ($sourceEdges as $edge) {
                if ($edge->getDestinationNode() === $node) {
                    $edges[] = $edge;
                }
            }
        }
        return $edges;
    }
    /**
     * Returns all incident edges (edges connected to the specified node).
     *
     * @param  int|string $node
     * @return array<GraphEdge>
     */
    public function getIncidentEdges(int|string $node): array
    {
        $edges = [];
        foreach ($this->adjacency as $source => $sourceEdges) {
            foreach ($sourceEdges as $edge) {
                if ($edge->getSourceNode() === $node || $edge->getDestinationNode() === $node) {
                    $edges[] = $edge;
                }
            }
        }
        return $edges;
    }

    public function clearEdges(): static
    {
        foreach (array_keys($this->adjacency) as $node) {
            $this->adjacency[$node] = [];
        }
        return $this;
    }
    public function getEdgeCount(): int
    {
        return count($this->getEdges());
    }

    /**
     * Display the graph as a formatted adjacency list.
     *
     * @return void
     */
    public function display(): void
    {
        echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
        echo " Graph\n";
        echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
        echo "Nodes : {$this->getNodesCount()}\n";
        echo "Edges : " . $this->getEdgeCount() . "\n\n";

        foreach ($this->adjacency as $source => $edges) {


            if ($edges === []) {
                echo "● {$source} []" . PHP_EOL;
                continue;
            }
            echo "● {$source}\n";

            $last = array_key_last($edges);

            foreach ($edges as $index => $edge) {

                $branch = $index === $last
                    ? "└──►"
                    : "├──►";

                $destination = $edge
                    ->getDestinationNode();

                echo "   {$branch} {$destination}\n";
            }



            echo PHP_EOL;
        }
    }
}
