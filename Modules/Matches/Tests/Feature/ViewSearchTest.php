<?php

namespace Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Matches\Models\MatchesProfile;
use Modules\Matches\Services\Search\SearchViewService;
use Modules\Matches\Services\SearchService;
use Modules\Matches\Tests\Feature\Traits\SearchUseCasesTrait;
use Tests\Feature\LoginBase;

class ViewSearchTest extends LoginBase
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
        list('form' => $form, 'profile' => $profile) = $this->prepareQuerySearchUseCase($matches, $values);
        (new SearchViewService())->createFormMatchesView($form->id);

        list('data' => $matches) = (new SearchService())->findMatchesByView(
            $profile,
            true,
        );

        $matchingProfileNames = $matches->map(function (\stdClass $profile) {
            return MatchesProfile::find($profile->id)->name;
        });
        $this->assertEquals($expected, $matchingProfileNames->toArray(), $caseName);
        (new SearchViewService())->dropView($form->id);
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
        list('form' => $form, 'profile' => $profile) = $this->prepareQuerySearchUseCase($matches, $values);
        (new SearchViewService())->createFormMatchesView($form->id);
        list('data' => $matches) = (new SearchService())->findMatchesByView($profile, false);
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
        (new SearchViewService())->dropView($form->id);
    }

    public function testPagination(): void
    {
        $this->preparePaginationTest();
        list('form' => $form, 'profile' => $profile) = $this->preparePaginationTest();
        (new SearchViewService())->createFormMatchesView($form->id);
        foreach ([true, false] as $exact) {
            foreach ($this->paginationUseCases() as $caseName => $case) {
                $matches = (new SearchService())->findMatchesByView(
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
