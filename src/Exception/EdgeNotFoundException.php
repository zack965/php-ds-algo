<?php


namespace Zack\PhpDsAlgo\Exception;

use RuntimeException;



class EdgeNotFoundException extends RuntimeException
{
    public static function edgeNotFound(
        int|string $source,
        int|string $destination
    ): self {
        return new self(
            sprintf(
                "No edge exists from node '%s' to node '%s'.",
                (string) $source,
                (string) $destination
            )
        );
    }
}
