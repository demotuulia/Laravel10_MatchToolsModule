<?php

namespace Modules\Matches\Services\MatchTypes;


use Illuminate\Database\Query\Builder;
use Modules\Matches\Models\Matches;
use Modules\Matches\Models\MatchesProfile;
use Modules\Matches\Models\MatchesValues;
use Modules\Matches\Services\MatchTypes\Interfaces;
use Modules\Matches\Services\MatchTypes\Treats\Tmatch;
use Modules\Matches\Services\MatchTypes\Treats\TsearchQueries;
use Modules\Matches\Services\MatchTypes\Treats\TviewQueries;
use Modules\Matches\Services\MatchTypes\Treats\Tstatistics;

class DateFromMatchesTypeService implements Interfaces\IMatchesTypeService
{
    use TsearchQueries;
    use TviewQueries;
    use Tmatch;
    use Tstatistics;

    public function getSearchQuery(
        string         $alias,
        Builder        $builder,
        MatchesValues  $value,
        MatchesProfile $profileToMatch,
        bool           $exact = true,
    ): Builder
    {
        return $profileToMatch->is_professional
            ? $this->smallerThan($alias, $builder, $value, $exact)
            : $this->biggerThan($alias, $builder, $value, $exact);
    }

    public function getViewSearchQuery(
        MatchesValues  $value,
        MatchesProfile $profileToMatch,
        bool           $exact = true,
    ): string
    {
        return $profileToMatch->is_professional
            ? $this->getOperator($exact) . $this->smallerThanView($value)
            : $this->getOperator($exact) . $this->biggerThanView($value);
    }

    /**
     * @inheritdoc
     */
    public function isMatch(
        array          $valuesToMatch,
        array          $valuesToSearch,
        MatchesProfile $profileToMatch,
    ): bool
    {
        if (current($valuesToMatch)->value == null ||
            current($valuesToSearch)->value == null
        ) {
            return false;
        }

        return $profileToMatch->is_professional
            ? $this->smallerThanMatch(
                current($valuesToMatch)->value,
                current($valuesToSearch)->value
            )
            : $this->biggerThanMatch(
                current($valuesToMatch)->value,
                current($valuesToSearch)->value
            );
    }

    public function statistics(Matches $match): array
    {
        return [];
    }
}
