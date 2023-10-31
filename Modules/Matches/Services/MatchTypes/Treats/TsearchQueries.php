<?php

namespace Modules\Matches\Services\MatchTypes\Treats;

use Illuminate\Database\Query\Builder;
use Modules\Matches\Models\MatchesValues;

trait TsearchQueries
{

    private function equals(
        string        $alias,
        Builder       $builder,
        MatchesValues $value,
        bool          $exact = true,
    ): Builder
    {
        return $exact ?
            $builder->where($alias . '.value', '=', $value->value)
            : $builder->orWhere($alias . '.value', '=', $value->value);
    }

    private function smallerThan(
        string        $alias,
        Builder       $builder,
        MatchesValues $value,
        bool          $exact = true,
    ): Builder
    {
        return $exact
            ? $builder->where($alias . '.value', '<', $value->value)
            : $builder->orWhere($alias . '.value', '<', $value->value);
    }

    private function biggerThan(
        string        $alias,
        Builder       $builder,
        MatchesValues $value,
        bool          $exact = true,
    ): Builder
    {
        return $exact
            ? $builder->where($alias . '.value', '>', $value->value)
            : $builder->orWhere($alias . '.value', '>', $value->value);
    }

}
