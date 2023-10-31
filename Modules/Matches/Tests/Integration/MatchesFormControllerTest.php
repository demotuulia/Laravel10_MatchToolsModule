<?php

namespace Integration;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\Queue;
use Illuminate\Testing\TestResponse;
use Illuminate\Testing\Fluent\AssertableJson;
use Modules\Matches\Enums\EMatchType;
use Modules\Matches\Jobs\ReindexViewsJob;
use Modules\Matches\Models\MatchesForm;
use Tests\Feature\LoginBase;
use Illuminate\Foundation\Testing\DatabaseTruncation;
use Modules\Matches\Models\MatchType;
use Database\Seeders\TestUsersSeeder;

class MatchesFormControllerTest extends LoginBase
{
    use DatabaseTruncation;


    public function testIndex(): void
    {
        // We create another form, after this we get 2 forms in our database
        $anotherFormName = 'Form 2';
        $form = new MatchesForm();
        $form->name = $anotherFormName;
        $form->save();
        /** @var TestResponse $response */
        $response = $this->getJson('api/matches/forms');
        $response->assertStatus(Response::HTTP_OK);
        $response->assertJson(fn(AssertableJson $json) => $json
            ->where('meta.status', Response::HTTP_OK)
            ->where('meta.count', 2)
            ->where('data.0.name', $anotherFormName)
        );

        // test search ok
        $response = $this->getJson('api/matches/forms?search=' . $anotherFormName);
        $response->assertStatus(Response::HTTP_OK);
        $response->assertJson(fn(AssertableJson $json) => $json
            ->where('meta.status', Response::HTTP_OK)
            ->where('meta.count', 1)
            ->where('data.0.name', $anotherFormName)
        );

        // find by like
        $response = $this->getJson('api/matches/forms?search=orm 2');
        $response->assertStatus(Response::HTTP_OK);
        $response->assertJson(fn(AssertableJson $json) => $json
            ->where('meta.status', Response::HTTP_OK)
            ->where('meta.count', 1)
            ->where('data.0.name', $anotherFormName)
        );

        // test search not found
        $response = $this->getJson('api/matches/forms?search=NOT_FOUND');
        $response->assertStatus(Response::HTTP_OK);
        $response->assertJson(fn(AssertableJson $json) => $json
            ->where('meta.status', Response::HTTP_OK)
            ->where('meta.message', 'TRNS_NO_ITEMS_FOUND')
        );

        // order by
        $response = $this->getJson('api/matches/forms?order_by=name');
        $response->assertJson(fn(AssertableJson $json) => $json
            ->where('meta.status', Response::HTTP_OK)
            ->where('meta.count', 2)
            ->where('data.0.name', $anotherFormName)
            ->where('data.1.name', 'TEST_FORM')
        );

    }

    public function testStore()
    {
        // Insert new form
        $newFormName = 'New Form';
        $response = $this->postJson('api/matches/forms', ['name' => $newFormName]);
        $response->assertJson(fn(AssertableJson $json) => $json
            ->where('meta.status', Response::HTTP_CREATED)
            ->where('data.0.name', $newFormName)
        );


        // Error when trying to save the same name again
        $response = $this->postJson('api/matches/forms', ['name' => $newFormName]);
        $response->assertJson(fn(AssertableJson $json) => $json
            ->where('meta.status', Response::HTTP_BAD_REQUEST)
            ->where('success', false)
            ->where('message', 'Validation errors')
            ->where('errors.name.0', 'The name has already been taken.')
        );
    }

