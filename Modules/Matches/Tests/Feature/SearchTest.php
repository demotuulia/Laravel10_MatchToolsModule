<?php

namespace Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Matches\Models\MatchesProfile;
use Modules\Matches\Models\MatchType;
use Modules\Matches\Services\SearchService;
use Modules\Matches\Tests\Feature\Traits\SearchUseCasesTrait;
use Tests\Feature\LoginBase;

class SearchTest extends LoginBase
{
    use RefreshDatabase;
    use SearchUseCasesTrait;

    public function testSearch(): void
    {
        foreach ($this->getQuerySearchUseCases() as $caseName => $case) {
            $this->_testSearch(
                $caseName,
                $case['matches'],
                $case['values'],
                $case['expected'],
            );
        }
    }

    public function _testSearch(
        string $caseName,
        array  $matches,
        array  $values,
        array  $expected,
    ): void
    {
        $searchService = new SearchService();
        list('profile' => $profile) = $this->prepareQuerySearchUseCase($matches, $values);
        list('data' => $matches) = $searchService->findMatchesByTable($profile);
        $matchingProfileNames = $matches->map(function (\stdClass $profile) {
            return MatchesProfile::find($profile->id)->name;
        });

        $this->assertEquals($expected, $matchingProfileNames->toArray(), 'CASE: ' . $caseName);
    }


    public function testByScoreSearch(): void
    {
        foreach ($this->getQuerySearchUseCases() as $caseName => $case) {
            $this->_testSearchByScore(
                $caseName,
                $case['matches'],
                $case['values'],
                $case['expectedScores'],
            );
        }
    }

    public function _testSearchByScore(
        string $caseName,
        array  $matches,
        array  $values,
        array  $expectedScores,
    ): void
    {
        $searchService = new SearchService();
        list('profile' => $profile) = $this->prepareQuerySearchUseCase($matches, $values);
        list('data' => $matches) = $searchService->findMatchesByTable($profile, false);

        $scoresByName = [];
        foreach ($matches as $score) {
            $name = MatchesProfile::find($score->id)->name;
            $scoresByName[$name] = $score->score;
        }

        $this->assertEquals(
            $expectedScores,
            $scoresByName,
            'Case:' . $caseName
        );
    }


    public function testPagination(): void
    {
        $searchService = new SearchService();
        foreach ([true, false] as $exact) {
            list('form' => $form, 'profile' => $profile) = $this->preparePaginationTest();
            foreach ($this->paginationUseCases() as $caseName => $case) {
                $matches = $searchService->findMatchesByTable(
                    $profile,
                    $exact,
                    $case['page'],
                    $case['pageSize']
                );
                $caseName .= $exact ? ' exact' : ' score';
                $this->assertPagination($matches, $case['expected'], $caseName);
            }
        }
    }
}
