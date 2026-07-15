<?php


namespace Zack\PhpDsAlgo\Exception;

use RuntimeException;

final class NotFoundException extends RuntimeException
{
    public static function nodeNotFound(mixed $value): self
    {
        return new self(
            sprintf("Graph node with value '%s' was not found.", (string) $value)
        );
    }
}
