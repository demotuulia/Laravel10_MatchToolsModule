<?php

namespace Modules\Matches\Services;

use Modules\Matches\Jobs\AddMatchToProfilesJob;
use Modules\Matches\Models\Matches;
use Illuminate\Support\Collection;
use Modules\Matches\Models\MatchesForm;
use Modules\Matches\Models\MatchesOptions;

class MatchesService
{
    private MatchesOptionService $matchesOptionService;

    public function __construct(MatchesOptionService $matchesOptionService)
    {
        $this->matchesOptionService = $matchesOptionService;
    }

    public function search(int $formId, ?string $search, ?string $order): Collection
    {

        $order = is_null($order) ? 'ordering' : $order;
        if ($search !== null) {
            return Matches::where('label', 'LIKE', '%' . $search . '%')
                ->where('matches_form_id', '=', $formId)
                ->orderBy($order)
                ->get();
        }

        return Matches::orderBy($order)
            ->where('matches_form_id', '=', $formId)
            ->get();
    }

    public function store(array $values)
    {
        $match = new Matches();
        $match->matches_form_id = $values['matches_form_id'];
        $match->match_type = $values['match_type'];
        $match->label = $values['label'];
        $match->ordering = $values['ordering'] ?? (int)Matches::max('ordering') + 1;

        $match->db_code = preg_replace("/[^a-zA-Z]/", "", $values['label'])
            . substr(uniqid(), 0, 6);
        $match->saveOrFail();
        if (isset($values['options'])) {
            foreach ($values['options'] as $option) {
                $option['matches_id'] = $match->id;
                $this->matchesOptionService->store($option);
            }
        }

        dispatch(new AddMatchToProfilesJob($match->id));
        return Matches::with('options')
            ->where('id', $match->id)->get();
    }


    public function addNewMatchToProfiles(Matches $match)
    {
        /** @var MatchesForm $form */
        $form = MatchesForm::find($match->matches_form_id);
        $profileIds = $form->profiles();
        $matchesValuesService = new MatchesValueService();
        foreach ($profileIds as $profileId) {
            $matchesValuesService->store(
                [
                    'matches_profile_id' => $profileId->matches_profile_id,
                    'matches_id' => $match->id,
                ]
            );
        }
    }

    public function update(int $id, array $values): Collection
    {
        /** @var Matches $match */
        $match = Matches::find($id);
        if (is_null($match)) {
            return collect();
        }

        Matches::where('id', $id)
            ->update(
                [
                    'label' => $values['label'],
                    'ordering' => $values['ordering'] ?? (int)Matches::max('ordering') + 1,
                ]);

        if (isset($values['options'])) {
            $currentOptionIds = array_column($values['options'], 'id');
            $this->matchesOptionService->deleteNotIncludedItems($match->id, $currentOptionIds);
            foreach ($values['options'] as $option) {
                $option['matches_id'] = $match->id;
                if (isset($option['id'])) {
                    $this->matchesOptionService->update(
                        $option['id'],
                        $option,
                    );
                } else {
                    $this->matchesOptionService->store($option);
                }
            }
        }
        return Matches::with('options')
            ->where('id', $match->id)->get();
    }

    public function show(int $id): Collection
    {
        $match = Matches::find($id);
        if (is_null($match)) {
            return collect();
        }
        return Matches::with('options')
            ->where('id', $match->id)->get();
    }


    public function deleteNotIncludedItems(int $formId, array $ids): void
    {
        /** @var Collection $matchesToDelete */
        $matchesToDelete = Matches::whereNotIn('id', $ids)
            ->where('matches_form_id', $formId)
            ->get();
        if ($matchesToDelete->isNotEmpty()) {
            foreach ($matchesToDelete->pluck('id') as $idToDelete) {
                $this->delete($idToDelete);
            }
        }
    }

    public function delete(int $id): bool
    {
        /** @var Matches $match */
        $match = Matches::find($id);
        if (is_null($match)) {
            return false;
        }
        // delete options
        /** @var Collection<MatchesOptions> $options */
        $options = $match->options();
        $optionIds = $options->pluck('id');
        foreach ($optionIds as $optionId) {
            $this->matchesOptionService->delete($optionId);
        }

        Matches::destroy($id);
        return true;
    }
}
