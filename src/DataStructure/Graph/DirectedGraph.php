<?php


namespace Zack\PhpDsAlgo\DataStructure\Graph;

use Zack\PhpDsAlgo\Algorithmes\GeneralArrayAlgorythmes;
use Zack\PhpDsAlgo\Exception\NotFoundException;

class DirectedGraph
{

    private int $length = 0;
    public function getLength(): int
    {
        return $this->length;
    }
    /**
     * @var GraphNode[]
     */
    private array $nodes;
    /**
     * Adjacency list.
     *
     * @var array<int|string, array<GraphEdge>>
     */
    private array $adjency;

    /**
     * Get adjacency list.

     *
     * @return array<int|string, array<int|string>>
     */
    public function getAdjency()
    {
        return $this->adjency;
    }


    /**
     * @var GraphEdge[]
     */
    private array $edges;
    public function getEdges(): array
    {
        return $this->edges;
    }
    public function getNodes(): array
    {
        return $this->nodes;
    }


    public function __construct()
    {
        $this->nodes = [];
        $this->edges = [];
        $this->adjency = [];
    }

    /**
     * addNode
     *
     * @param  int|string $value
     * @return GraphNode
     */
    public function addNode(int|string $value): GraphNode
    {
        $newNode = new GraphNode($value);
        array_push($this->nodes, $newNode);
        $this->length += 1;
        return $newNode;
    }
    /**
     * addEdge
     *
     * @param  GraphNode $sourceNode
     * @param  GraphNode $destinationNode
     * @return GraphEdge
     */
    public function addEdge(GraphNode $sourceNode, GraphNode $destinationNode): GraphEdge
    {
        $newEdge =  new GraphEdge($sourceNode, $destinationNode);
        array_push($this->edges, $newEdge);
        return $newEdge;
    }
    /**
     * buildFromAdjencyList
     *
     * @param  array $data
     * @return void
     */
    public function buildFromAdjencyList(array $data): void
    {

        $this->nodes = $this->extractNodesFromAdjencyList($data);
        $this->edges = $this->extractEdgesFromAdjencyList($data);
    }



    /**
     * extractNodesFromAdjencyList
     *
     * @param  mixed $adjacencyList
     * @return GraphNode[]
     */
    private function extractNodesFromAdjencyList(array $adjacencyList): array
    {
        GeneralArrayAlgorythmes::checkDuplicateInArray(array_keys($adjacencyList));
        $keys = array_unique(array_keys($adjacencyList));

        return array_map(
            fn($value) => $this->addNode($value),
            $keys
        );
    }
    /**
     * getNode
     *
     * @param  mixed $value
     * @return GraphNode
     */
    public function getNode(mixed $value): ?GraphNode
    {
        foreach ($this->nodes as $node) {
            if ($node->getValue() === $value) {
                return $node;
            }
        }

        return null;
    }


    /**
     * getEdgesOfNode
     *
     * @param  mixed $value
     * @return GraphEdge[]
     */
    public function getEdgesOfNode(int|string $value): array
    {
        $sourceNode = $this->getNode($value);
        $edges = [];
        foreach ($this->edges as $edge) {
            if ($edge->getSourceNode()->getValue() == $sourceNode->getValue()) {
                $edges[] = $edge;
            }
        }

        return $edges;
    }
    /**
     * extractEdgesFromAdjencyList
     *
     * @param  mixed $adjacencyList
     * @return GraphEdge[]
     */
    private function extractEdgesFromAdjencyList(array $adjacencyList): array
    {
        /**
         * @var GraphEdge[]
         */
        $appEdges = [];

        foreach ($adjacencyList as $key => $edges) {
            $sourceNode = $this->getNode($key);
            if ($sourceNode === null) {
                throw NotFoundException::nodeNotFound($key);
            }

            GeneralArrayAlgorythmes::checkDuplicateInArray($edges);
            if (empty($edges)) {
                $this->adjency[$sourceNode->getValue()] = [];
            } else {
                foreach ($edges as $edge) {

                    $destinationNode = $this->getNode($edge);
                    if ($destinationNode === null) {
                        throw NotFoundException::nodeNotFound($key);
                    }
                    $newEdge = $this->addEdge($sourceNode, $destinationNode);
                    $this->adjency[$sourceNode->getValue()][] = $newEdge;

                    array_push($appEdges, $newEdge);
                }
            }
        }
        return $appEdges;
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
        echo "Nodes : {$this->getLength()}\n";
        echo "Edges : " . count($this->edges) . "\n\n";

        foreach ($this->adjency as $source => $edges) {

            echo "● {$source}\n";

            if ($edges === []) {
                echo "● {$source} []" . PHP_EOL;
                continue;
            }

            $last = array_key_last($edges);

            foreach ($edges as $index => $edge) {

                $branch = $index === $last
                    ? "└──►"
                    : "├──►";

                $destination = $edge
                    ->getDestinationeEnd()
                    ->getValue();

                echo "   {$branch} {$destination}\n";
            }



            echo PHP_EOL;
        }
    }
}
