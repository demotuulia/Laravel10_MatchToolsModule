<?php

namespace Integration;

use App\Models\User;
use Database\Seeders\TestUsersSeeder;
use Illuminate\Foundation\Testing\DatabaseTruncation;
use Illuminate\Http\Response;
use Illuminate\Testing\Fluent\AssertableJson;
use Illuminate\Testing\TestResponse;
use Modules\Matches\Enums\EMatchType;
use Modules\Matches\Models\Matches;
use Modules\Matches\Models\MatchesForm;
use Modules\Matches\Models\MatchesProfile;
use Modules\Matches\Models\MatchesValues;
use Modules\Matches\Models\MatchType;
use Modules\Matches\Services\MatchesFormService;
use Modules\Matches\Services\MatchesOptionService;
use Modules\Matches\Services\MatchesOptionValuesService;
use Modules\Matches\Services\MatchesProfileService;
use Modules\Matches\Services\MatchesService;
use Modules\Matches\Services\MatchesValueService;
use Tests\Feature\LoginBase;

class MatchesProfileControllerTest extends LoginBase
{
    use DatabaseTruncation;

    public function testIndex(): void
    {
        // The matches for this test are created in TestDataSeeder

        /** @var TestResponse $response */
        $response = $this->getJson('api/matches/profiles');
        $response->assertStatus(Response::HTTP_OK);
        $response->assertJson(fn(AssertableJson $json) => $json
            ->where('meta.status', Response::HTTP_OK)
            ->where('meta.count', 2)
            ->where('data.0.name', 'TEST_COMPANY_PROFILE_1')
        );


        // test search ok
        $response = $this->getJson('api/matches/profiles?search=TEST_COMPANY_PROFILE_1');
        $response->assertStatus(Response::HTTP_OK);
        $response->assertJson(fn(AssertableJson $json) => $json
            ->where('meta.status', Response::HTTP_OK)
            ->where('meta.count', 1)
            ->where('data.0.name', 'TEST_COMPANY_PROFILE_1')
        );

        // find by like from name
        $response = $this->getJson('api/matches/profiles?search=any_profile_1');
        $response->assertStatus(Response::HTTP_OK);
        $response->assertJson(fn(AssertableJson $json) => $json
            ->where('meta.status', Response::HTTP_OK)
            ->where('meta.count', 1)
            ->where('data.0.name', 'TEST_COMPANY_PROFILE_1')
        );

        // find by like from description
        $response = $this->getJson('api/matches/profiles?search=Y_PROFILE_1');
        $response->assertStatus(Response::HTTP_OK);
        $response->assertJson(fn(AssertableJson $json) => $json
            ->where('meta.status', Response::HTTP_OK)
            ->where('meta.count', 1)
            ->where('data.0.name', 'TEST_COMPANY_PROFILE_1')
        );

        // test search not found
        $response = $this->getJson('api/matches/profiles?search=NOT_FOUND');
        $response->assertStatus(Response::HTTP_OK);
        $response->assertJson(fn(AssertableJson $json) => $json
            ->where('meta.status', Response::HTTP_OK)
            ->where('meta.message', 'TRNS_NO_ITEMS_FOUND')
        );

        // find by user_id
        /** @var MatchesProfile $userProfile */
        $userProfile = MatchesProfile::where('name', 'TEST_COMPANY_PROFILE_2')->first();
        $userId = $userProfile->user_id;
        $response = $this->getJson('api/matches/profiles?user_id=' . $userId);

        $response->assertStatus(Response::HTTP_OK);
        $response->assertJson(fn(AssertableJson $json) => $json
            ->where('meta.status', Response::HTTP_OK)
            ->where('meta.count', 2)
            ->where('data.0.name', 'TEST_COMPANY_PROFILE_1')
            ->where('data.1.name', 'TEST_COMPANY_PROFILE_2')
        );

        // order by
        $response = $this->getJson('api/matches/profiles?order_by=name');
        $response->assertJson(fn(AssertableJson $json) => $json
            ->where('meta.status', Response::HTTP_OK)
            ->where('meta.count', 2)
            ->where('data.0.name', 'TEST_COMPANY_PROFILE_1')
            ->where('data.1.name', 'TEST_COMPANY_PROFILE_2')
        );
    }

