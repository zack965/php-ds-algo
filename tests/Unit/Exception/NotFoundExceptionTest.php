<?php

namespace Tests\Unit\Exception;

use PHPUnit\Framework\TestCase;
use RuntimeException;
use Zack\PhpDsAlgo\Exception\NotFoundException;

class NotFoundExceptionTest extends TestCase
{
    public function testNodeNotFoundBuildsMessageWithStringValue(): void
    {
        $exception = NotFoundException::nodeNotFound('A');

        $this->assertSame("Node with value 'A' was not found.", $exception->getMessage());
    }

    public function testNodeNotFoundBuildsMessageWithIntValue(): void
    {
        $exception = NotFoundException::nodeNotFound(42);

        $this->assertSame("Node with value '42' was not found.", $exception->getMessage());
    }

    public function testNodeNotFoundIsARuntimeException(): void
    {
        $exception = NotFoundException::nodeNotFound('A');

        $this->assertInstanceOf(RuntimeException::class, $exception);
    }
}
