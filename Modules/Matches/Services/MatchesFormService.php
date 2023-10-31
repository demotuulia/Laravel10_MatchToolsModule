<?php

namespace Modules\Matches\Services;

use Modules\Matches\Models\Matches;
use Modules\Matches\Models\MatchesForm;
use Illuminate\Support\Collection;

class MatchesFormService
{
    private MatchesService $matchesService;

    public function __construct(MatchesService $matchesService)
    {
        $this->matchesService = $matchesService;
    }

    public function search(?string $search, ?string $order): Collection
    {
        $order = is_null($order) ? 'name' : $order;
        if ($search !== null) {
            return MatchesForm::with('matches')
                ->where('name', 'LIKE', '%' . $search . '%')
                ->orderBy($order)
                ->get();
        }
        return MatchesForm::with('matches')
            ->orderBy($order)
            ->get();
    }

    public function store(array $values)
    {
        $form = new MatchesForm();
        $form->name = $values['name'];
        $form->saveOrFail();

        if (isset($values['matches'])) {
            foreach ($values['matches'] as $match) {
                $match['matches_form_id'] = $form->id;
                $this->matchesService->store($match);
            }
        }

        return MatchesForm::with('matches', 'matches.options')
            ->where('id', $form->id)->get();
    }

    public function update(int $id, array $values): array
    {
        $form = MatchesForm::find($id);
        if (is_null($form)) {
            return [];
        }

        MatchesForm::where('id', $id)
            ->update(['name' => $values['name']]);

        if (isset($values['matches'])) {
            $this->matchesService->deleteNotIncludedItems(
                $form->id,
                array_column($values['matches'], 'id')
            );

            foreach ($values['matches'] as $match) {
                $match['matches_form_id'] = $form->id;
                if (isset($match['id'])) {
                    $this->matchesService->update(
                        $match['id'],
                        $match,
                    );
                } else {
                    $this->matchesService->store($match);
                }
            }
        }

        $form->name = $values['name'];
        $form = MatchesForm::with('matches', 'matches.options')
            ->where('id', $form->id)->get();
        return $form->toArray();

    }

    public function show(int $id): array
    {
        /** @var Collection<MatchesForm> $form */
        $form = MatchesForm::with([
            'matches' => function ($query) {
                $query->orderBy('ordering', 'ASC');
            },
            'matches.options'
        ])
            ->where('id', $id)->get();

        if (is_null($form->first())) {
            return [];
        }

        $form->first()->setMatchTypes();
        return $form->toArray();
    }

    public function delete(int $id): bool
    {
        /** @var MatchesForm $form */
        $form = MatchesForm::find($id);
        if (is_null($form)) {
            return false;
        }
        // delete matches
        /** @var Collection $matches */
        $matches = $form->matches();
        $matchIds = $matches->pluck('id');
        foreach ($matchIds as $matchId) {
            $this->matchesService->delete($matchId);
        }

        // delete form
        MatchesForm::destroy($id);

        return true;
    }

    public function getForm($id): MatchesForm
    {
        return MatchesForm::find($id);
    }
}
