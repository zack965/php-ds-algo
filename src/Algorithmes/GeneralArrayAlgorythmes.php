<?php


namespace Zack\PhpDsAlgo\Algorithmes;

use Zack\PhpDsAlgo\Exception\DuplicateNodeException;

class GeneralArrayAlgorythmes
{
    /**
     * CheckDuplicateInArray
     *
     * @param  array<int|string> $data
     * @throws DuplicateNodeException
     */
    public static function checkDuplicateInArray(array $data)
    {
        $seen = [];

        foreach ($data as $entry) {

            if (isset($seen[$entry])) {
                throw DuplicateNodeException::nodeDuplicate($entry);
            }

            $seen[$entry] = true;
        }
    }
}
