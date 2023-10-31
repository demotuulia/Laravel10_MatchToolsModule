<?php

namespace Integration;

use Database\Seeders\TestUsersSeeder;
use Illuminate\Foundation\Testing\DatabaseTruncation;
use Illuminate\Testing\Fluent\AssertableJson;
use Modules\Matches\Enums\EMatchType;
use Modules\Matches\Models\MatchesProfile;
use Modules\Matches\Models\MatchType;
use Tests\Feature\LoginBase;

class DemoTest extends LoginBase
{
    use DatabaseTruncation;

    /**
     * This is a simple demo test to give a quick overview of this system.
     * You can see how an api client would create a form, user profiles and find matches by using
     * the enpints of the API. The details of each endpoint are explained in the read.md file
     * We do the following:
     *
     * 1) Insert a new form with following matches:
     *      . Years of experience
     *               type: BIGGER_THAN
     *               For a match the candidate  must have more ears of experience
     *              than the company profile requires.
     *      . Available from
     *               type: DATE_TO
     *               For a match the candidate  must be available before the available date  of
     *               the company vacancy.
     *
     *  2) Insert one company with one vacancy (profile) and three professionals with a profile
     *
     *  3) Search matches for the company vacancy from the professional profiles and check results.
     *      We should de profiles which has more  than 4 years experience and/or be available before '2023-07-13'
     *
     */
    public function testDemo(): void
    {

        /* ***********************************************************************************
         *
         * Create form
         *
         * After creating it we read from the response the created ids for
         * different items.
         *
         * ***********************************************************************************/

        $form = [
            'name' => 'Demo Form',
            'matches' => [
                // The professional must have this amount of years of experience
                'YEARS_OF_EXPERIENCE' =>
                    [
                        'label' => 'Years of experience',
                        'match_type' => EMatchType::BIGGER_THAN->name,
                    ],
                // The professional must available before this date
                'AVAILABLE_FROM' => [
                    'label' => 'Available from',
                    'match_type' => EMatchType::DATE_TO->name,
                ],
            ]
        ];

        // Call the endpoint: 'forms.store' to make a new form
        $response = $this->postJson('api/matches/forms', $form);

        $formData = $response->json()['data'][0];
        $formId = $formData['id'];


        /* ***********************************************************************************
        *
        * Create users and profiles for them
        *
        *
        * ***********************************************************************************/

        $userProfiles = [
            // This company will search matches by this vacancy profile
            'Demo-Company' => [
                'name' => 'Demo-Company',
                'role' => 'company',
                'matches' => [
                    'YEARS_OF_EXPERIENCE' => 4,
                    'AVAILABLE_FROM' => '2023-07-13',
                ],
            ],
            // These professionals are to be searched
            [
                'name' => 'Professional-1',
                'role' => 'professional',
                'matches' => [
                    'YEARS_OF_EXPERIENCE' => 2,
                    'AVAILABLE_FROM' => '2023-06-13',
                ],
                // 1 match, expected score 50%
            ],
            [
                'name' => 'Professional-2',
                'role' => 'professional',
                'matches' => [
                    'YEARS_OF_EXPERIENCE' => 5,
                    'AVAILABLE_FROM' => '2023-06-13',
                ],
                // 2 matches, expected score 100%
            ],
            [
                'name' => 'Professional-3',
                'role' => 'professional',
                'matches' => [
                    'YEARS_OF_EXPERIENCE' => 2,
                    'AVAILABLE_FROM' => '2023-11-13',
                ],
                // no matches, not to be expected in the search results
            ]
        ];


        $demoCompanyProfileId = null; // we need this profile id to search matches in our test
        foreach ($userProfiles as $key => $userProfile) {
            // Call endpoint 'register' to add a new  user with the given role
            $userId = $this->registerUser($userProfile['name'], $userProfile['role'], $formId);
            // create new profile by calling the endpoint 'profiles.store'
            $profile = $this->insertProfile($userId, $formId, $userProfile);


            /**
             *  As a response we  profile form in a json string like in the example below.
             *  Here are only the essentials fields shown.
             *  We update the values (which are initially 'null')
             *

                    {
                        "id":5,
                       "user_id":4,
                       "name":"Demo-Company",
                       "matches":[
                          {
                             "label":"Years of experience",
                             "values":[
                                {
                                   "value":null,
                                }
                             ],
                          },
                          {
                             "label":"Available from",
                             "values":[
                                {
                                   "value":null,
                                }
                             ],
                          }
                       ]
                    }
              */

            $profile['matches'][0]['values'][0]['value'] = $userProfile['matches']['YEARS_OF_EXPERIENCE'];
            $profile['matches'][1]['values'][0]['value'] = $userProfile['matches']['AVAILABLE_FROM'];

            // update form with values and post it by calling the endpoint 'profiles.update'
            $this->putJson('api/matches/profiles/' . $profile['id'], $profile);

            if ($key == 'Demo-Company') {
                $demoCompanyProfileId = $profile['id']; // save for our test
            }
        }

        /* ***********************************************************************************
        *
        *  Search matches for the Demo Company vacancy and check results
        *
        *
        * ***********************************************************************************/

        //
        $response = $this->getJson(
            'api/matches/search' . '?profile_id=' .$demoCompanyProfileId
        );

        // We should de profiles which has more  than 4 years experience and/or be available before '2023-07-13'
        $response->assertJson(fn(AssertableJson $json) => $json
            ->where('meta.total_count', 2)
            ->where('data.0.name', 'Professional-2')
            ->where('data.0.score', 100)
            ->where('data.1.name', 'Professional-1')
            ->where('data.1.score', 50)
        );
    }


    /**
     * Call endpoint register to add user
     */
    private function registerUser(string $name, string $role, int $formId): int
    {
        $response = $this->postJson(
            '/api/register',
            [
                'name' => $name,
                'email' => $name . '@test.nl',
                'password' => 'whatever',
                'c_password' => 'whatever',
                'role' => $role,
                'form_id' => $formId,
            ]
        );

        return $response->json()['data']['id'];
    }

    /**
     * Create new profile by calling the endpoint 'profiles.store'
     *
     * @return array Profile form
     */
    private function insertProfile(int $userId, int $formId, array $userProfile): array
    {
        $response = $this->postJson('api/matches/profiles', [
            'name' => $userProfile['name'],
            'description' => 'dummy',
            'user_id' => $userId,
            'is_professional' => $userProfile['role'] == 'company'
                ? MatchesProfile::$company
                : MatchesProfile::$professional,
            'matches_form_id' => $formId,
        ]);

        return $response->json()['data'][0];
    }
}
