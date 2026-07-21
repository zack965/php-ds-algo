<?php

namespace Tests\Unit\Exception;

use PHPUnit\Framework\TestCase;
use RuntimeException;
use Zack\PhpDsAlgo\Exception\EdgeNotFoundException;

class EdgeNotFoundExceptionTest extends TestCase
{
    public function testEdgeNotFoundBuildsMessageWithStringValues(): void
    {
        $exception = EdgeNotFoundException::edgeNotFound('A', 'B');

        $this->assertSame(
            "No edge exists from node 'A' to node 'B'.",
            $exception->getMessage()
        );
    }

    public function testEdgeNotFoundBuildsMessageWithIntValues(): void
    {
        $exception = EdgeNotFoundException::edgeNotFound(1, 2);

        $this->assertSame(
            "No edge exists from node '1' to node '2'.",
            $exception->getMessage()
        );
    }

    public function testEdgeNotFoundIsARuntimeException(): void
    {
        $exception = EdgeNotFoundException::edgeNotFound('A', 'B');

        $this->assertInstanceOf(RuntimeException::class, $exception);
    }
}