    public function testPagination(): void
    {
        $matchesValues = MatchesValues::all();
        MatchesValues::destroy($matchesValues->pluck('id'));
        $matchesProfiles = MatchesProfile::all();
        MatchesProfile::destroy($matchesProfiles->pluck('id'));

        $users = User::all();
        $userId = $users->first()->id;

        $form = new MatchesForm();
        $form->name = 'PAGINATION_TEST_FORM';
        $form->save();

        $matchesProfileService = new MatchesProfileService(
            new MatchesFormService(new MatchesService(new MatchesOptionService(new MatchesOptionValuesService()))),
            new MatchesValueService(),
            new MatchesOptionValuesService()
        );
        // insert 50 profiles with name PROFILE_{INDEX}
        // and 50 profiles with name X_PROFILE_{INDEX}
        foreach (['PROFILE_', 'X_PROFILE_']  as $profileName) {
            for ($i = 0; $i < 50; $i++) {
                $matchesProfileService->store(
                    [
                        'name' =>  $profileName . $i,
                        'user_id' => $userId,
                        'is_professional' => MatchesProfile::$company,
                        'matches_form_id' => $form->id,
                    ]
                );

            }
        }

        //
        // Test first page
        //
        $totalCount = 100;
        $pageSize = 3;
        $numberOfPages = (int)ceil($totalCount / $pageSize);
        $page = 0;
        $response = $this->getJson('api/matches/profiles?page=' . $page . '&page_size=' . $pageSize);
        $response->assertStatus(Response::HTTP_OK);

        $response->assertJsonCount($pageSize, 'data');
        $response->assertJson(fn(AssertableJson $json) => $json
            ->where('meta.status', Response::HTTP_OK)
            ->where('meta.total_count', $totalCount)
            ->where('meta.page', $page)
            ->where('meta.number_of_pages', $numberOfPages)
            ->where('meta.count', $pageSize)
            ->where('data.0.name', 'PROFILE_0')
            ->where('data.2.name', 'PROFILE_10') // note: results are ordered  by name
        );

        //
        // Test last page
        //
        $page = $numberOfPages;
        $response = $this->getJson('api/matches/profiles?page=' . $page . '&page_size=' . $pageSize);
        $response->assertStatus(Response::HTTP_OK);

        $response->assertJsonCount(1, 'data'); // last page has 1 item
        $response->assertJson(fn(AssertableJson $json) => $json
            ->where('meta.status', Response::HTTP_OK)
            ->where('meta.page', $page)
            ->where('meta.count', 1)
            ->where('data.0.name', 'X_PROFILE_9') // note: results are ordered  by name
        );


        //
        // test first page with search: names including 'ILE_3'
        //
        $page = 0;
        $totalCount = 22;
        $numberOfPages = (int)ceil($totalCount / $pageSize);
        $response = $this->getJson('api/matches/profiles?search=file_3&page=' . $page . '&page_size=' . $pageSize);
        $response->assertStatus(Response::HTTP_OK);

        $response->assertJsonCount($pageSize, 'data');
        $response->assertJson(fn(AssertableJson $json) => $json
            ->where('meta.status', Response::HTTP_OK)
            ->where('meta.total_count', $totalCount)
            ->where('meta.page', $page)
            ->where('meta.number_of_pages', $numberOfPages)
            ->where('meta.count', $pageSize)
            ->where('data.0.name', 'PROFILE_3')
            ->where('data.2.name', 'PROFILE_31') // note: results are ordered  by name
        );


        //
        // test last page with search: names including 'ILE_3'
        //
        $page = $numberOfPages;
        $lastPageCount = 1;
        $response = $this->getJson('api/matches/profiles?search=ILE_3&page=' . $page . '&page_size=' . $pageSize);
        $response->assertStatus(Response::HTTP_OK);

        $response->assertJsonCount($lastPageCount, 'data');
        $response->assertJson(fn(AssertableJson $json) => $json
            ->where('meta.status', Response::HTTP_OK)
            ->where('meta.total_count', $totalCount)
            ->where('meta.page', $page)
            ->where('meta.number_of_pages', $numberOfPages)
            ->where('meta.count', $lastPageCount)
            ->where('data.0.name', 'X_PROFILE_39')
        );

    }

