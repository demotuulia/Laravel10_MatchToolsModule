<?php

namespace Modules\Matches\Tests\Feature\Traits;

use App\Models\User;
use Illuminate\Support\Collection;
use Modules\Matches\Enums\EMatchType;
use Modules\Matches\Models\Matches;
use Modules\Matches\Models\MatchesForm;
use Modules\Matches\Models\MatchesProfile;
use Modules\Matches\Models\MatchesValues;
use Modules\Matches\Models\MatchType;
use Modules\Matches\Services\MatchesOptionService;
use Modules\Matches\Services\MatchesOptionValuesService;
use Modules\Matches\Services\MatchesService;
use Modules\Matches\Services\MatchTypes\RangeMatchesTypeService;

trait SearchUseCasesTrait
{
    private function getQuerySearchUseCases(): array
    {
        return [
            ////////////////////////////////////////////////////////////////////

            'EQUAL' => [
                'matches' => [
                    'SEARCH_TEST_FORM' => [
                        'label' => 'SEARCH_TEST_FORM',
                        'match_type' => EMatchType::EQUAL,
                    ],
                ],
                'values' => [
                    [
                        'matches_profile_id' => 'TEST_COMPANY_PROFILE_1',
                        'matches_id' => 'SEARCH_TEST_FORM',
                        'value' => 'MATCH OK',
                    ],
                    [
                        'matches_profile_id' => 'TEST_CANDIDATE_PROFILE_1',
                        'matches_id' => 'SEARCH_TEST_FORM',
                        'value' => 'MATCH NOT OK',
                    ],
                    [
                        'matches_profile_id' => 'TEST_CANDIDATE_PROFILE_2',
                        'matches_id' => 'SEARCH_TEST_FORM',
                        'value' => 'MATCH OK',
                    ],
                ],
                'expected' => [
                    'TEST_CANDIDATE_PROFILE_2',
                ],
                'expectedScores' => [
                    'TEST_CANDIDATE_PROFILE_2' => 100,
                ],
            ],

            ////////////////////////////////////////////////////////////////////

            'SMALLER_THAN_COMPANY' => [
                'matches' => [
                    'SMALLER_THAN_SearchTest' => [
                        'label' => 'SMALLER_THAN_SearchTest',
                        'match_type' => EMatchType::SMALLER_THAN,
                    ],
                ],
                'values' => [
                    [
                        'matches_profile_id' => 'TEST_COMPANY_PROFILE_1',
                        'matches_id' => 'SMALLER_THAN_SearchTest',
                        'value' => '100',
                    ],
                    [
                        'matches_profile_id' => 'TEST_CANDIDATE_PROFILE_1',
                        'matches_id' => 'SMALLER_THAN_SearchTest',
                        'value' => '10',
                    ],
                    [
                        'matches_profile_id' => 'TEST_CANDIDATE_PROFILE_2',
                        'matches_id' => 'SMALLER_THAN_SearchTest',
                        'value' => '500',
                    ],
                ],
                'expected' => [
                    'TEST_CANDIDATE_PROFILE_1',
                ],
                'expectedScores' => [
                    'TEST_CANDIDATE_PROFILE_1' => 100,
                ],
            ],

            ////////////////////////////////////////////////////////////////////
            // Note in case smaller than professional, the value of the
            // professional must be BIGGER than the value of the company
            'SMALLER_THAN_PROFESSIONAL' => [
                'matches' => [
                    'SMALLER_THAN_THAN_PROFESSIONAL_SearchTest' => [
                        'label' => 'SMALLER_THAN_PROFESSIONAL_SearchTest',
                        'match_type' => EMatchType::SMALLER_THAN,
                    ],
                ],
                'values' => [
                    [
                        'matches_profile_id' => 'TEST_CANDIDATE_PROFILE_1',
                        'matches_id' => 'SMALLER_THAN_THAN_PROFESSIONAL_SearchTest',
                        'value' => '100',
                    ],
                    [
                        'matches_profile_id' => 'TEST_COMPANY_PROFILE_1',
                        'matches_id' => 'SMALLER_THAN_THAN_PROFESSIONAL_SearchTest',
                        'value' => '10',
                    ],
                    [
                        'matches_profile_id' => 'TEST_COMPANY_PROFILE_2',
                        'matches_id' => 'SMALLER_THAN_THAN_PROFESSIONAL_SearchTest',
                        'value' => '500',
                    ],
                ],
                'expected' => [
                    'TEST_COMPANY_PROFILE_2',
                ],
                'expectedScores' => [
                    'TEST_COMPANY_PROFILE_2' => 100,
                ],
            ],

            ////////////////////////////////////////////////////////////////////
            // Note in case smaller than professional, the value of the
            // professional must be SMALLER than the value of the company
            'BIGGER_THAN_PROFESSIONAL' => [
                'matches' => [
                    'BIGGER_THAN_THAN_PROFESSIONAL_SearchTest' => [
                        'label' => 'BIGGER_THAN_PROFESSIONAL_SearchTest',
                        'match_type' => EMatchType::BIGGER_THAN,
                    ],
                ],
                'values' => [
                    [
                        'matches_profile_id' => 'TEST_CANDIDATE_PROFILE_1',
                        'matches_id' => 'BIGGER_THAN_THAN_PROFESSIONAL_SearchTest',
                        'value' => '100',
                    ],
                    [
                        'matches_profile_id' => 'TEST_COMPANY_PROFILE_1',
                        'matches_id' => 'BIGGER_THAN_THAN_PROFESSIONAL_SearchTest',
                        'value' => '10',
                    ],
                    [
                        'matches_profile_id' => 'TEST_COMPANY_PROFILE_2',
                        'matches_id' => 'BIGGER_THAN_THAN_PROFESSIONAL_SearchTest',
                        'value' => '500',
                    ],
                ],
                'expected' => [
                    'TEST_COMPANY_PROFILE_1',
                ],
                'expectedScores' => [
                    'TEST_COMPANY_PROFILE_1' => 100,
                ],
            ],
            ////////////////////////////////////////////////////////////////////

            'BIGGER_THAN' => [
                'matches' => [
                    'BIGGER_THAN_SearchTest' => [
                        'label' => 'BIGGER_THAN_SearchTest',
                        'match_type' => EMatchType::BIGGER_THAN,
                    ],
                ],
                'values' => [
                    [
                        'matches_profile_id' => 'TEST_COMPANY_PROFILE_1',
                        'matches_id' => 'BIGGER_THAN_SearchTest',
                        'value' => '100',
                    ],
                    [
                        'matches_profile_id' => 'TEST_CANDIDATE_PROFILE_1',
                        'matches_id' => 'BIGGER_THAN_SearchTest',
                        'value' => '10',
                    ],
                    [
                        'matches_profile_id' => 'TEST_CANDIDATE_PROFILE_2',
                        'matches_id' => 'BIGGER_THAN_SearchTest',
                        'value' => '500',
                    ],
                ],
                'expected' => [
                    'TEST_CANDIDATE_PROFILE_2',
                ],
                'expectedScores' => [
                    'TEST_CANDIDATE_PROFILE_2' => 100,
                ],
            ],
            ////////////////////////////////////////////////////////////////////

            'RADIO_BUTTON' => [
                'matches' => [
                    'RADIO_BUTTON_SearchTest' => [
                        'label' => 'RADIO_BUTTON_SearchTest',
                        'match_type' => EMatchType::RADIO_BUTTON,
                    ],
                ],
                'values' => [
                    [
                        'matches_profile_id' => 'TEST_COMPANY_PROFILE_1',
                        'matches_id' => 'RADIO_BUTTON_SearchTest',
                        'value' => '1',
                    ],
                    [
                        'matches_profile_id' => 'TEST_CANDIDATE_PROFILE_1',
                        'matches_id' => 'RADIO_BUTTON_SearchTest',
                        'value' => '1',
                    ],
                    [
                        'matches_profile_id' => 'TEST_CANDIDATE_PROFILE_2',
                        'matches_id' => 'RADIO_BUTTON_SearchTest',
                        'value' => '1',
                    ],
                ],
                'expected' => [
                    'TEST_CANDIDATE_PROFILE_1',
                    'TEST_CANDIDATE_PROFILE_2',
                ],
                'expectedScores' => [
                    'TEST_CANDIDATE_PROFILE_1' => 100,
                    'TEST_CANDIDATE_PROFILE_2' => 100,
                ],
            ],

            ////////////////////////////////////////////////////////////////////

            'RADIO_BUTTON_AND_EQUALS' => [
                'matches' => [
                    'RADIO_BUTTON_SearchTest_2' => [
                        'label' => 'RADIO_BUTTON_SearchTest_2',
                        'match_type' => EMatchType::RADIO_BUTTON,
                    ],
                    'SEARCH_TEST_FORM_2' => [
                        'label' => 'SEARCH_TEST_FORM_2',
                        'match_type' => EMatchType::EQUAL,
                    ],
                ],
                'values' => [
                    [
                        'matches_profile_id' => 'TEST_COMPANY_PROFILE_1',
                        'matches_id' => 'SEARCH_TEST_FORM_2',
                        'value' => 'MATCH OK',
                    ],
                    [
                        'matches_profile_id' => 'TEST_CANDIDATE_PROFILE_1',
                        'matches_id' => 'SEARCH_TEST_FORM_2',
                        'value' => 'MATCH NOT OK',
                    ],
                    [
                        'matches_profile_id' => 'TEST_CANDIDATE_PROFILE_2',
                        'matches_id' => 'SEARCH_TEST_FORM_2',
                        'value' => 'MATCH OK',
                    ],

                    [
                        'matches_profile_id' => 'TEST_COMPANY_PROFILE_1',
                        'matches_id' => 'RADIO_BUTTON_SearchTest_2',
                        'value' => '1',
                    ],
                    [
                        'matches_profile_id' => 'TEST_CANDIDATE_PROFILE_1',
                        'matches_id' => 'RADIO_BUTTON_SearchTest_2',
                        'value' => '1',
                    ],
                    [
                        'matches_profile_id' => 'TEST_CANDIDATE_PROFILE_2',
                        'matches_id' => 'RADIO_BUTTON_SearchTest_2',
                        'value' => '1',
                    ],
                ],
                'expected' => [
                    'TEST_CANDIDATE_PROFILE_2',
                ],
                'expectedScores' => [
                    'TEST_CANDIDATE_PROFILE_2' => 100,
                    'TEST_CANDIDATE_PROFILE_1' => 50,
                ],
            ],
            ////////////////////////////////////////////////////////////////////
            'RANGE' => [
                'matches' => [
                    'RANGE_SearchTest' => [
                        'label' => 'RANGE_SearchTest',
                        'match_type' => EMatchType::RANGE,
                    ],
                ],
                'values' => [
                    [
                        'matches_profile_id' => 'TEST_COMPANY_PROFILE_1',
                        'matches_id' => 'RANGE_SearchTest',
                        'value' => '100',
                        'order' => RangeMatchesTypeService::LOVER_BOUND_VALUE_INDEX,
                    ],
                    [
                        'matches_profile_id' => 'TEST_COMPANY_PROFILE_1',
                        'matches_id' => 'RANGE_SearchTest',
                        'value' => '200',
                        'order' => RangeMatchesTypeService::UPPER_BOUND_VALUE_INDEX,
                    ],

                    [
                        'matches_profile_id' => 'TEST_CANDIDATE_PROFILE_1',
                        'matches_id' => 'RANGE_SearchTest',
                        'value' => '10',
                        'order' => RangeMatchesTypeService::LOVER_BOUND_VALUE_INDEX,
                    ],
                    [
                        'matches_profile_id' => 'TEST_CANDIDATE_PROFILE_1',
                        'matches_id' => 'RANGE_SearchTest',
                        'value' => '150',
                        'order' => RangeMatchesTypeService::UPPER_BOUND_VALUE_INDEX,
                    ],


                    [
                        'matches_profile_id' => 'TEST_CANDIDATE_PROFILE_2',
                        'matches_id' => 'RANGE_SearchTest',
                        'value' => '190',
                        'order' => RangeMatchesTypeService::LOVER_BOUND_VALUE_INDEX,
                    ],
                    [
                        'matches_profile_id' => 'TEST_CANDIDATE_PROFILE_2',
                        'matches_id' => 'RANGE_SearchTest',
                        'value' => '180',
                        'order' => RangeMatchesTypeService::UPPER_BOUND_VALUE_INDEX,
                    ],
                ],
                'expected' => [
                    'TEST_CANDIDATE_PROFILE_2',
                ],
                'expectedScores' => [
                    'TEST_CANDIDATE_PROFILE_2' => 100,
                ],
            ],

            ////////////////////////////////////////////////////////////////////
            'MULTIPLE_CHOOSE' => [
                'matches' => [
                    'MULTIPLE_CHOOSE_SearchTest' => [
                        'label' => 'MULTIPLE_CHOOSE_SearchTest',
                        'match_type' => EMatchType::MULTIPLE_CHOOSE,
                    ],
                ],
                'values' => [
                    [
                        'matches_profile_id' => 'TEST_COMPANY_PROFILE_1',
                        'matches_id' => 'MULTIPLE_CHOOSE_SearchTest',
                        'value' => 'BLUE,RED',
                    ],
                    [
                        'matches_profile_id' => 'TEST_CANDIDATE_PROFILE_1',
                        'matches_id' => 'MULTIPLE_CHOOSE_SearchTest',
                        'value' => 'RED',
                    ],

                    [
                        'matches_profile_id' => 'TEST_CANDIDATE_PROFILE_2',
                        'matches_id' => 'MULTIPLE_CHOOSE_SearchTest',
                        'value' => 'BLUE,RED',
                    ],
                ],
                'expected' => [
                    'TEST_CANDIDATE_PROFILE_2',
                ],
                'expectedScores' => [
                    'TEST_CANDIDATE_PROFILE_2' => 100,
                ],
            ],

            ////////////////////////////////////////////////////////////////////
            'MENU' => [
                'matches' => [
                    'MENU_SearchTest' => [
                        'label' => 'MENU_SearchTest',
                        'match_type' => EMatchType::MENU,
                    ],
                ],
                'values' => [
                    [
                        'matches_profile_id' => 'TEST_COMPANY_PROFILE_1',
                        'matches_id' => 'MENU_SearchTest',
                        'value' => '1',
                    ],
                    [
                        'matches_profile_id' => 'TEST_CANDIDATE_PROFILE_1',
                        'matches_id' => 'MENU_SearchTest',
                        'value' => '1',
                    ],

                    [
                        'matches_profile_id' => 'TEST_CANDIDATE_PROFILE_2',
                        'matches_id' => 'MENU_SearchTest',
                        'value' => '2',
                    ],
                ],
                'expected' => [
                    'TEST_CANDIDATE_PROFILE_1',
                ],
                'expectedScores' => [
                    'TEST_CANDIDATE_PROFILE_1' => 100,
                ],
            ],

            ////////////////////////////////////////////////////////////////////

            'DATE_FROM_COMPANY' => [
                'matches' => [
                    'DATE_FROM_SearchTest' => [
                        'label' => 'DATE_FROM_SearchTest',
                        'match_type' => EMatchType::DATE_FROM,
                    ],
                ],
                'values' => [
                    [
                        'matches_profile_id' => 'TEST_COMPANY_PROFILE_1',
                        'matches_id' => 'DATE_FROM_SearchTest',
                        'value' => '100',
                    ],
                    [
                        'matches_profile_id' => 'TEST_CANDIDATE_PROFILE_1',
                        'matches_id' => 'DATE_FROM_SearchTest',
                        'value' => '10',
                    ],
                    [
                        'matches_profile_id' => 'TEST_CANDIDATE_PROFILE_2',
                        'matches_id' => 'DATE_FROM_SearchTest',
                        'value' => '500',
                    ],
                ],
                'expected' => [
                    'TEST_CANDIDATE_PROFILE_2',
                ],
                'expectedScores' => [
                    'TEST_CANDIDATE_PROFILE_2' => 100,
                ],
            ],


            ////////////////////////////////////////////////////////////////////
            // Note in case smaller than professional, the date of the
            // professional must be SMALLER than the date of the company
            'DATE_FROM_PROFESSIONAL' => [
                'matches' => [
                    'DATE_FROM_SearchTest' => [
                        'label' => 'DATE_FROM_SearchTest',
                        'match_type' => EMatchType::DATE_FROM,
                    ],
                ],
                'values' => [
                    [
                        'matches_profile_id' => 'TEST_CANDIDATE_PROFILE_1',
                        'matches_id' => 'DATE_FROM_SearchTest',
                        'value' => '100',
                    ],
                    [
                        'matches_profile_id' => 'TEST_COMPANY_PROFILE_1',
                        'matches_id' => 'DATE_FROM_SearchTest',
                        'value' => '10',
                    ],
                    [
                        'matches_profile_id' => 'TEST_COMPANY_PROFILE_2',
                        'matches_id' => 'DATE_FROM_SearchTest',
                        'value' => '500',
                    ],
                ],
                'expected' => [
                    'TEST_COMPANY_PROFILE_1',
                ],
                'expectedScores' => [
                    'TEST_COMPANY_PROFILE_1' => 100,
                ],
            ],
        ];
    }

