<?php


namespace Zack\PhpDsAlgo\DataStructure\Graph;

class GraphEdge
{

    private int|float|null $weight;
    private array $metadata;
    public function __construct(
        private int|string $sourceNode,
        private int|string $destinationNode,
        int|float|null $weight = null,
        array $metadata = [],
    ) {
        $this->weight = $weight;
        $this->metadata = $metadata;
    }
    public function getSourceNode(): int|string
    {
        return $this->sourceNode;
    }

    public function getDestinationNode(): int|string
    {
        return $this->destinationNode;
    }

    /**
     * Get the value of metadata
     *
     * @return array<mixed>
     */
    public function getMetadata(): array
    {
        return $this->metadata;
    }

    public function getWeight(): int|float|null
    {
        return $this->weight;
    }
}