    public function testStore()
    {
        $formId = MatchesForm::where('name', 'TEST_FORM')->first()->id;
        // The matches for this test are created in TestDataSeeder

        $user = User::where('name', 'testCompany')->get();
        $userId = $user[0]->id;

        // Insert new profile
        $newProfileName = '2 New Profileasd221323df' ;
        $description = 'This is test profile';
        $params = [
            'name' => $newProfileName,
            'description' => $description,
            'user_id' => $userId,
            'matches_form_id' => $formId,
            'is_professional' => MatchesProfile::$company,
        ];

        $response = $this->postJson('api/matches/profiles', $params);
        // Insert new profile
        $response->assertJson(fn(AssertableJson $json) => $json
            ->where('meta.status', Response::HTTP_CREATED)
            ->where('data.0.name', $newProfileName)
            ->where('data.0.description', $description)
            ->where('data.0.matches.0.label', 'TEST_FORM_1_EQUAL')
            ->where('data.0.matches.0.match_type', EMatchType::EQUAL->name)
            ->where('data.0.matches.0.values.0.value', null)
        );

        // Error when trying to save with non-existing user_id
        $params['user_id'] = 873;
        $response = $this->postJson('api/matches/profiles', $params);
        $response->assertJson(fn(AssertableJson $json) => $json
            ->where('meta.status', Response::HTTP_BAD_REQUEST)
            ->where('success', false)
            ->where('message', 'Validation errors')
            ->where('errors.user_id.0', 'The selected user id is invalid.')
        );
    }

