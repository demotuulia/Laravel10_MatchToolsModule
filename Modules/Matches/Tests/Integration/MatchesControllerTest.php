<?php

namespace Integration;

use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Queue;
use Illuminate\Testing\TestResponse;
use Illuminate\Testing\Fluent\AssertableJson;
use Modules\Matches\Enums\EMatchType;
use Modules\Matches\Jobs\AddMatchToProfilesJob;
use Modules\Matches\Jobs\ReindexViewsJob;
use Modules\Matches\Models\Matches;
use Modules\Matches\Models\MatchesForm;
use Modules\Matches\Models\MatchesOptions;
use Modules\Matches\Models\MatchesOptionValues;
use Modules\Matches\Models\MatchesValues;
use Tests\Feature\LoginBase;
use Illuminate\Foundation\Testing\DatabaseTruncation;
use Modules\Matches\Models\MatchType;
use Database\Seeders\TestUsersSeeder;

class MatchesControllerTest extends LoginBase
{
    use DatabaseTruncation;


    public function testIndex(): void
    {
        // The matches for this test are created in TestDataSeeder
        $formId = 1;

        /** @var TestResponse $response */
        $response = $this->getJson('api/matches/matches/?form_id=' . $formId);

        $response->assertStatus(Response::HTTP_OK);
        $response->assertJson(fn(AssertableJson $json) => $json
            ->where('meta.status', Response::HTTP_OK)
            ->where('meta.count', 3)
            ->where('data.0.label', 'TEST_FORM_1_EQUAL')
            ->where('data.0.ordering', 1)
        );

        // test search ok
        $response = $this->getJson('api/matches/matches/?form_id=' . $formId . '&search=Equ');
        $response->assertStatus(Response::HTTP_OK);
        $response->assertJson(fn(AssertableJson $json) => $json
            ->where('meta.status', Response::HTTP_OK)
            ->where('meta.count', 1)
            ->where('data.0.label', 'TEST_FORM_1_EQUAL')
            ->where('data.0.ordering', 1)
        );

        // test search not found
        $response = $this->getJson('api/matches/matches/?form_id=' . $formId . '&search=NOT_FOUND');
        $response->assertStatus(Response::HTTP_OK);
        $response->assertJson(fn(AssertableJson $json) => $json
            ->where('meta.status', Response::HTTP_OK)
            ->where('meta.message', 'TRNS_NO_ITEMS_FOUND')
        );

        // order by
        $response = $this->getJson('api/matches/matches/?form_id=' . $formId . '&order_by=label');
        $response->assertJson(fn(AssertableJson $json) => $json
            ->where('meta.status', Response::HTTP_OK)
            ->where('meta.count', 3)
            ->where('data.0.label', 'TEST_FORM_1_EQUAL')
            ->where('data.1.label', 'TEST_FORM_1_MULTIPLE_CHOOSE')
        );
    }

    public function testStore()
    {
        // This test is based on the data inserted in
        // Modules/Matches/Database/Seeders/Tests/TestDataSeeder.php
        Queue::fake();
        $form = MatchesForm::where('name', 'TEST_FORM')->first();
        $formId = $form->id;

        $params = [
            'name' => 'NEW_MATCH',
            'label' => 'new match',
            'matches_form_id' => $formId,
            'match_type' => EMatchType::EQUAL->name
        ];

        $response = $this->postJson('api/matches/matches/', $params);
        $response->assertJson(fn(AssertableJson $json) => $json
            ->where('meta.status', Response::HTTP_CREATED)
            ->where('data.0.label', $params['label'])
            ->where('data.0.ordering', 4)
        );
        $response->assertJsonStructure(['data' => ['*' => ['db_code',]]]);
        Queue::assertPushed(ReindexViewsJob::class, 1);
        Queue::assertPushed(AddMatchToProfilesJob::class, 1);

        // Test AddMatchToProfilesJob, all profiles should have a new match
        $newMatchId = $response->json()['data'][0]['id'];
        $addMatchToProfilesJob = new AddMatchToProfilesJob($newMatchId);
        $addMatchToProfilesJob->handle();
        $updatedProfiles = MatchesValues::where('matches_id', $newMatchId)->get();
        // All profiles inserted in TestDataSeeder  should have a new match
        $this->assertEquals(4, $updatedProfiles->count());


        // Validation error
        $params['match_type'] = 'DUMMY';
        $response = $this->postJson('api/matches/matches/', $params);
        $response->assertJson(fn(AssertableJson $json) => $json
            ->where('meta.status', Response::HTTP_BAD_REQUEST)
            ->where('success', false)
            ->where('message', 'Validation errors')
            ->where('errors.match_type.0', 'The selected match type is invalid.')
        );
    }