    private function prepareQuerySearchUseCase(
        array $matches,
        array $values,
    ): array
    {
        $matchesService = new MatchesService(
            new MatchesOptionService(new MatchesOptionValuesService())
        );
        MatchesValues::truncate();

        // Insert a new form
        $form = new MatchesForm();
        $form->name = 'TEST_SEARCH';
        $form->save();
        /** @var int $formId */
        $formId = $form->id;

        // Insert the matches to the new form
        foreach (array_keys($matches) as $key) {
            $insertedMatch = $matchesService->store(
                [
                    'label' => $matches[$key]['label'],
                    'match_type' => $matches[$key]['match_type']->name,
                    'matches_form_id' => $formId
                ],
            );
            $matches[$key]['id'] = $insertedMatch->first()->id;
        }

        //Insert values
        foreach (array_keys($values) as $key) {
            $valueToInsert = new MatchesValues();
            $value = $values[$key];
            $valueToInsert->matches_profile_id = MatchesProfile::where(
                'name', $value['matches_profile_id']
            )->first()->id;
            $valueToInsert->matches_id = $matches[$value['matches_id']]['id'];
            $valueToInsert->value = $value['value'];
            $valueToInsert->order = $value['order'] ?? null;
            $valueToInsert->save();
            $values[$key]['id'] = $valueToInsert->id;
        }

        // We use the first values profile to search matches
        /** @var MatchesProfile $profile */
        $profile = MatchesProfile::where(
            'name', current($values)['matches_profile_id']
        )->first();

        return [
            'form' => $form,
            'profile' => $profile,
        ];
    }

