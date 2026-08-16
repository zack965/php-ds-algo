<?php


namespace Zack\PhpDsAlgo\DataStructure\Graph;

use Traversable;
use InvalidArgumentException;
use Zack\PhpDsAlgo\Algorithmes\GeneralArrayAlgorithms;
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
    public function getAdjency(): array
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

    public function getAdjencyMetrix(): array
    {
        $data = [];
        $leftPos = 1;
        $rightPos = 1;
        $index = 0;
        // build borders
        foreach ($this->adjacency as $source => $edges) {

            $data[0][$leftPos] = $source;
            $data[$rightPos][0] = $source;

            $leftPos++;
            $rightPos++;
            $index++;
        }
        $keys = array_keys($this->adjacency);
        $count = count($keys) - 1;
        for ($i = 0; $i <= $count; $i++) {
            for ($j = 0; $j <= $count; $j++) {
                $currentSource = $keys[$i];
                $currentDestination = $keys[$j];
                $exists = false;

                foreach ($this->getNeighbors($currentSource) as $edge) {
                    if ($edge->getDestinationNode() === $currentDestination) {
                        $exists = true;
                        break;
                    }
                }

                $data[$i + 1][$j + 1] = $exists ? 1 : 0;
            }
        }
        return $data;
    }
    public  function printAdjacencyMatrix(): void
    {
        $matrix = $this->getAdjencyMetrix();

        if ($matrix === []) {
            echo "Graph is empty." . PHP_EOL;
            return;
        }

        // Header
        echo "    ";
        foreach ($matrix[0] as $label) {
            printf("%-3s", $label);
        }
        echo PHP_EOL;

        echo "   +" . str_repeat("---", count($matrix[0])) . PHP_EOL;

        // Rows
        for ($i = 1; $i < count($matrix); $i++) {
            printf("%-3s| ", $matrix[$i][0]);

            for ($j = 1; $j < count($matrix[0]) + 1; $j++) {
                printf("%-3s", $matrix[$i][$j] ?? 0);
            }

            echo PHP_EOL;
        }
    }

    private bool $directed;
    private bool $weighted;
    public function __construct(
        bool $directed = true,
    ) {
        $this->directed = $directed;
        $this->weighted = false;
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
     * $data maps each source node to a list of edge rows describing its
     * outgoing edges. Each row is an array with:
     *   - 'destination' (int|string, required) — the destination node.
     *   - 'weight'      (int|float|null, optional, default null) — passed
     *                    straight to addEdge(); the graph becomes weighted
     *                    as soon as a row supplies a non-null weight (see
     *                    addEdge()), and mixing weighted/unweighted rows
     *                    throws, same as calling addEdge() directly.
     *   - 'metadata'    (array, optional, default []) — arbitrary edge data.
     *
     * Example:
     *   [
     *       'A' => [
     *           ['destination' => 'B', 'weight' => 4.5, 'metadata' => ['label' => 'road']],
     *           ['destination' => 'C', 'weight' => 2.0],
     *       ],
     *       'B' => [
     *           ['destination' => 'C', 'weight' => 1.0],
     *       ],
     *   ]
     *
     * Nodes are created automatically for every source and destination
     * encountered (including nodes that only ever appear as a destination),
     * and clear() is called first to discard any existing graph state.
     *
     * @param array<int|string, array<int, array{destination: int|string, weight?: int|float|null, metadata?: array}>> $data
     * @return void
     */
    public function buildFromAdjencyList(array $data): void
    {
        $this->clear();
        // First pass: add every node.
        foreach ($data as $source => $edges) {
            if (!$this->hasNode($source)) {
                $this->addNode($source);
            }

            foreach ($edges as $edge) {
                $destination = $edge['destination'];

                if (!$this->hasNode($destination)) {
                    $this->addNode($destination);
                }

                $this->addEdge(
                    $source,
                    $destination,
                    $edge['weight'] ?? null,
                    $edge['metadata'] ?? [],
                );
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
        // First edge determines whether the graph is weighted.
        if ($this->getEdgeCount() === 0) {
            $this->weighted = $weight !== null;
        } elseif ($this->weighted !== ($weight !== null)) {
            throw new InvalidArgumentException(
                'A graph cannot mix weighted and unweighted edges.'
            );
        }


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
