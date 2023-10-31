<?php

namespace Modules\Matches\Services\Search;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Modules\Matches\Enums\EMatchType;
use Modules\Matches\Managers\IMatchesTypeManager;
use Modules\Matches\Models\MatchesProfile;
use Modules\Matches\Models\MatchesValues;
use Modules\Matches\Services\MatchTypes\Interfaces\IMatchesTypeService;

class SearchTableService
{
    public function getFindQuery(MatchesProfile $profileToMatch, bool $exact = true): Builder
    {
        $factory = app(IMatchesTypeManager::class);
        $query = DB::table('matches_profile')
            ->select('matches_profile.id')
            ->where('is_professional', '!=', $profileToMatch->is_professional)
            ->distinct();

        $index = 0;
        /** @var MatchesValues $value */
        foreach ($profileToMatch->values()->get() as $value) {
            if (!is_null($value->value)) {

                $alias = 'MV_' . $index;
                $query->join(
                    'matches_values AS ' . $alias,
                    $alias . '.matches_profile_id',
                    '=',
                    'matches_profile.id')
                    ->where($alias . '.matches_id', '=', $value->matches_id)
                    ->where($alias . '.order', '=', $value->order);
;
                $type = $value->match()->first()->match_type;
                /** @var IMatchesTypeService $service */
                $service = $factory->make(EMatchType::value($type));
                if ($exact) {
                    // find profiles with exact match
                    $query = $service->getSearchQuery(
                        $alias,
                        $query,
                        $value,
                        $profileToMatch
                    );
                }
                $index++;
            }
        }
        // find matches by score, where at one of the matches is a match
        if (!$exact) {
            $query->where(function ($query) use ($profileToMatch, $factory) {
                $index = 0;
                /** @var MatchesValues $value */
                foreach ($profileToMatch->values()->get() as $value) {
                    if (!is_null($value->value)) {
                        $alias = 'MV_' . $index;
                        $type = $value->match()->first()->match_type;
                        /** @var IMatchesTypeService $service */
                        $service = $factory->make(EMatchType::value($type));
                        $query = $service->getSearchQuery(
                            $alias,
                            $query,
                            $value,
                            $profileToMatch,
                            false
                        );
                        $index++;
                    }
                }
            }
            );
        }
        return $query;
    }
}