    /**
     * Create following
     * . form by name PAGINATION_TEST_FORM with EQUAL match  'PAGINATION_TEST'
     * . 50 candidate profiles with name 'PROFILE_', with PAGINATION_TEST'  = 233
     *  .50 candidate profiles with name 'PROFILE_X' with PAGINATION_TEST'  = 233
     *   1 company profile with name 'SEARCHINGPROFILE' with PAGINATION_TEST'  = 233
     */
    public function preparePaginationTest(): array
    {
        $matchesValues = MatchesValues::all();
        MatchesValues::destroy($matchesValues->pluck('id'));
        $matches = Matches::all();
        Matches::destroy($matches->pluck('id'));
        $matchesProfiles = MatchesProfile::all();
        MatchesProfile::destroy($matchesProfiles->pluck('id'));

        $users = User::all();
        $userId = $users->first()->id;

        $form = new MatchesForm();
        $form->name = 'PAGINATION_TEST_FORM';
        $form->save();
        $matchesService = new MatchesService(
            new MatchesOptionService(new MatchesOptionValuesService())
        );
        $insertedMatch = $matchesService->store(
            [
                'label' => 'whatever' . uniqid(),
                'match_type' => EMatchType::EQUAL->name,
                'matches_form_id' => $form->id
            ],
        );
        $matchId = $insertedMatch->first()->id;


        $value = '233';
        // Create profiles to search for professional)
        foreach (['PROFILE_', 'X_PROFILE_'] as $profileNameBase) {
            for ($i = 0; $i < 50; $i++) {
                $profile = new MatchesProfile();
                $profile->name = $profileNameBase . $i;
                $profile->user_id = $userId;
                $profile->is_professional = MatchesProfile::$professional;
                $profile->save();

                $profileId = $profile->id;
                $matchesValue = new MatchesValues();
                $matchesValue->matches_profile_id = $profileId;
                $matchesValue->matches_id = $matchId;
                $matchesValue->value = $value;
                $matchesValue->save();
            }
        }

        // Create searching company
        $searchingProfile = new MatchesProfile();
        $searchingProfile->name = 'SEARCHINGPROFILE';
        $searchingProfile->user_id = $userId;
        $searchingProfile->is_professional = MatchesProfile::$company;
        $searchingProfile->save();

        $profileId = $searchingProfile->id;
        $matchesValue = new MatchesValues();
        $matchesValue->matches_profile_id = $profileId;
        $matchesValue->matches_id = $matchId;
        $matchesValue->value = $value;
        $matchesValue->save();

        return [
            'form' => $form,
            'profile' => $searchingProfile
        ];
    }

