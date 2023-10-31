<?php

namespace Integration;

use Illuminate\Http\Response;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\Feature\LoginBase;
use Illuminate\Foundation\Testing\DatabaseTruncation;
use Modules\Matches\Models\MatchesProfile;
use Modules\Matches\Models\MatchType;
use Database\Seeders\TestUsersSeeder;

class SearchControllerTest extends LoginBase
{
    use DatabaseTruncation;

    /**
     *  This test is to check that the controller works.
     *  The details of the search are in Modules/Matches/Tests/Feature/SearchTest.php
     */
    public function testExactSearch(): void
    {
        $profileId = MatchesProfile::where('name', 'TEST_COMPANY_PROFILE_1')
            ->first()
            ->id;

        $page = 0;
        $pageSize = 3;
        $response = $this->getJson(
            'api/matches/search' .
            '?profile_id=' . $profileId .
            '&page=' . $page .
            '&page_size=' . $pageSize .
            '&exact=true'
        );

        $response->assertJson(fn(AssertableJson $json) => $json
            ->where('meta.status', Response::HTTP_OK)
            ->where('meta.total_count', 1)
            ->where('meta.page', $page)
            ->where('meta.number_of_pages', 1)
            ->where('meta.count', 1)
            ->where('data.0.name', 'TEST_CANDIDATE_PROFILE_2')
        );
    }


    public function testScoreSearch(): void
    {
        $profileId = MatchesProfile::where('name', 'TEST_COMPANY_PROFILE_1')
            ->first()
            ->id;

        $page = 0;
        $pageSize = 3;
        $response = $this->getJson(
            'api/matches/search' .
            '?profile_id=' . $profileId .
            '&page=' . $page .
            '&page_size=' . $pageSize
        );

        $response->assertJson(fn(AssertableJson $json) => $json
            ->where('meta.status', Response::HTTP_OK)
            ->where('meta.total_count', 2)
            ->where('meta.page', $page)
            ->where('meta.number_of_pages', 1)
            ->where('meta.count', 2)
            ->where('data.0.name', 'TEST_CANDIDATE_PROFILE_2')
            ->where('data.0.score', 100) ->where('data.0.name', 'TEST_CANDIDATE_PROFILE_2')
            ->where('data.1.name', 'TEST_CANDIDATE_PROFILE_1')
            ->where('data.1.score', 67)
        );
    }
}