    public function testMultipleChoose()
    {
        Queue::fake();
        $form = MatchesForm::where('name', 'TEST_FORM')->first();
        $formId = $form->id;

        //
        // insert
        //
        $params = [
            'label' => 'Multiple choose',
            'matches_form_id' => $formId,
            'match_type' => EMatchType::MULTIPLE_CHOOSE->name,
            'options' =>
                [
                    'RED' => ['value' => 'RED'],
                    'BLUE' => ['value' => 'BLUE'],
                    'GREEN' => ['value' => 'GREEN'],
                ]
        ];
        $response = $this->postJson('api/matches/matches/', $params);

        $response->assertJsonCount(count($params['options']), 'data.0.options');
        $response->assertJson(fn(AssertableJson $json) => $json
            ->where('meta.status', Response::HTTP_CREATED)
            ->where('data.0.label', $params['label'])
            ->where('data.0.ordering', 4)
            ->where('data.0.options.0.value', $params['options']['RED']['value'])
            ->where('data.0.options.2.value', $params['options']['GREEN']['value'])
        );
        Queue::assertPushed(ReindexViewsJob::class, 1);

        $data = $response->json()['data'][0];
        $matchId = $data['id'];

        //
        // update
        //
        $params = [
            'label' => 'Multiple choose',
            'options' =>
                [
                    'RED' => [
                        'id' => $data['options'][0]['id'],
                        'value' => 'RED is cool',
                    ],
                    'BLUE' => [
                        'id' => $data['options'][1]['id'],
                        'value' => 'BLUE',
                    ],
                    'BLACK' => [
                        'value' => 'BLACK',
                    ],
                ]  // Option GREEN is to be deleted, because it is not on the list
        ];

        $response = $this->putJson('api/matches/matches/' . $matchId, $params);
        $response->assertJsonCount(3, 'data.0.options');
        $response->assertJson(fn(AssertableJson $json) => $json
            ->where('meta.status', Response::HTTP_OK)
            ->where('data.0.label', $params['label'])
            ->where('data.0.options.0.value', $params['options']['RED']['value'])
            ->where('data.0.options.2.value', $params['options']['BLACK']['value'])
        );

        //
        // show
        //
        $response = $this->getJson('api/matches/matches/' . $matchId);
        $response->assertJsonCount(3, 'data.0.options');
        $response->assertJson(fn(AssertableJson $json) => $json
            ->where('meta.status', Response::HTTP_OK)
            ->where('data.0.label', $params['label'])
            ->where('data.0.options.0.value', $params['options']['RED']['value'])
            ->where('data.0.options.2.value', $params['options']['BLACK']['value'])
        );

        //
        // delete
        //
        $response = $this->deleteJson('api/matches/matches/' . $matchId);
        $response->assertStatus(Response::HTTP_OK);
        $response->assertJson(fn(AssertableJson $json) => $json
            ->where('meta.status', Response::HTTP_OK)
            ->where('meta.message', 'TRNS_ITEM_DELETED_WITH_ID_' . $matchId)
        );
    }

    public function testShow()
    {
        // The matches for this test are created in TestDataSeeder
        $match = Matches::where('label', 'TEST_FORM_1_EQUAL')->first();
        $matchId = $match->id;

        // show ok
        $response = $this->getJson('api/matches/matches/' . $matchId);
        $response->assertStatus(Response::HTTP_OK);
        $response->assertJson(fn(AssertableJson $json) => $json
            ->where('meta.count', 1)
            ->where('data.0.label', 'TEST_FORM_1_EQUAL')

        );

        $response = $this->getJson('api/matches/matches/923124');
        $response->assertJson(fn(AssertableJson $json) => $json
            ->where('meta.status', Response::HTTP_NOT_FOUND)
            ->where('meta.message', 'TRNS_NO_ITEM_FOUND_FOR_ID 923124')
        );
    }

    public function testUpdate()
    {
        Queue::fake();
        // The matches for this test are created in TestDataSeeder
        $match = Matches::where('label', 'TEST_FORM_1_EQUAL')->first();
        $matchId = $match->id;
        $ordering = 10;
        $params = [
            'label' => 'updated match',
            'ordering' => $ordering,
        ];

        // show ok
        $response = $this->putJson('api/matches/matches/' . $matchId, $params);
        $response->assertStatus(Response::HTTP_OK);
        $response->assertJson(fn(AssertableJson $json) => $json
            ->where('meta.status', Response::HTTP_OK)
            ->where('data.0.label', 'updated match')
            ->where('data.0.ordering', $ordering)
        );
        Queue::assertPushed(ReindexViewsJob::class, 1);

        // Test not found
        $params = [
            'name' => 'UPDATED_MATCH2',
            'label' => 'updated match 2'
        ];
        $response = $this->putJson('api/matches/matches/923562', $params);
        $response->assertJson(fn(AssertableJson $json) => $json
            ->where('meta.status', Response::HTTP_NOT_FOUND)
            ->where('meta.message', 'TRNS_NO_ITEM_FOUND_FOR_ID 923562')
        );
    }

    public function testDestroy()
    {
        // The matches for this test are created in TestDataSeeder

        $useCases = ['TEST_FORM_1_EQUAL', 'TEST_FORM_1_MULTIPLE_CHOOSE'];

        foreach ($useCases as $caseName) {
            Queue::fake();

            $match = Matches::where('label', $caseName)->first();
            $matchId = $match->id;

            // show ok
            $response = $this->deleteJson('api/matches/matches/' . $matchId);
            $response->assertStatus(Response::HTTP_OK);
            $response->assertJson(fn(AssertableJson $json) => $json
                ->where('meta.status', Response::HTTP_OK)
                ->where('meta.message', 'TRNS_ITEM_DELETED_WITH_ID_' . $matchId)
            );
            Queue::assertPushed(ReindexViewsJob::class, 1);
            // Check all values for the match are deleted
            /** @var Collection $values */
            $values = MatchesValues::where('matches_id', $matchId)->get();
            $this->assertEquals(0, $values->count());

            // Check all option values for the match are deleted
            /** @var Collection $values */
            $values = MatchesOptionValues::where('matches_id', $matchId)->get();
            $this->assertEquals(0, $values->count());

            // Check all options
            /** @var Collection $values */
            $options = MatchesOptions::where('matches_id', $matchId)->get();
            $this->assertEquals(0, $options->count());
        }


        //
        // Test delete non-existing  match  with values in table matches_value
        //
        $response = $this->deleteJson('api/matches/matches/' . $matchId);
        $response->assertJson(fn(AssertableJson $json) => $json
            ->where('meta.status', Response::HTTP_NOT_FOUND)
            ->where('meta.message', 'TRNS_NO_ITEM_FOUND_FOR_ID ' . $matchId)
        );
    }
}
