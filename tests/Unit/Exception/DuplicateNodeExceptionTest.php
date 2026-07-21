<?php

namespace Tests\Unit\Exception;

use PHPUnit\Framework\TestCase;
use RuntimeException;
use Zack\PhpDsAlgo\Exception\DuplicateNodeException;

class DuplicateNodeExceptionTest extends TestCase
{
    public function testNodeDuplicateBuildsMessageWithStringValue(): void
    {
        $exception = DuplicateNodeException::nodeDuplicate('A');

        $this->assertSame("Graph node with value 'A' was repeated.", $exception->getMessage());
    }

    public function testNodeDuplicateBuildsMessageWithIntValue(): void
    {
        $exception = DuplicateNodeException::nodeDuplicate(42);

        $this->assertSame("Graph node with value '42' was repeated.", $exception->getMessage());
    }

    public function testNodeDuplicateIsARuntimeException(): void
    {
        $exception = DuplicateNodeException::nodeDuplicate('A');

        $this->assertInstanceOf(RuntimeException::class, $exception);
    }
}
