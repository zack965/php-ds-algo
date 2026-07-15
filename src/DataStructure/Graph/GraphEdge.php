<?php


namespace Zack\PhpDsAlgo\DataStructure\Graph;

class GraphEdge
{

    private GraphNode $sourceNode;
    private GraphNode $destinationNode;

    private int $weight;
    private array $metadata;
    public function __construct(GraphNode $sourceNode, GraphNode $destinationNode)
    {
        $this->sourceNode = $sourceNode;
        $this->destinationNode = $destinationNode;
    }
    public function getSourceNode(): GraphNode
    {
        return $this->sourceNode;
    }

    public function getDestinationeEnd(): GraphNode
    {
        return $this->destinationNode;
    }
}