    public function paginationUseCases(): array
    {
        return [
            'FIRST_PAGE' =>
                [
                    'page' => 1,
                    'pageSize' => 3,
                    'expected' => [
                        'profiles' => [
                            'PROFILE_0',
                            'PROFILE_1',
                            'PROFILE_2'
                        ],
                        'count' => 3,
                        'totalCount' => 100,
                        'NumberOfPages' => 34,
                        'page' => 1,
                        'pageSize' => 3,
                    ]
                ],
            'SECOND_PAGE' =>
                [
                    'page' => 2,
                    'pageSize' => 3,
                    'expected' => [
                        'profiles' => [
                            'PROFILE_3',
                            'PROFILE_4',
                            'PROFILE_5'
                        ],
                        'count' => 3,
                        'totalCount' => 100,
                        'NumberOfPages' => 34,
                        'page' => 2,
                        'pageSize' => 3,
                    ]
                ],
            'LAST_PAGE' =>
                [
                    'page' => 34,
                    'pageSize' => 3,
                    'expected' => [
                        'profiles' => [
                            'X_PROFILE_49'
                        ],
                        'count' => 1,
                        'totalCount' => 100,
                        'NumberOfPages' => 34,
                        'page' => 34,
                        'pageSize' => 3,
                    ]
                ],
        ];
    }

    public function assertPagination(array $matches, array $expected, $caseName): void
    {
        $caseName = 'CASE: ' . $caseName;
        $profileIds = $matches['data']->pluck('id')->toArray();
        /** @var Collection $profileIds */
        $profiles = MatchesProfile::whereIn('id', $profileIds)
            ->get()
            ->all();
        $profileNames = array_column($profiles, 'name');

        $this->assertEquals(
            $expected['profiles'],
            $profileNames,
            $caseName,
        );
        $this->assertEquals($expected['totalCount'], $matches['total_count'], $caseName);
        $this->assertEquals($expected['count'], $matches['count'], $caseName);
        $this->assertEquals($expected['NumberOfPages'], $matches['number_of_pages'], $caseName);
        $this->assertEquals($expected['page'], $matches['page'], $caseName);
        $this->assertEquals($expected['pageSize'], $matches['page_size'], $caseName);
    }
}
