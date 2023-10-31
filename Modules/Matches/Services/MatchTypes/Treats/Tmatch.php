<?php

namespace Modules\Matches\Services\MatchTypes\Treats;

trait Tmatch
{
    private function equalsMatch(
        string $matchValue,
        string $searchValue,

    ): bool
    {
        return $matchValue === $searchValue;
    }

    private function smallerThanMatch(
        string $matchValue,
        string $searchValue,
    ): bool
    {
        return $searchValue < $matchValue;
    }

    private function biggerThanMatch(
        string $matchValue,
        string $searchValue,
    ): bool
    {
        return $searchValue > $matchValue;
    }
}
