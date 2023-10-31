<?php

namespace Modules\Matches\Services;

use Illuminate\Support\Collection;
use Modules\Matches\Enums\EMatchType;
use Modules\Matches\Models\Matches;
use Modules\Matches\Models\MatchesValues;

class MatchesValueService
{
    public function store(array $values): Collection
    {
        $value = new MatchesValues();
        $value->value = $values['value'] ?? null;
        $value->matches_id = $values['matches_id'];
        $value->matches_profile_id = $values['matches_profile_id'];
        $value->order = $values['order'] ?? null;
        $value->saveOrFail();
        return MatchesValues::where('id', $value->id)->get();
    }

    public function update(
        int     $id,
        array   $values,
        Matches $match
    ): array
    {
        $returnValue = MatchesValues::find($id);
        if (is_null($returnValue)) {
            return [];
        }

        $values = $this->formatBeforeSave($values, $match);
        MatchesValues::where('id', $id)
            ->update(['value' => $values['value']]);

        $returnValue->value = $values['value'];
        return $returnValue->toArray();
    }


    private function formatBeforeSave(array $values, Matches $match):array
    {

        if ($match->match_type == EMatchType::DATE_FROM->name) {
            // Convert mysql date to unix timestamp
            if (preg_match("/^([0-9]{4})-([0-9]{2})-([0-9]{2})$/", $values['value'])) {
                $carbonDate  =   \Carbon\Carbon::createFromFormat('Y-m-d', $values['value']);
                $values['value'] = $carbonDate->timestamp;
            }
        }
        return $values;
    }
}
