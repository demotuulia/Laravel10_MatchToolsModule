<?php

namespace Integration;

use Illuminate\Http\Response;
use Illuminate\Testing\Fluent\AssertableJson;
use Modules\Matches\Enums\EMatchType;
use Modules\Matches\Models\MatchesProfile;
use Tests\Feature\LoginBase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Matches\Models\MatchType;
use Database\Seeders\TestUsersSeeder;

class DashboardControllerTest extends LoginBase
{
    use RefreshDatabase;

    public function testDashboard(): void
    {

        //
        // Insert form
        //
        $newFormName = 'Dashboard test ' . uniqid();
        $form = [
            'name' => $newFormName,
            'matches' => [
                'EQUAL' => [
                    'label' => 'New_Form_EQUAL',
                    'match_type' => EMatchType::EQUAL->name,
                ],

                'MULTIPLE_CHOOSE' =>
                    [
                        'label' => 'New_Form_MULTIPLE_CHOOSE',
                        'match_type' => EMatchType::MULTIPLE_CHOOSE->name,
                        'options' => [
                            ['value' => 'PURPLE'],
                            ['value' => 'YELLOW'],
                            ['value' => 'PINK'],
                        ]
                    ],
                'MENU' => [
                    'label' => 'New_Form_MENU',
                    'match_type' => EMatchType::MENU->name,
                    'options' => [
                        ['value' => 'MONDAY'],
                        ['value' => 'TUESDAY'],
                    ]
                ],
                'RADIO_BUTTON' => [
                    'label' => 'New_Form_RADIO_BUTTON',
                    'match_type' => EMatchType::RADIO_BUTTON->name,
                ],
            ]
        ];
        $response = $this->postJson(
            'api/matches/forms',
            $form,
        );


        $formId = $response->json()['data'][0]['id'];
        $data = $response->json()['data'][0];
        $matches = $data['matches'];

        $equalMatchId = $matches[0]['id'];

        $multipleChooseMatchId = $matches[1]['id'];
        $purpleOptionId = $matches[1]['options'][0]['id'];
        $yellowOptionId = $matches[1]['options'][1]['id'];
        $pinkOptionId = $matches[1]['options'][2]['id'];

        $menuMatchId = $matches[2]['id'];
        $mondayId = $matches[2]['options'][0]['id'];
        $tuesdayId = $matches[2]['options'][1]['id'];

        $radioButtonMatchId = $matches[3]['id'];


        //
        // Insert users and profiles
        //
        $users = [
            [
                'name' => 'company1',
                'matches' => [
                    'EQUAL' => 2,
                    'MULTIPLE_CHOOSE' => [$purpleOptionId, $pinkOptionId],
                    'MENU' => $tuesdayId,
                    'RADIO_BUTTON' => 0,
                ]
            ],
            [
                'name' => 'company2',
                'matches' => [
                    'EQUAL' => 1,
                    'MULTIPLE_CHOOSE' => [$purpleOptionId, $yellowOptionId],
                    'MENU' => $tuesdayId,
                    'RADIO_BUTTON' => 0,
                ]
            ],
            [
                'name' => 'company3',
                'matches' => [
                    'EQUAL' => 2,
                    'MULTIPLE_CHOOSE' => [$yellowOptionId, $pinkOptionId],
                    'MENU' => $tuesdayId,
                    'RADIO_BUTTON' => 1,
                ]
            ],
            [
                'name' => 'company4',
                'matches' => [
                    'EQUAL' => 2,
                    'MULTIPLE_CHOOSE' => [$yellowOptionId, $pinkOptionId],
                    'MENU' => $mondayId,
                    'RADIO_BUTTON' => 0,
                ]
            ],
        ];


        foreach ($users as $user) {
            // register user
            $response = $this->postJson(
                '/api/register',
                [
                    'name' => $user['name'], 'email' => $user['name'] . '@' . uniqid() . '.cd',
                    'password' => 'whatever', 'c_password' => 'whatever', 'role' => 'company',
                ]
            );
            $userId = $response->json()['data']['id'];

            // insert profile (without values)
            $params = [
                'name' => 'whatever', 'description' => 'whatever', 'user_id' => $userId,
                'is_professional' => MatchesProfile::$company, 'matches_form_id' => $formId,
            ];
            $response = $this->postJson('api/matches/profiles', $params);
            $matches = $response->json()['data'][0]['matches'];

            $equalValueId = $matches[0]['values'][0]['id'];
            $multipleChooseValueId = $matches[1]['values'][0]['id'];
            $menuValueId = $matches[2]['values'][0]['id'];
            $radioButtonMatchValueId = $matches[3]['values'][0]['id'];

            // update  profile by  values
            $profileId = $response->json()['data'][0]['id'];
            $params = [
                'name' => uniqid(),
                'description' => uniqid(),
            ];
            $params['matches'] = [];
            foreach ($user['matches'] as $key => $userMatch) {
                $matchId = match ($key) {
                    'EQUAL' => $equalMatchId,
                    'MULTIPLE_CHOOSE' => $multipleChooseMatchId,
                    'MENU' => $menuMatchId,
                    'RADIO_BUTTON' => $radioButtonMatchId,
                };
                $valueId = match ($key) {
                    'EQUAL' => $equalValueId,
                    'MULTIPLE_CHOOSE' => $multipleChooseValueId,
                    'MENU' => $menuValueId,
                    'RADIO_BUTTON' => $radioButtonMatchValueId,
                };

                $match = ['id' => $matchId,];

                if ($key == 'MULTIPLE_CHOOSE') {
                    $match['options'] = [
                        ['id' => $purpleOptionId, 'selected' => in_array($purpleOptionId, $userMatch)],
                        ['id' => $yellowOptionId, 'selected' => in_array($yellowOptionId, $userMatch)],
                        ['id' => $pinkOptionId, 'selected' => in_array($pinkOptionId, $userMatch)],
                    ];
                } else {
                    $match['values'] = [
                        ['value_id' => $valueId, 'value' => $user['matches'][$key]]
                    ];
                }
                $params['matches'][] = $match;
            }

            $this->putJson('api/matches/profiles/' . $profileId, $params);
        }


        $response = $this->getJson(
            'api/matches/dashboard?form_id=' . $formId
        );
        $response->assertJsonCount(4, 'data.statistics');
        $response->assertJson(fn(AssertableJson $json) => $json
            ->where('meta.status', Response::HTTP_OK)

            ->where('data.statistics.0.name', 'New_Form_EQUAL')
            ->where('data.statistics.0.values.companies.labels.0', '1')
            ->where('data.statistics.0.values.companies.labels.1', '2')
            ->where('data.statistics.0.values.companies.data.0', 1)
            ->where('data.statistics.0.values.companies.data.1', 3)

            ->where('data.statistics.1.name', 'New_Form_MULTIPLE_CHOOSE')
            ->where('data.statistics.1.values.companies.labels.0', 'PINK')
            ->where('data.statistics.1.values.companies.labels.1', 'PURPLE')
            ->where('data.statistics.1.values.companies.labels.2', 'YELLOW')
            ->where('data.statistics.1.values.companies.data.0', 3)
            ->where('data.statistics.1.values.companies.data.1', 2)
            ->where('data.statistics.1.values.companies.data.2', 3)


            ->where('data.statistics.2.name', 'New_Form_MENU')
            ->where('data.statistics.2.values.companies.labels.0', 'MONDAY')
            ->where('data.statistics.2.values.companies.labels.1', 'TUESDAY')
            ->where('data.statistics.2.values.companies.data.0', 1)
            ->where('data.statistics.2.values.companies.data.1', 3)


            ->where('data.statistics.3.name', 'New_Form_RADIO_BUTTON')
            ->where('data.statistics.3.values.companies.labels.0', '0')
            ->where('data.statistics.3.values.companies.labels.1', '1')
            ->where('data.statistics.3.values.companies.data.0', 3)
            ->where('data.statistics.3.values.companies.data.1', 1)
        );
    }
}