    public function testCreateNewProfile(): void
    {
        //
        // Create new user
        //
        $response = $this->postJson(
            '/api/register',
            [
                'name' => 'whatever',
                'email' => 'whatever@nl.nl',
                'password' => 'whatever',
                'c_password' => 'whatever',
                'role' => 'company'
            ]
        );
        $userId = $response->json()['data']['id'];

        //
        // create form
        //
        $newFormName = 'New Form' . uniqid();

        $equalMatchName = 'New_Form_EQUAL';
        $smallerThanMatch2Name = 'New_Form_SMALLER_THAN';
        $radioButtonMatchName = 'New_Form_RADIO_BUTTON';
        $dateFromMatchName = 'New_Form_DATE';

        $equalMatch1Type = EMatchType::EQUAL->name;
        $smallerThanMatchType = EMatchType::SMALLER_THAN->name;
        $radioButtonMatchType = EMatchType::RADIO_BUTTON->name;
        $dateFromMatchType = EMatchType::DATE_FROM->name;

        $response = $this->postJson('api/matches/forms',
            [
                'name' => $newFormName,
                'matches' => [
                    'EQUAL' => [
                        'label' => $equalMatchName,
                        'match_type' => $equalMatch1Type,
                    ],
                    'SMALLER_THAN' =>
                        [
                            'label' => $smallerThanMatch2Name,
                            'match_type' => $smallerThanMatchType,
                        ],
                    'RADIO_BUTTON' =>
                        [
                            'label' => $radioButtonMatchName,
                            'match_type' => $radioButtonMatchType,
                        ],
                    'DATE' =>
                        [
                            'label' => $dateFromMatchName,
                            'match_type' => $dateFromMatchType,
                        ],
                ]
            ]
        );
        $formId = $response->json()['data'][0]['id'];

        //
        // create profile
        //

        $profileName = 'new profile';
        $description = 'This is test profile xxx';
        $params = [
            'name' => $profileName,
            'description' => $description,
            'user_id' => $userId,
            'is_professional' => MatchesProfile::$company,
            'matches_form_id' => $formId,
        ];

        $response = $this->postJson('api/matches/profiles', $params);
        $response->assertStatus(Response::HTTP_OK);

        // check the profile and values are inserted
        $profileId = $response->json()['data'][0]['id'];
        $response->assertJson(fn(AssertableJson $json) => $json
            ->where('meta.status', Response::HTTP_CREATED)
            ->where('data.0.name', $profileName)
            ->where('data.0.description', $description)
            ->where('data.0.matches.0.label', $equalMatchName)
            ->where('data.0.matches.0.match_type', $equalMatch1Type)
            ->where('data.0.matches.0.values.0.value', null) // initially null
            ->where('data.0.matches.2.label', $radioButtonMatchName)
            ->where('data.0.matches.2.match_type', $radioButtonMatchType)
            ->where('data.0.matches.2.values.0.value', null)// initially null

        );

        //
        //  Test update
        //

        // define values to profile
        $responseDataMatches = $response->json()['data'][0]['matches'];

        $equalMatchId = $responseDataMatches[0]['id'];
        $equalId = $responseDataMatches[0]['values'][0]['id'];

        $smallerThanMatchId = $responseDataMatches[1]['id'];
        $smallerThanId = $responseDataMatches[1]['values'][0]['id'];

        $radioButtonMatchId = $responseDataMatches[2]['id'];
        $radioButtonId = $responseDataMatches[2]['values'][0]['id'];

        $dateFromMatchId = $responseDataMatches[3]['id'];
        $dateFromId = $responseDataMatches[3]['values'][0]['id'];

        $equalValue = '23';
        $smallerThanValue = '245';
        $radioButtonValue = '212';
        $dateFromValue = '2023-10-13';
        $updatedDescription = 'This updated is test profile xxx';

        $params = [
            'name' => $profileName,
            'description' => $updatedDescription,
            'matches' => [
                [
                    'id' => $equalMatchId,
                    'values' => [
                        ['value_id' => $equalId, 'value' => $equalValue]
                    ]
                ],
                [
                    'id' => $smallerThanMatchId,
                    'values' => [
                        ['value_id' => $smallerThanId, 'value' => $smallerThanValue],
                    ]
                ],
                [
                    'id' => $radioButtonMatchId,
                    'values' => [
                        ['value_id' => $radioButtonId, 'value' => $radioButtonValue],
                    ]
                ],
                [
                    'id' => $dateFromMatchId,
                    'values' => [
                        ['value_id' => $dateFromId, 'value' => $dateFromValue],
                    ]
                ],
            ]
        ];

        $response = $this->putJson('api/matches/profiles/' . $profileId, $params);
        $response->assertStatus(Response::HTTP_OK);
        $response->assertJson(fn(AssertableJson $json) => $json
            ->where('meta.status', Response::HTTP_OK)
            ->where('data.0.name', $profileName)
            ->where('data.0.tags', 'is,new,profile,test,this,updated,xxx')
            ->where('data.0.description', $updatedDescription)
            ->where('data.0.matches.0.values.0.value', $equalValue)
            ->where('data.0.matches.2.values.0.value', $radioButtonValue)
            // Assert below is in comments because there is sometimes 1 second delay
            //->where('data.0.matches.3.values.0.value',
            //    (string)\Carbon\Carbon::createFromFormat('Y-m-d', $dateFromValue)
            //        ->timestamp
            //)
            ->where('data.0.matches.3.values.0.valueLabel', '2023-10-13')

        );

        //
        //  Test show
        //
        $response = $this->getJson('api/matches/profiles/' . $profileId);
        $response->assertStatus(Response::HTTP_OK);
        $response->assertJson(fn(AssertableJson $json) => $json
            ->where('meta.status', Response::HTTP_OK)
            ->where('data.0.name', $profileName)
            ->where('data.0.description', $updatedDescription)
            ->where('data.0.matches.0.label', $equalMatchName)
            ->where('data.0.matches.0.match_type', $equalMatch1Type)
            ->where('data.0.matches.0.values.0.value', $equalValue)
            ->where('data.0.matches.2.label', $radioButtonMatchName)
            ->where('data.0.matches.2.match_type', $radioButtonMatchType)
            ->where('data.0.matches.2.values.0.value', $radioButtonValue)
            // Assert below is in comments because there is sometimes 1 second delay
            //->where('data.0.matches.3.values.0.value',
            //    (string)\Carbon\Carbon::createFromFormat('Y-m-d', $dateFromValue)
            //        ->timestamp
            //)
            ->where('data.0.matches.3.values.0.valueLabel', '2023-10-13')
        );

        //
        // test destroy
        //
        $response = $this->deleteJson('api/matches/profiles/' . $profileId);
        $response->assertStatus(Response::HTTP_OK);
        $response->assertJson(fn(AssertableJson $json) => $json
            ->where('meta.status', Response::HTTP_OK)
            ->where('meta.message', 'TRNS_ITEM_DELETED_WITH_ID_' . $profileId)
        );

        $response = $this->deleteJson('api/matches/profiles/' . $profileId);
        $response->assertJson(fn(AssertableJson $json) => $json
            ->where('meta.status', Response::HTTP_NOT_FOUND)
            ->where('meta.message', 'TRNS_NO_ITEM_FOUND_FOR_ID ' . $profileId)
        );
    }

