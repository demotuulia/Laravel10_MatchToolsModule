<?php

namespace Modules\Matches\Services\MatchTypes\Treats;

use Modules\Matches\Models\Matches;
use Modules\Matches\Models\MatchesValues;

trait TviewQueries
{

    private function equalsView(
        MatchesValues $value,
    ): string
    {
        return self::getColumnName($value) . ' = "' . $value->value . '"';
    }

    private function smallerThanView(
        MatchesValues $value,
    ): string
    {
        return self::getColumnName($value) . ' < "' . $value->value . '"';
    }

    private function biggerThanView(
        MatchesValues $value,
    ): string
    {
        return self::getColumnName($value) . ' > "' . $value->value . '"';
    }

    private static function getColumnName(MatchesValues $value)
    {
        /** @var Matches $match */
        $match = $value->match()->first();
        $name = $match->db_code;
        $name .= (is_numeric($value->order)) ? '_' . $value->order : '';
        return $name;
    }

    private function getOperator(bool $exact): string
    {
        return $exact ? ' AND ' : ' OR ';
    }
}
