<?php

namespace Modules\Matches\Services;

use Illuminate\Support\Facades\Auth;
use Modules\Matches\Enums\EMatchType;
use Modules\Matches\Models\Matches;
use Modules\Matches\Models\MatchesOptions;
use Modules\Matches\Models\MatchesProfile;
use Modules\Matches\Models\MatchesValues;
use Spatie\Permission\Models\Role;

class MatchesProfileService
{
    private MatchesFormService $matchesFormService;
    private MatchesValueService $matchesValueService;
    private MatchesOptionValuesService $matchesOptionValuesService;

    public function __construct(
        MatchesFormService         $matchesFormService,
        MatchesValueService        $matchesValueService,
        MatchesOptionValuesService $matchesOptionValuesService
    )
    {
        $this->matchesFormService = $matchesFormService;
        $this->matchesValueService = $matchesValueService;
        $this->matchesOptionValuesService = $matchesOptionValuesService;
    }

    public function search(
        ?string $search,
        ?int    $userId,
        ?string $order,
        int     $page = 0,
        int     $pageSize = 15,
    ): array
    {
        $query = MatchesProfile::with('values');
        if ($search !== null) {
            foreach (explode(' ', $search) as $searchItem) {
                if (!is_numeric($searchItem) && !in_array($searchItem, ['and', 'or'])) {
                    $query->where('tags', 'LIKE', '%' . $searchItem . '%');
                }
            }
        }
        if ($userId !== null) {
            $query->where('user_id', '=', $userId);
        } else {
            /** @var Role $role */
            $role = Auth::user()->roles()->first();
            $query->where(
                'is_professional',
                '=',
                $role->name == 'company'
                    ? MatchesProfile::$professional
                    : MatchesProfile::$company
            );

        }

        $order = is_null($order) ? 'name' : $order;
        $data = $query->orderBy($order)
            ->get()
            ->forPage($page, $pageSize);
        $count = $query->count();

        return [
            'data' => $data,
            'total_count' => $count,
            'page' => $page,
            'number_of_pages' => ceil($count / $pageSize)
        ];
    }

    public function store(array $values): MatchesProfile
    {
        $profile = new MatchesProfile();
        $profile->name = $values['name'];
        if (isset($values['description'])) {
            $profile->description = $values['description'];
        }
        $profile->user_id = $values['user_id'];
        $profile->is_professional = $values['is_professional'];
        $profile->tags = $this->setTags($values);
        $profile->saveOrFail();

        // insert match values
        $matches = $this->matchesFormService->getForm($values['matches_form_id'])->matches()->get();
        $matchIds = $matches->pluck('id');
        foreach ($matchIds as $matchId) {
            $this->matchesValueService->store(
                [
                    'matches_profile_id' => $profile->id,
                    'matches_id' => $matchId,
                ]
            );
        }

        return $matches->isNotEmpty()
            ? MatchesProfile::where('id', $profile->id)
                ->first()
                ->setMatches()
            : MatchesProfile::where('id', $profile->id)
                ->first();
    }

    public function update(int $id, array $profileArr): ?MatchesProfile
    {

        /** @var MatchesProfile $profile */
        $profile = MatchesProfile::find($id);
        if (is_null($profile)) {
            return null;
        }

        foreach ($profileArr['matches'] as $match) {
            $matchObj = Matches::find($match['id']);
            if (isset($match['values'])) {
                foreach ($match['values'] as $value) {

                    $value['matches_profile_id'] = $profile->id;

                    if (isset($value['value'])) {
                        if (isset($value['value_id']) || isset($value['id'])) {
                            $value['value_id'] = $value['id'] ?? $value['value_id'];
                            $this->matchesValueService->update(
                                $value['value_id'],
                                $value,
                                $matchObj,
                            );
                        } else {
                            $this->matchesValueService->store($value);
                        }
                    }
                }
            }

            if ($matchObj->match_type == EMatchType::MULTIPLE_CHOOSE->name) {
                $matchesId = $match['id'];
                $this->matchesOptionValuesService->refreshProfileValues($profile->id, $matchesId);
                $optionValues = [];
                foreach ($match['options'] as $option) {
                    $matchObj = Matches::find($match['id']);
                    if (isset($option['selected'])) {
                        if ($option['selected']) {
                            $optionValue = [
                                'matches_profile_id' => $profile->id,
                                'matches_id' => $matchesId,
                                'matches_options_id' => $option['id'],
                            ];
                            $this->matchesOptionValuesService->store($optionValue);
                            $optionValues[] = $option['id'];
                        }
                    }

                }
                // We update the options as a value to match_values for indexing views
                sort($optionValues);
                $value['value'] = implode(',', $optionValues);
                $value['value_id'] = MatchesValues::where('matches_profile_id', $profile->id)
                    ->where('matches_id', $matchesId)
                    ->first()->id;
                $this->matchesValueService->update(
                    $value['value_id'],
                    $value,
                    $matchObj,
                );
            }
        }

        $values = ['name' => $profileArr['name']];
        if (isset($profileArr['description'])) {
            $values['description'] = $profileArr['description'];
        }
        $values['tags'] = $this->setTags($profileArr);

        MatchesProfile::where('id', $id)
            ->update($values);

        return MatchesProfile::where('id', $profile->id)
            ->first()
            ->setMatches();
    }

    private function setTags(array $profile): string
    {
        $tags = [];
        foreach (['name', 'description'] as $column) {
            if (isset($profile[$column])) {
                $tags = array_merge(
                    $tags,
                    explode(
                        ' ',
                        strtolower(
                            str_replace([',', '.'], '', $profile[$column])
                        )
                    )
                );
            }
        }

        for ($index = 0; $index < count($tags); $index++) {
            if (is_numeric($tags[$index])) {
                unset($tags[$index]);
            }
        }

        if (isset($profile['matches'])) {
            foreach ($profile['matches'] as $match) {
                if (isset($match['match_type'])) {
                    $matchType = $match['match_type'];
                } else {
                    /** @var Matches $match */
                    $matchType = Matches::where('id', $match['id'])->first()->match_type;
                }

                switch ($matchType) {
                    case EMatchType::MULTIPLE_CHOOSE->name :
                        $selected = [];
                        foreach ($match['options'] as $option) {
                            if (isset($option['selected'])) {
                                if ($option['selected']) {
                                    $selected[] = $option['id'];
                                }
                            }
                        }
                        $optionTags = MatchesOptions::whereIn('id', $selected)->get()->toArray();
                        $tags = array_merge($tags, array_column($optionTags, 'value'));
                        break;
                    case EMatchType::MENU->name :
                        $newTag = MatchesOptions::where('id', $match['values'][0]['value'])->first();
                        if(!is_null($newTag)) {
                            $newTag = $newTag->value;
                            $tags[] = strtolower($newTag);
                        }
                        break;
                    default:
                        break;
                }
            }
        }
        $tags = array_unique($tags);
        sort($tags);
        return implode(',', $tags);
    }

    public function show(int $id): ?MatchesProfile
    {
        /** @var MatchesProfile $profile */
        $profile = MatchesProfile::with('values', 'user')
            ->where('id', $id)->first();

        if (is_null($profile)) {
            return null;
        }

        return $profile->setMatches();
    }

    public
    function delete(int $id): bool
    {
        $profile = MatchesProfile::find($id);
        if (is_null($profile)) {
            return false;
        }

        $values = MatchesValues::where('matches_profile_id', $id)->get();
        $valueIds = $values->pluck('id');
        MatchesValues::destroy(collect($valueIds));
        MatchesProfile::destroy($id);
        return true;
    }
}
