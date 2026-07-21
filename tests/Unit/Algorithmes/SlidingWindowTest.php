<?php

namespace Tests\Unit\Algorithmes;

use PHPUnit\Framework\TestCase;
use Zack\PhpDsAlgo\Algorithmes\SlidingWindow;

class SlidingWindowTest extends TestCase
{
    public function testProcessesEveryFixedSizeWindowInOrder(): void
    {
        $windows = [];

        SlidingWindow::processFixedSizeSlidingWindow(
            [1, 2, 3, 4, 5],
            3,
            function (array $window, int $startIndex) use (&$windows) {
                $windows[] = ['window' => $window, 'start' => $startIndex];
            }
        );

        $this->assertSame([
            ['window' => [1, 2, 3], 'start' => 0],
            ['window' => [2, 3, 4], 'start' => 1],
            ['window' => [3, 4, 5], 'start' => 2],
        ], $windows);
    }

    public function testWindowSizeEqualToArrayLengthProducesSingleWindow(): void
    {
        $windows = [];

        SlidingWindow::processFixedSizeSlidingWindow(
            [1, 2, 3],
            3,
            function (array $window, int $startIndex) use (&$windows) {
                $windows[] = $window;
            }
        );

        $this->assertSame([[1, 2, 3]], $windows);
    }

    public function testWindowSizeGreaterThanArrayLengthNeverInvokesCallback(): void
    {
        $called = false;

        SlidingWindow::processFixedSizeSlidingWindow(
            [1, 2, 3],
            10,
            function () use (&$called) {
                $called = true;
            }
        );

        $this->assertFalse($called);
    }

    public function testZeroWindowSizeNeverInvokesCallback(): void
    {
        $called = false;

        SlidingWindow::processFixedSizeSlidingWindow(
            [1, 2, 3],
            0,
            function () use (&$called) {
                $called = true;
            }
        );

        $this->assertFalse($called);
    }

    public function testNegativeWindowSizeNeverInvokesCallback(): void
    {
        $called = false;

        SlidingWindow::processFixedSizeSlidingWindow(
            [1, 2, 3],
            -1,
            function () use (&$called) {
                $called = true;
            }
        );

        $this->assertFalse($called);
    }

    public function testEmptyArrayNeverInvokesCallback(): void
    {
        $called = false;

        SlidingWindow::processFixedSizeSlidingWindow(
            [],
            1,
            function () use (&$called) {
                $called = true;
            }
        );

        $this->assertFalse($called);
    }
}
