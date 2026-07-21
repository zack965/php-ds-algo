<?php


namespace Zack\PhpDsAlgo\Exception;

use RuntimeException;

class DuplicateNodeException extends RuntimeException
{
    public static function nodeDuplicate(int|string $value): self
    {
        return new self(
            sprintf("Graph node with value '%s' was repeated.", (string) $value)
        );
    }
}
