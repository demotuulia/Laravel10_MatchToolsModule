<?php

namespace Modules\Matches\Services\MatchTypes;

use Illuminate\Database\Query\Builder;
use Modules\Matches\Models\Matches;
use Modules\Matches\Models\MatchesProfile;
use Modules\Matches\Models\MatchesValues;
use Modules\Matches\Services\MatchTypes\Treats\Tmatch;
use Modules\Matches\Services\MatchTypes\Treats\TsearchQueries;
use Modules\Matches\Services\MatchTypes\Treats\TviewQueries;
use Modules\Matches\Services\MatchTypes\Treats\Tstatistics;

class RangeMatchesTypeService implements Interfaces\IMatchesTypeService
{
    public const LOVER_BOUND_VALUE_INDEX = 0;
    public const UPPER_BOUND_VALUE_INDEX = 1;

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
        if ($value->order === self::LOVER_BOUND_VALUE_INDEX) {
            return $this->biggerThan($alias, $builder, $value, $exact);
        }
        return $this->smallerThan($alias, $builder, $value, $exact);
    }

    public function getViewSearchQuery(
        MatchesValues  $value,
        MatchesProfile $profileToMatch,
        bool           $exact = true,
    ): string
    {
        if ($value->order === self::LOVER_BOUND_VALUE_INDEX) {
            return $this->getOperator($exact) . $this->biggerThanView($value);
        }
        return 'AND ' . $this->smallerThanView($value);
    }

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

        /** @var MatchesValues $valuesToMatchLowerBound */
        $valuesToMatchLowerBound = current($valuesToMatch)->order == self::LOVER_BOUND_VALUE_INDEX
            ? current($valuesToMatch)
            : end($valuesToMatch);
        /** @var MatchesValues $valuesToMatchUpperBound */
        $valuesToMatchUpperBound = current($valuesToMatch)->order == self::UPPER_BOUND_VALUE_INDEX
            ? current($valuesToMatch)
            : end($valuesToMatch);

        /** @var MatchesValues $valuesToSearchLowerBound */
        $valuesToSearchLowerBound = current($valuesToSearch)->order == self::LOVER_BOUND_VALUE_INDEX
            ? current($valuesToSearch)
            : end($valuesToSearch);
        /** @var MatchesValues $valuesToSearchUpperBound */
        $valuesToSearchUpperBound = current($valuesToSearch)->order == self::UPPER_BOUND_VALUE_INDEX
            ? current($valuesToSearch)
            : end($valuesToSearch);

        return $this->biggerThanMatch($valuesToMatchLowerBound->value, $valuesToSearchLowerBound->value) &&
            $this->biggerThanMatch($valuesToSearchUpperBound->value, $valuesToMatchUpperBound->value);
    }

    public function statistics(Matches $match): array
    {
        return [];
    }
}
