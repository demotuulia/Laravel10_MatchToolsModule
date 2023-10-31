<?php
/**
 * // php artisan db:seed --class=TestUsersSeeder
 *  php artisan module:seed Matches --class="Tests\\TestDataSeeder"
 */

namespace Modules\Matches\Database\Seeders\Tests;

use App\Models\User;
use Illuminate\Database\Seeder;
use Modules\Matches\Enums\EMatchType;
use Modules\Matches\Models\Matches;
use Modules\Matches\Models\MatchesForm;
use Modules\Matches\Models\MatchesOptions;
use Modules\Matches\Models\MatchesValues;
use Modules\Matches\Services\MatchesFormService;
use Modules\Matches\Services\MatchesOptionService;
use Modules\Matches\Services\MatchesOptionValuesService;
use Modules\Matches\Services\MatchesProfileService;
use Modules\Matches\Services\MatchesService;
use Modules\Matches\Services\MatchesValueService;


class TestDataSeeder extends Seeder
{
    private static array $users = [];

    private static array $forms = [
        'TEST_FORM' => [
            'name' => 'TEST_FORM',
            'matches' => [
                'TEST_FORM_1_EQUAL' => [
                    'label' => 'TEST_FORM_1_EQUAL',
                    'match_type' => EMatchType::EQUAL,
                ],
                'TEST_FORM_1_SMALLER_THAN' =>
                    [
                        'label' => 'TEST_FORM_1_SMALLER_THAN',
                        'match_type' => EMatchType::SMALLER_THAN,
                    ],
                'TEST_FORM_1_MULTIPLE_CHOOSE' =>
                    [
                        'label' => 'TEST_FORM_1_MULTIPLE_CHOOSE',
                        'match_type' => EMatchType::MULTIPLE_CHOOSE,
                        'options' => [
                            'OPTION_1' => [
                                'matches_name' => 'TEST_FORM_1_MULTIPLE_CHOOSE',
                                'value' => 'OPTION_1',
                                'order' => 0,
                            ],
                            'OPTION_2' => [
                                'matches_name' => 'TEST_FORM_1_MULTIPLE_CHOOSE',
                                'value' => 'OPTION_2',
                                'order' => 1,
                            ],
                            'OPTION_3' => [
                                'matches_name' => 'TEST_FORM_1_MULTIPLE_CHOOSE',
                                'value' => 'OPTION_3',
                                'order' => 2,
                            ],
                        ]
                    ],
            ]
        ],
    ];

    private static array $profiles = [

        'TEST_COMPANY_PROFILE_1' => [
            'name' => 'TEST_COMPANY_PROFILE_1',
            'user' => 'testCompany',
            'is_professional' => 0,
            'matches_form_name' => 'TEST_FORM',
            'description' => 'test company profile 1',
            'matches' => [
                [
                    'match_type' => EMatchType::EQUAL->name,
                    'matches_name' => 'TEST_FORM_1_EQUAL',
                    'values' => [
                        [
                            'value' => 'TEST_COMPANY_PROFILE_1_EQUALS',
                        ],
                    ],
                ],
                [
                    'match_type' => EMatchType::SMALLER_THAN->name,
                    'matches_name' => 'TEST_FORM_1_SMALLER_THAN',
                    'values' => [
                        [
                            'value' => '8',
                        ],
                    ],
                ],
                [
                    'match_type' => EMatchType::MULTIPLE_CHOOSE->name,
                    'matches_name' => 'TEST_FORM_1_MULTIPLE_CHOOSE',
                    'selected_options' => [
                        ['name' => 'OPTION_3'],
                        ['name' => 'OPTION_1'],
                    ],
                ],
            ],
        ],

        'TEST_COMPANY_PROFILE_2' => [
            'name' => 'TEST_COMPANY_PROFILE_2',
            'user' => 'testCompany',
            'is_professional' => 0,
            'matches_form_name' => 'TEST_FORM',
            'description' => 'test company profile 2',
            'matches' => [],
        ],

        'TEST_CANDIDATE_PROFILE_1' => [
            'name' => 'TEST_CANDIDATE_PROFILE_1',
            'user' => 'testCandidate',
            'is_professional' => 1,
            'matches_form_name' => 'TEST_FORM',
            'description' => 'test candidate profile 1',
            'matches' => [
                [
                    'match_type' => EMatchType::EQUAL->name,
                    'matches_name' => 'TEST_FORM_1_EQUAL',
                    'values' => [
                        [
                            'value' => 2,
                        ],
                    ],
                ],
                [
                    'match_type' => EMatchType::SMALLER_THAN->name,
                    'matches_name' => 'TEST_FORM_1_SMALLER_THAN',
                    'values' => [
                        [
                            'value' => 3,
                        ],
                    ],
                ],
                [
                    'match_type' => EMatchType::MULTIPLE_CHOOSE->name,
                    'matches_name' => 'TEST_FORM_1_MULTIPLE_CHOOSE',
                    'selected_options' => [
                        ['name' => 'OPTION_3'],
                        ['name' => 'OPTION_1'],
                    ],
                ],
            ],
        ],

        'TEST_CANDIDATE_PROFILE_2' => [
            'name' => 'TEST_CANDIDATE_PROFILE_2',
            'user' => 'testCandidate',
            'is_professional' => 1,
            'matches_form_name' => 'TEST_FORM',
            'description' => 'test candidate profile 2',
            'matches' => [
                [
                    'match_type' => EMatchType::EQUAL->name,
                    'matches_name' => 'TEST_FORM_1_EQUAL',
                    'values' => [
                        [
                            'value' => 'TEST_COMPANY_PROFILE_1_EQUALS',
                        ],
                    ],
                ],
                [
                    'match_type' => EMatchType::SMALLER_THAN->name,
                    'matches_name' => 'TEST_FORM_1_SMALLER_THAN',
                    'values' => [
                        [
                            'value' => 3,
                        ],
                    ],
                ],
                [
                    'match_type' => EMatchType::MULTIPLE_CHOOSE->name,
                    'matches_name' => 'TEST_FORM_1_MULTIPLE_CHOOSE',
                    'selected_options' => [
                        ['name' => 'OPTION_3'],
                        ['name' => 'OPTION_1'],
                    ],
                ],
            ],
        ],
    ];

