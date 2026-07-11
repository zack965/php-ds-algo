<?php


namespace Zack\PhpDsAlgo\Algorithmes;

class SlidingWindow
{
    /**
     * Process every fixed-size window.
     *
     * @param array $data
     * @param int $size
     * @param callable $callback function(array $window, int $startIndex): void
     */
    public static function processFixedSizeSlidingWindow(array $data, int $size,  callable $callback)
    {
        $count = count($data);
        if ($size <= 0 || $size > $count) {
            return;
        }
        echo "count is : " . $count . PHP_EOL;
        for ($i = 0; $i <= $count - $size; $i++) {
            $window = array_slice($data, $i, $size);
            $callback($window, $i);
        }
    }
}
