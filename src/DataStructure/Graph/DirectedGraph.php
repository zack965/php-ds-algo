<?php


namespace Zack\PhpDsAlgo\DataStructure\Graph;

use Zack\PhpDsAlgo\Algorithmes\GeneralArrayAlgorythmes;
use Zack\PhpDsAlgo\Exception\NotFoundException;

class DirectedGraph
{
    /**
     * @var GraphNode[]
     */
    private array $nodes;


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
    public function buildFromAdjencyList(): self
    {
        $data = [
            "a" => ["b", "c"],
            "b" => ["d"],
            "c" => ["e"],
            "d" => [],
            "e" => ["b"],
            "f" => ["d"]
        ];
        $this->nodes = $this->extractNodesFromAdjencyList($data);
        $this->edges = $this->extractEdgesFromAdjencyList($data);


        return new DirectedGraph([]);
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
            fn($value) => new GraphNode($value),
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


            foreach ($edges as $edge) {

                $destinationNode = $this->getNode($edge);

                if ($destinationNode === null) {
                    throw NotFoundException::nodeNotFound($key);
                }

                array_push($appEdges, new GraphEdge($sourceNode, $destinationNode));
            }
        }
        return $appEdges;
    }
}
