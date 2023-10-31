<?php

namespace Modules\Matches\Services;

use Illuminate\Support\Collection;
use Modules\Matches\Enums\EMatchType;
use Modules\Matches\Managers\IMatchesTypeManager;
use Modules\Matches\Models\MatchesForm;
use Modules\Matches\Models\MatchesProfile;
use Modules\Matches\Services\MatchTypes\Interfaces\IMatchesTypeService;
use Modules\Matches\Services\Search\SearchTableService;
use Modules\Matches\Services\Search\SearchViewService;

class SearchService
{

    public function search(
        int  $matchesProfileId,
        bool $exact = true,
        int  $page = 0,
        int  $pageSize = 15,
    ): array
    {
        /** @var MatchesProfile $profile */
        $profile = MatchesProfile::findOrFail($matchesProfileId);
        $result = $this->findMatchesByTable($profile, $exact, $page, $pageSize);
        $matches = $result['data'];

        if ($matches->count() > 0) {
            $profiles = $matches->map(function (\stdClass $profileData) {
                /** @var MatchesProfile $profile */
                $profile = MatchesProfile::with('values')
                    ->where('id', $profileData->id)
                    ->get();
                $profile->first()->setScore($profileData->score);
                return $profile->toJson();
            });
        } else {
            $profiles = collect(new MatchesProfile());
        }

        $result['data'] = $profiles;
        return $result;
    }

    public function findMatchesByTable(
        MatchesProfile $profileToMatch,
        bool           $exact = true,
        int            $page = 0,
        int            $pageSize = 15,
    ): array
    {
        $query = (new SearchTableService())->getFindQuery($profileToMatch, $exact);
        if ($exact) {
            $data = $query->get()->forPage($page, $pageSize);
            $totalCollection = $query->get();
            $totalCount = $totalCollection->count();

            $data = $data->map(function ($profile) {
                $profile->score = 100;
                return $profile;
            });

            return $this->getResultData($data, $totalCount, $page, $pageSize);
        }
        // get scores
        $profileIds = $query->get();
        return $this->defineScores($profileToMatch, $profileIds, $page, $pageSize);
    }


    public function findMatchesByView(
        MatchesProfile $profileToMatch,
        bool           $exact = true,
        int            $page = 0,
        int            $pageSize = 15,
    ): array
    {
        $query = (new SearchViewService())->getFindQuery($profileToMatch, $exact);
        if ($exact) {
            $allProfileIds = collect(\DB::select($query));

            $pageToSlice = $page > 0 ? $page - 1 : 0;
            $profileIds = (count($allProfileIds) > $pageSize)
                ? $allProfileIds->slice($pageToSlice * $pageSize, $pageSize)
                : $allProfileIds;

            return $this->getResultData(collect($profileIds), count($allProfileIds), $page, $pageSize);
        }
        // get scores
        $profileIds = collect(\DB::select($query));
        return $this->defineScores($profileToMatch, $profileIds, $page, $pageSize);
    }

    /**
     * @param MatchesProfile $profileToMatch
     * @param Collection<int> $profileIdToSearch
     */
    private function defineScores(
        MatchesProfile $profileToMatch,
        Collection     $profileIdToSearch,
        int            $page = 0,
        int            $pageSize = 15,
    ): array
    {
        $factory = app(IMatchesTypeManager::class);

        if ($profileIdToSearch->isNotEmpty()) {

            /** @var MatchesProfile $profileToMatchValues */
            $profileToMatchValues = MatchesProfile::with('values', 'values.match')
                ->where('id', $profileToMatch->id)
                ->first();

            $form = MatchesForm::with('matches')
                ->where('id', $profileToMatchValues->formId())
                ->first();
            $numberOfMatches = $form->matches->count();

            // collect values to array by match_id
            $profileValuesToMatch = [];
            foreach ($profileToMatchValues->values as $matchValue) {
                if (!isset($profileValuesToMatch[$matchValue->matches_id])) {
                    $profileValuesToMatch[$matchValue->matches_id] = [];
                }
                $profileValuesToMatch[$matchValue->matches_id][] = $matchValue;
            }

            /** @var array<MatchesProfile> $profilesToSearch */
            $profilesToSearch = MatchesProfile::with('values', 'values.match')
                ->whereIn('id', $profileIdToSearch->pluck(['id']))
                ->get();

            // Define scores
            $scores = [];
            foreach ($profilesToSearch as $profileToSearch) {
                $score = 0;
                $profileValuesToSearch = [];
                foreach ($profileToSearch->values as $matchValue) {
                    if (!isset($profileValuesToSearch[$matchValue->matches_id])) {
                        $profileValuesToSearch[$matchValue->matches_id] = [];
                    }
                    $profileValuesToSearch[$matchValue->matches_id][] = $matchValue;
                }
                // define score per value
                foreach ($profileValuesToSearch as $matchId => $profileValueToSearch) {
                    $matchType = current($profileValueToSearch)->match->match_type;
                    /** @var IMatchesTypeService $service */
                    $service = $factory->make(EMatchType::value($matchType));
                    if ($service->isMatch(
                        $profileValuesToMatch[$matchId],
                        $profileValueToSearch,
                        $profileToMatch,
                    )
                    ) {
                        $score++;
                    };
                }
                if ($score > 0) {
                    $score = (int)round(100 * $score / $numberOfMatches);
                    $scores[$profileToSearch->id] = $score;
                }

            }
        }
        // sort scores descending
        uasort($scores, function (int $a, int $b) { /** @phpstan-ignore-line */
            return $b - $a;
        });

        $totalCount = count($scores); /** @phpstan-ignore-line */

        $pageToSlice = $page > 0 ? $page - 1 : 0;

        $scores = array_slice(
            $scores, /** @phpstan-ignore-line */
            ($pageToSlice * $pageSize),
            $pageSize,
            true);
        $data = array_keys($scores);

        // format for controller
        $data = array_map(
            function ($item) use ($scores) {
                return (object)[
                    'id' => $item,
                    'score' => $scores[$item],
                ];
            },
            $data
        );
        $data = collect($data);
        return $this->getResultData($data, $totalCount, $page, $pageSize);
    }

    private function getResultData(
        Collection $data,
        int        $totalCount,
        int        $page,
        int        $pageSize,
    ): array
    {
        return [
            'data' => $data,
            'count' => count($data),
            'total_count' => $totalCount,
            'page' => $page,
            'page_size' => $pageSize,
            'number_of_pages' => ceil($totalCount / $pageSize),
        ];
    }
}