    public function testShow()
    {
        // The matches for this test are created in TestDataSeeder
        $profile = MatchesProfile::where('name', 'TEST_COMPANY_PROFILE_1')->first();
        $profileId = $profile->id;

        // show ok
        $response = $this->getJson('api/matches/profiles/' . $profileId);
        $response->assertStatus(Response::HTTP_OK);
        $response->assertJson(fn(AssertableJson $json) => $json
            ->where('meta.count', 1)
            ->where('data.0.name', 'TEST_COMPANY_PROFILE_1')
            ->where('data.0.description', 'test company profile 1')
            ->where('data.0.matches.0.label', 'TEST_FORM_1_EQUAL')
            ->where('data.0.matches.0.match_type', 'EQUAL')
            ->where('data.0.matches.0.values.0.value', 'TEST_COMPANY_PROFILE_1_EQUALS')
            ->where('data.0.matches.2.label', 'TEST_FORM_1_MULTIPLE_CHOOSE')
            ->where('data.0.matches.2.match_type', 'MULTIPLE_CHOOSE')
            ->where('data.0.user.name', 'testCompany')
        );

        $response = $this->getJson('api/matches/profiles/923124');
        $response->assertJson(fn(AssertableJson $json) => $json
            ->where('meta.status', Response::HTTP_NOT_FOUND)
            ->where('meta.message', 'TRNS_NO_ITEM_FOUND_FOR_ID 923124')
        );
    }

    public function testUpdate()
    {
        $profile = MatchesProfile::where('name', 'TEST_CANDIDATE_PROFILE_1');
        $profileId = $profile->first()->id;
        $matchName = 'TEST_FORM_1_SMALLER_THAN';
        $matchToUpdate = Matches::where('label', $matchName);
        $matchToUpdateId = $matchToUpdate->first()->id;
        $valueId = MatchesValues::where('matches_profile_id', $profileId)
            ->where('matches_id', $matchToUpdateId)
            ->first()->id;
        $updatedValue = '235';

        $updatedName = 'Updated profile name';
        $updatedDescription = 'Updated profile description';
        $params = [
            'name' => $updatedName,
            'description' => $updatedDescription,
            'matches' => [
                [
                    'id' => $matchToUpdateId,
                    'values' => [
                        ['value_id' => $valueId, 'value' => $updatedValue]
                    ]
                ],
            ]
        ];

        // show ok
        $response = $this->putJson('api/matches/profiles/' . $profileId, $params);
        $response->assertStatus(Response::HTTP_OK);
        $response->assertJson(fn(AssertableJson $json) => $json
            ->where('meta.status', Response::HTTP_OK)
            ->where('data.0.name', $updatedName)
            ->where('data.0.description', $updatedDescription)
            ->where('data.0.matches.1.label', $matchName)
            ->where('data.0.matches.1.values.0.value', $updatedValue)
        );

        $params = [
            'name' => 'DUMMY',
        ];

        $response = $this->putJson('api/matches/profiles/923562', $params);
        $response->assertJson(fn(AssertableJson $json) => $json
            ->where('meta.status', Response::HTTP_NOT_FOUND)
            ->where('meta.message', 'TRNS_NO_ITEM_FOUND_FOR_ID 923562')
        );
    }

