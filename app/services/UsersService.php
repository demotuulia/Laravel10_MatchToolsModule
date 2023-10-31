<?php

namespace App\Services;

use App\Models\User;
use Modules\Matches\Models\MatchesForm;
use Modules\Matches\Services\MatchesFormService;
use Modules\Matches\Services\MatchesOptionService;
use Modules\Matches\Services\MatchesOptionValuesService;
use Modules\Matches\Services\MatchesProfileService;
use Modules\Matches\Services\MatchesService;
use Modules\Matches\Services\MatchesValueService;

class UsersService
{
    public function register(array $data): array
    {
        $data['password'] = bcrypt($data['password']);
        /** @var User $user */
        $user = User::create($data);
        $response['token'] = $user->createToken('MyApp')->plainTextToken;
        $response['name'] = $user->name;
        $response['id'] = $user->id;
        $user->assignRole($data['role']);

        $matchesProfileService = new MatchesProfileService(
            new MatchesFormService(new MatchesService(
                    new MatchesOptionService(new MatchesOptionValuesService())
                )
            ),
            new MatchesValueService(),
            new MatchesOptionValuesService()
        );


        if ($data['role'] == 'professional') {
            // At the moment the front end  has only one form
            $formId = $data['form_id'] ?? \Config::get('matches')['formId'];
            $form = MatchesForm::where('id', $formId)->first();
            $name = $data['name'];
            if (isset($data['familyName'])) {
                $name .= ' ';
                $data['prefix'] = $data['prefix'] ?? '';
                $name .= $data['prefix']
                    ? $data['prefix'] . ' ' . $data['familyName']
                    : $data['familyName'];
            }

            $matchesProfileService->store(
                [
                    'name' => $name,
                    'user_id' => $user->id,
                    'is_professional' => 1,
                    'matches_form_id' => $form->id,
                ]
            );
        }


        return $response;
    }
}
