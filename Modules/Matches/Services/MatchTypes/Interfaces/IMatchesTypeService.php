<?php

namespace Modules\Matches\Services\MatchTypes\Interfaces;

use Illuminate\Database\Query\Builder;
use Modules\Matches\Models\Matches;
use Modules\Matches\Models\MatchesProfile;
use Modules\Matches\Models\MatchesValues;

interface IMatchesTypeService
{
    public function getSearchQuery(
        string         $alias,
        Builder        $builder,
        MatchesValues  $value,
        MatchesProfile $profileToMatch,
        bool           $exact = true,
    ): Builder;

    public function getViewSearchQuery(
        MatchesValues  $value,
        MatchesProfile $profileToMatch,
        bool           $exact,
    ): string;

    /**
     * @param array<MatchesValues> $valuesToMatch
     * @param array<MatchesValues> $valuesToSearch
     */
    public function isMatch(
        array          $valuesToMatch,
        array          $valuesToSearch,
        MatchesProfile $profileToMatch,
    ): bool;


    public function statistics(Matches $match): array;
}