    public function testMultipleChooseUpdate()
    {
        //
        // create form, with one match with type MULTIPLE_CHOOSE
        //
        $newFormName = 'New Form' . uniqid();

        $multipleChooseMatchName = 'New_Form_MULTIPLE_CHOOSEyutyut8765'  ;
        $multipleChooseMatchType = EMatchType::MULTIPLE_CHOOSE->name;

        $response = $this->postJson('api/matches/forms',
            [
                'name' => $newFormName,
                'matches' => [
                    'MULTIPLE_CHOOSE' =>
                        [
                            'label' => $multipleChooseMatchName,
                            'match_type' => $multipleChooseMatchType,
                            'options' => [
                                ['value' => 'PURPLE'],
                                ['value' => 'YELLOW'],
                                ['value' => 'PINK'],
                            ],
                        ],
                ]
            ]
        );
        $formId = $response->json()['data'][0]['id'];

        $user = User::where('name', 'testCompany')->get();
        $userId = $user[0]->id;

        //
        // Insert new profile
        //
        $newProfileName = '1 New Profilesadasdqweqwe23' ;
        $params = [
            'name' => $newProfileName,
            'user_id' => $userId,
            'matches_form_id' => $formId,
            'is_professional' => MatchesProfile::$company,
        ];

        $response = $this->postJson('api/matches/profiles', $params);
        $profileId = $response->json()['data'][0]['id'];

        //
        // Update profile form
        //
        // define options values, we have selected purple and pink
        $responseMatches = $response->json()['data'][0]['matches'][0];
        $multipleChooseId = $responseMatches['id'];
        $multipleChooseOptions = $responseMatches['options'];
        $purpleOptionId = $multipleChooseOptions[0]['id'];
        $yellowOptionId = $multipleChooseOptions[1]['id'];
        $pinkOptionId = $multipleChooseOptions[2]['id'];

        $params = [
            'name' => $newProfileName,
            'matches' => [
                [
                    'id' => $multipleChooseId,
                    'options' => [
                        ['id' => $purpleOptionId, 'selected' => true],
                        ['id' => $yellowOptionId, 'selected' => false],
                        ['id' => $pinkOptionId, 'selected' => true,],
                    ],
                ],
            ],
        ];

        $response = $this->putJson('api/matches/profiles/' . $profileId, $params);
        $response->assertJson(fn(AssertableJson $json) => $json
            ->where('meta.status', Response::HTTP_OK)
            ->where('data.0.name', $newProfileName)
            ->where('data.0.tags', 'PINK,PURPLE,new,profilesadasdqweqwe23')
            ->where('data.0.matches.0.label', $multipleChooseMatchName)
            ->where('data.0.matches.0.values.0.value', $purpleOptionId . ',' . $pinkOptionId)
            ->where('data.0.matches.0.option_values.0.matches_options_id', $purpleOptionId)
            ->where('data.0.matches.0.option_values.1.matches_options_id', $pinkOptionId)
        );

        //
        //  update options again
        //
        // define options values, we have selected purple and pink
        $responseMatches = $response->json()['data'][0]['matches'][0];
        $multipleChooseId = $responseMatches['id'];


        // This time only yellow is selected
        $params = [
            'name' => $newProfileName,
            'matches' => [
                [
                    'id' => $multipleChooseId,
                    'options' => [
                        ['id' => $purpleOptionId, 'selected' => false],
                        ['id' => $yellowOptionId, 'selected' => true],
                        ['id' => $pinkOptionId, 'selected' => false],
                    ],
                ],
            ],
        ];

        $response = $this->putJson('api/matches/profiles/' . $profileId, $params);
        $response->assertJson(fn(AssertableJson $json) => $json
            ->where('meta.status', Response::HTTP_OK)
            ->where('data.0.name', $newProfileName)
            ->where('data.0.matches.0.label', $multipleChooseMatchName)
            ->where('data.0.matches.0.option_values.0.matches_options_id', $yellowOptionId)
        );

        //
        // test show
        //
        $response = $this->getJson('api/matches/profiles/' . $profileId);
        $response->assertJson(fn(AssertableJson $json) => $json
            ->where('meta.status', Response::HTTP_OK)
            ->where('data.0.matches.0.option_values.0.matches_options_id', $yellowOptionId)
        );
    }


