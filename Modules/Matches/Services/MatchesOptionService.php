<?php

namespace Modules\Matches\Services;

use Modules\Matches\Models\MatchesOptions;

class MatchesOptionService
{
    private MatchesOptionValuesService $matchesOptionValuesService;

    public function __construct(MatchesOptionValuesService $matchesOptionValuesService)
    {
        $this->matchesOptionValuesService = $matchesOptionValuesService;
    }

    public function store(array $values)
    {
        $option = new MatchesOptions();
        $option->matches_id = $values['matches_id'];
        $option->value = $values['value'];
        $option->order = $values['order'] ?? null;
        $option->saveOrFail();

        return $option->toArray();
    }

    public function update(int $id, array $values): array
    {
        /** @var MatchesOptions $option */
        $option = MatchesOptions::find($id);
        if (is_null($option)) {
            return [];
        }

        MatchesOptions::where('id', $id)
            ->update(
                [
                    'value' => $values['value'],
                    'order' => isset($values['order']) ?? null,
                ]
            );
        $option->value = $values['value'];
        $option->order = $values['order'] ?? null;

        return $option->toArray();
    }

    public function delete(int $id): bool
    {
        $matchesOption = MatchesOptions::find($id);
        $this->matchesOptionValuesService->delete($matchesOption);
        MatchesOptions::destroy($id);
        return true;
    }

    public function deleteNotIncludedItems(int $matchId, array $ids): void
    {
        $matchesToDelete = MatchesOptions::whereNotIn('id', $ids)
            ->where('matches_id', $matchId)
            ->get();
        if ($matchesToDelete->isNotEmpty()) {
            foreach ($matchesToDelete->pluck('id') as $idToDelete) {
                $this->delete($idToDelete);
            }
        }
    }
}