    private MatchesFormService $matchesFormService;
    private MatchesService $matchesService;
    private MatchesOptionService $matchesOptionService;
    private MatchesProfileService $matchesProfileService;
    private MatchesValueService $matchesValueService;
    private MatchesOptionValuesService $matchesOptionValuesService;

    public function __construct()
    {
        $this->matchesOptionService = new MatchesOptionService(
            new MatchesOptionValuesService()
        );
        $this->matchesService = new MatchesService($this->matchesOptionService);
        $this->matchesFormService = new MatchesFormService($this->matchesService);
        $this->matchesValueService = new MatchesValueService();
        $this->matchesOptionValuesService = new MatchesOptionValuesService();
        $this->matchesProfileService = new MatchesProfileService(
            $this->matchesFormService,
            $this->matchesValueService,
            $this->matchesOptionValuesService,
        );
    }

    public function run(): void
    {
        $this->insertUsers();
        $this->insertForms();
        $this->insertProfiles();
    }

    private function insertUsers(): void
    {
        $users = User::all()->toArray();
        self::$users = array_combine(array_column($users, 'name'), $users);
    }


    private function insertForms(): void
    {
        foreach (self::$forms as &$form) {
            $this->matchesFormService->store($form);
        }
    }

    private function insertProfiles(): void
    {
        // insert profiles
        foreach (array_keys(self::$profiles) as $profileKey) {
            $profile = self::$profiles[$profileKey];
            $profile['matches_form_id'] = MatchesForm::where('name', $profile['matches_form_name'])->first()->id;
            $profile['user_id'] = User::where('name', $profile['user'])->first()->id;
            unset($profile['matches']); // first time we don't save values

            $newProfile = $this->matchesProfileService->store($profile);
            self::$profiles[$profileKey]['id'] = $newProfile['id'];
        }
        // insert profile values
        foreach (array_keys(self::$profiles) as $profileKey) {
            $profile = self::$profiles[$profileKey];
            $profile['matches'] = [];

            foreach (array_keys(self::$profiles[$profileKey]['matches']) as $matchKey) {
                $match = self::$profiles[$profileKey]['matches'][$matchKey];
                $matchObj = Matches::where('label', $match['matches_name'])->first();
                $matchId = $matchObj->id;

                $values = [];
                if (isset($match['values'])) {
                    foreach ($match['values'] as $value) {
                        $valueObj = MatchesValues::where('matches_profile_id', $profile['id'])
                            ->where('matches_id', $matchId)->first();
                        $values[] = [
                            'id' => $valueObj->id,
                            'value' => $value['value']
                        ];
                    }
                }

                $options = [];
                if (isset($match['selected_options'])) {
                    foreach ($match['selected_options'] as $option) {
                        $option = MatchesOptions::where('value', $option['name'])
                            ->first();

                        $options[] = [
                            'id' => $option->id,
                            'matches_id' => $matchId,
                            'matches_profile_id' => $profile['id'],
                            'value' => $option['name'],
                            'selected' => true,
                        ];
                    }
                }

                $profile['matches'][] =
                    [
                        'id' => $matchObj->id,
                        'match_type' => ($match['match_type']),
                        'matches_id' => $matchId,
                        'values' => $values,
                        'options' => $options
                    ];
            }
            $this->matchesProfileService->update($profile['id'], $profile);

        }
    }

}
