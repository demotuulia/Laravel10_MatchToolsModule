<?php

namespace Modules\Matches\Services;

use Modules\Matches\Enums\EMatchType;
use Modules\Matches\Managers\IMatchesTypeManager;
use Modules\Matches\Models\Matches;
use Modules\Matches\Models\MatchesForm;
use Modules\Matches\Services\MatchTypes\Interfaces\IMatchesTypeService;


class DashboardService
{
    public function dashboard(
        ?int $formId = null,
    ): array
    {
        $formId =  $formId ?? \Config::get('matches')['formId'];
        /** @var MatchesForm $form */
        $form = MatchesForm::with('matches')->where('id', $formId)->first();

        $factory = app(IMatchesTypeManager::class);

        $statistics = [];
        /** @var Matches $match */
        foreach ($form->matches as $match) {
            /** @var IMatchesTypeService $service */
            $service = $factory->make(EMatchType::value($match->match_type));
            $values =  $service->statistics($match);
            if(!empty($values)) {
                $statistics[] = [
                    'name' => $match->label,
                    'values' => $values,
                ];
            }

        }
        return $statistics;
    }
}