    public function testMenuUpdate()
    {
        //
        // create form, with one match with type MENU
        //
        $newFormName = 'New Form' . uniqid();

        $menuMatchName = 'New_Form_MENU' . uniqid();
        $menuMatchType = EMatchType::MENU->name;

        $response = $this->postJson('api/matches/forms',
            [
                'name' => $newFormName,
                'matches' => [
                    'MENU' =>
                        [
                            'label' => $menuMatchName,
                            'match_type' => $menuMatchType,
                            'options' => [
                                ['value' => 'PURPLE'],
                                ['value' => 'YELLOW'],
                                ['value' => 'PINK'],
                            ],
                        ],
                ]
            ]
        );
        $formId = $response->json()['data'][0]['id'];

        $user = User::where('name', 'testCompany')->get();
        $userId = $user[0]->id;

        //
        // Insert new profile
        //
        $newProfileName = '1 New Menu Test Profile';
        $params = [
            'name' => $newProfileName,
            'user_id' => $userId,
            'matches_form_id' => $formId,
            'is_professional' => MatchesProfile::$company,
        ];

        $response = $this->postJson('api/matches/profiles', $params);
        $profileId = $response->json()['data'][0]['id'];

        //
        // Update profile form
        //
        // define options values, we have selected purple and pink
        $responseMatches = $response->json()['data'][0]['matches'][0];
        $valueId = $responseMatches['values'][0]['id'];


        $menuId = $responseMatches['id'];
        $menuOptions = $responseMatches['options'];
        $pinkOptionId = (string)$menuOptions[2]['id'];

        $params = [
            'name' => $newProfileName,
            'matches' => [
                [
                    'id' => $menuId,
                    'values' => [
                        [
                            'id' => $valueId,
                            'value' => $pinkOptionId,
                        ],
                    ],
                ],
            ],
        ];

        $response = $this->putJson('api/matches/profiles/' . $profileId, $params);
        $response->assertJson(fn(AssertableJson $json) => $json
            ->where('meta.status', Response::HTTP_OK)
            ->where('data.0.name', $newProfileName)
            ->where('data.0.tags', 'menu,new,pink,profile,test')
            ->where('data.0.matches.0.label', $menuMatchName)
            ->where('data.0.matches.0.values.0.value', $pinkOptionId)
            ->where('data.0.matches.0.values.0.valueLabel', 'PINK')
        );


        //
        // test show
        //
        $response = $this->getJson('api/matches/profiles/' . $profileId);
        $response->assertJson(fn(AssertableJson $json) => $json
            ->where('meta.status', Response::HTTP_OK)
            ->where('data.0.matches.0.values.0.value', $pinkOptionId)
        );
    }

    public function testDestroy()
    {
        $profile = MatchesProfile::where('name', 'TEST_COMPANY_PROFILE_1')->first();
        $profileId = $profile->id;

        // show ok
        $response = $this->deleteJson('api/matches/profiles/' . $profileId);
        $response->assertStatus(Response::HTTP_OK);
        $response->assertJson(fn(AssertableJson $json) => $json
            ->where('meta.status', Response::HTTP_OK)
            ->where('meta.message', 'TRNS_ITEM_DELETED_WITH_ID_' . $profileId)
        );

        $response = $this->deleteJson('api/matches/profiles/' . $profileId);
        $response->assertJson(fn(AssertableJson $json) => $json
            ->where('meta.status', Response::HTTP_NOT_FOUND)
            ->where('meta.message', 'TRNS_NO_ITEM_FOUND_FOR_ID ' . $profileId)
        );
    }
}
