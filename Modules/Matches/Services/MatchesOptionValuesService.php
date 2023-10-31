<?php

namespace Modules\Matches\Services;

use Illuminate\Support\Collection;
use Modules\Matches\Models\MatchesOptions;
use Modules\Matches\Models\MatchesOptionValues;
use Modules\Matches\Models\MatchesValues;

class MatchesOptionValuesService
{
    public function refreshProfileValues(int $profileId, int $matchesId)
    {
        MatchesOptionValues::where('matches_profile_id', $profileId)
            ->where('matches_id', $matchesId)
            ->delete();
    }

    public function store(array $value): Collection
    {
        $valueToInsert = new MatchesOptionValues();
        $valueToInsert->matches_options_id = $value['matches_options_id'];
        $valueToInsert->matches_id = $value['matches_id'];
        $valueToInsert->matches_profile_id = $value['matches_profile_id'];
        $valueToInsert->saveOrFail();
        return MatchesOptionValues::where('id', $valueToInsert->id)->get();
    }

    public function delete(MatchesOptions $matchesOption): void
    {
        MatchesOptionValues::where('matches_options_id', $matchesOption->id)
            ->delete();

        // update values in table matches_values
        /** @var Collection<MatchesValues> $values */
        $values = MatchesValues::where('matches_id', $matchesOption->matches_id)->get();
        foreach ($values as $value) {
            $selectedValues = MatchesOptionValues::where('matches_id', $matchesOption->matches_id)
                ->where('matches_profile_id', $value->matches_profile_id)
                ->get();
            $valuesStr = $selectedValues->implode('matches_options_id', ',');
            $value->value = $valuesStr;
            $value->save();
        }
    }
}