    /**
     * A complete test to create a form, by inserting all items
     */
    public function testCreateAndUpdateFormWitMatches(): void
    {
        Queue::fake();

        //
        // Insert
        //
        $newFormName = 'New Form' . uniqid();
        $response = $this->postJson(
            'api/matches/forms',
            [
                'name' => $newFormName,
                'matches' => [
                    'EQUAL' => [
                        'label' => 'New_Form_EQUAL',
                        'match_type' => EMatchType::EQUAL->name,
                    ],
                    'SMALLER_THAN' =>
                        [
                            'label' => 'New_Form_SMALLER_THAN',
                            'match_type' => EMatchType::SMALLER_THAN->name,
                        ],
                    'RADIO_BUTTON' =>
                        [
                            'label' => 'New_Form_RADIO_BUTTON',
                            'match_type' => EMatchType::RADIO_BUTTON->name,
                        ],
                    'MULTIPLE_CHOOSE' =>
                        [
                            'label' => 'New_Form_MULTIPLE_CHOOSE',
                            'match_type' => EMatchType::MULTIPLE_CHOOSE->name,
                            'options' => [
                                ['value' => 'PURPLE'],
                                ['value' => 'YELLOW'],
                                ['value' => 'PINK',],
                            ]
                        ],
                    'MENU' => [
                        'label' => 'New_Form_MENU',
                        'match_type' => EMatchType::MENU->name,
                        'options' => [
                            ['value' => 'MONDAY'],
                            ['value' => 'TUESDAY'],
                            ['value' => 'WEDNESDAY'],
                            ['value' => 'THURSDAY'],
                        ]
                    ],

                ]
            ]
        );

        $response->assertJsonCount(5, 'data.0.matches');
        $response->assertJsonCount(3, 'data.0.matches.3.options');
        $response->assertJsonCount(4, 'data.0.matches.4.options');
        $response->assertJson(fn(AssertableJson $json) => $json
            ->where('meta.status', Response::HTTP_CREATED)
            ->where('meta.count', 1)
            ->where('data.0.name', $newFormName)
            ->where('data.0.matches.0.label', 'New_Form_EQUAL')
            ->where('data.0.matches.0.match_type', 'EQUAL')
            // MULTIPLE_CHOOSE
            ->where('data.0.matches.3.label', 'New_Form_MULTIPLE_CHOOSE')
            ->where('data.0.matches.3.match_type', 'MULTIPLE_CHOOSE')
            ->where('data.0.matches.3.options.0.value', 'PURPLE')
            // MENU
            ->where('data.0.matches.4.label', 'New_Form_MENU')
            ->where('data.0.matches.4.match_type', EMatchType::MENU->name)
            ->where('data.0.matches.4.options.3.value', 'THURSDAY')
        );
        Queue::assertPushed(ReindexViewsJob::class, 1);


        //
        // update form
        //
        Queue::fake();
        $formId = $response->json()['data'][0]['id'];
        $updatedName = 'Form 2123 updated';
        $updatedMatchLabel = 'Form 2123 updated label';
        $addedMatchName = 'ADDED_EQUAL';
        $responseData = $response->json()['data'][0];

        $response = $this->putJson(
            'api/matches/forms/' . $formId,
            [
                'name' => $updatedName,
                'matches' => [
                    'EQUAL' => [
                        'id' => $responseData['matches'][0]['id'],
                        'label' => $updatedMatchLabel,
                    ],
                    'MULTIPLE_CHOOSE' =>
                        [
                            'id' => $responseData['matches'][3]['id'],
                            'label' => 'New_Form_MULTIPLE_CHOOSE_updated',
                            'options' => [
                                [
                                    'id' => $responseData['matches'][3]['options'][0]['id'],
                                    'value' => 'PURPLE_updated',
                                ],
                                // new option
                                ['value' => 'BEIGE_new'],
                            ]
                        ],
                    'MENU' =>
                        [
                            'id' => $responseData['matches'][4]['id'],
                            'label' => 'New_Form_MENU_updated',
                            'options' => [
                                [
                                    'id' => $responseData['matches'][4]['options'][0]['id'],
                                    'value' => 'MONDAY_updated',
                                ],
                                // new option
                                ['value' => 'FRIDAY_new'],
                            ]
                        ],
                    //new match
                    'EQUAL2' => [
                        'label' => $addedMatchName,
                        'match_type' => EMatchType::EQUAL->name,
                    ],
                ]
                // To be deleted SMALLER_THAN, RADIO_BUTTON beacause they are not in the form
            ]
        );


        $response->assertJsonCount(4, 'data.0.matches');
        $response->assertJsonCount(2, 'data.0.matches.1.options'); //MULTIPLE_CHOOSE
        $response->assertJsonCount(2, 'data.0.matches.2.options'); // MENU
        $response->assertJson(fn(AssertableJson $json) => $json
            ->where('meta.status', Response::HTTP_OK)
            ->where('meta.count', 1)
            ->where('data.0.name', $updatedName)
            ->where('data.0.matches.0.label', $updatedMatchLabel)
            ->where('data.0.matches.0.match_type', 'EQUAL')
            // MULTIPLE_CHOOSE
            ->where('data.0.matches.1.label', 'New_Form_MULTIPLE_CHOOSE_updated')
            ->where('data.0.matches.1.options.0.value', 'PURPLE_updated')
            ->where('data.0.matches.1.options.1.value', 'BEIGE_new')
            // MENU
            ->where('data.0.matches.2.label', 'New_Form_MENU_updated')
            ->where('data.0.matches.2.options.0.value', 'MONDAY_updated')
            ->where('data.0.matches.2.options.1.value', 'FRIDAY_new')
        );
        Queue::assertPushed(ReindexViewsJob::class, 1);

        //
        // Check show
        //
        $response = $this->getJson('api/matches/forms/' . $formId);
        $response->assertJsonCount(4, 'data.0.matches');
        $response->assertJsonCount(2, 'data.0.matches.1.options'); //MULTIPLE_CHOOSE
        $response->assertJsonCount(2, 'data.0.matches.2.options'); // MENU

        $response->assertJson(fn(AssertableJson $json) => $json
            ->where('meta.status', Response::HTTP_OK)
            ->where('meta.count', 1)
            ->where('data.0.name', $updatedName)
            ->where('data.0.matches.0.label', $updatedMatchLabel)
            ->where('data.0.matches.0.match_type', 'EQUAL')
            // MULTIPLE_CHOOSE
            ->where('data.0.matches.1.label', 'New_Form_MULTIPLE_CHOOSE_updated')
            ->where('data.0.matches.1.options.0.value', 'PURPLE_updated')
            ->where('data.0.matches.1.options.1.value', 'BEIGE_new')
            // MENU
            ->where('data.0.matches.2.label', 'New_Form_MENU_updated')
            ->where('data.0.matches.2.options.0.value', 'MONDAY_updated')
            ->where('data.0.matches.2.options.1.value', 'FRIDAY_new')
        );

        $response = $this->deleteJson('api/matches/forms/' . $formId);
        $response->assertJson(fn(AssertableJson $json) => $json
            ->where('meta.status', Response::HTTP_OK)
        );
    }

