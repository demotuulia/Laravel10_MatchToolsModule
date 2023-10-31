<?php

namespace Modules\Matches\Services\MatchTypes\Treats;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;


trait Tstatistics
{
    private function singleValueStatistics(int $matches_id, int $is_professional): array
    {
        $stats = DB::table('matches_values AS MV')
            ->select(DB::raw('value, count(*) as num'))
            ->join(
                'matches_profile AS MP',
                'MP.id', '=', 'MV.matches_profile_id')
            ->where('MV.matches_id', '=', $matches_id)
            ->where('MP.is_professional', '=', $is_professional)
            ->whereNotNull('value')
            ->groupBy('value')
            ->orderBy('value')
            ->get();

        return [
            'labels' => $stats->pluck('value')->toArray(),
            'data' => $stats->pluck('num')->toArray()
        ];
    }


    private function multipleValueOptionStatistics(int $matches_id, int $is_professional): array
    {
        $stats = DB::table('matches_options AS MO')
            ->select(DB::raw('value, count(*) as num'))
            ->join(
                'matches_option_values AS MOV',
                'MO.id', '=', 'MOV.matches_options_id')
            ->join(
                'matches_profile AS MP',
                'MP.id', '=', 'MOV.matches_profile_id')
            ->where('MO.matches_id', '=', $matches_id)
            ->where('MP.is_professional', '=', $is_professional)
            ->groupBy('value')
            ->orderBy('value')
            ->get();

        return [
            'labels' => $stats->pluck('value')->toArray(),
            'data' => $stats->pluck('num')->toArray()
        ];
    }

    private function singleValueOptionStatistics(int $matches_id, int $is_professional): array
    {
        $stats = DB::table('matches_options AS MO')
            ->select(DB::raw('MO.value, count(*) as num'))
            ->join(
                'matches_values AS MV',
                'MO.id', '=', 'MV.value')
            ->join(
                'matches_profile AS MP',
                'MP.id', '=', 'MV.matches_profile_id')
            ->where('MV.matches_id', '=', $matches_id)
            ->where('MP.is_professional', '=', $is_professional)
            ->groupBy('MO.value')
            ->orderBy('MO.value')
            ->get();

        return [
            'labels' => $stats->pluck('value')->toArray(),
            'data' => $stats->pluck('num')->toArray()
        ];
    }


}