    public function testShow()
    {
        $newFormName = 'Form 22';
        $form = new MatchesForm();
        $form->name = $newFormName;
        $form->save();

        // show ok
        $response = $this->getJson('api/matches/forms/' . $form->id);
        $response->assertStatus(Response::HTTP_OK);
        $response->assertJson(fn(AssertableJson $json) => $json
            ->where('meta.count', 1)
            ->where('data.0.name', $newFormName)
        );

        $response = $this->getJson('api/matches/forms/923124');
        $response->assertJson(fn(AssertableJson $json) => $json
            ->where('meta.status', Response::HTTP_NOT_FOUND)
            ->where('meta.message', 'TRNS_NO_ITEM_FOUND_FOR_ID 923124')
        );
    }

    public function testUpdate()
    {
        $newFormName = 'Form 2123';
        $form = new MatchesForm();
        $form->name = $newFormName;
        $form->save();

        $updatedName = 'Form 2123 updated';
        // show ok
        $response = $this->putJson('api/matches/forms/' . $form->id, ['name' => $updatedName]);
        $response->assertStatus(Response::HTTP_OK);
        $response->assertJson(fn(AssertableJson $json) => $json
            ->where('meta.status', Response::HTTP_OK)
            ->where('data.0.name', $updatedName)
        );

        $response = $this->putJson('api/matches/forms/923562', ['name' => 'WHATEVER']);
        $response->assertJson(fn(AssertableJson $json) => $json
            ->where('meta.status', Response::HTTP_NOT_FOUND)
            ->where('meta.message', 'TRNS_NO_ITEM_FOUND_FOR_ID 923562')
        );
    }

    public function testDestroy()
    {
        $newFormName = 'Form to be removed';
        $form = new MatchesForm();
        $form->name = $newFormName;
        $form->save();


        // show ok
        $response = $this->deleteJson('api/matches/forms/' . $form->id);
        $response->assertStatus(Response::HTTP_OK);
        $response->assertJson(fn(AssertableJson $json) => $json
            ->where('meta.status', Response::HTTP_OK)
            ->where('meta.message', 'TRNS_ITEM_DELETED_WITH_ID_' . $form->id)
        );

        $response = $this->deleteJson('api/matches/forms/' . $form->id);
        $response->assertJson(fn(AssertableJson $json) => $json
            ->where('meta.status', Response::HTTP_NOT_FOUND)
            ->where('meta.message', 'TRNS_NO_ITEM_FOUND_FOR_ID ' . $form->id)
        );
    }
}
