<?php

namespace Modules\Matches\Http\Requests\BaseApiRequest;

use Illuminate\Validation\Rule;
use Modules\Matches\Http\Requests\BaseApiRequest;

class MatchesProfileRequest extends BaseApiRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Todo:
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        $rules = match ($this->getMethod()) {
            // rules tp update
            'PUT' => [
                'name' => 'required|max:255',
            ],
            // rules to insert
            'POST' => [
                'name' => 'required',
                'user_id' => 'required|exists:Users,id',
                'matches_form_id' => 'required|exists:Modules\Matches\Models\MatchesForm,id',
                'is_professional' => [
                    'required',
                    Rule::in(0, 1)
                ],
            ],
        };

        // Add rules for value
        foreach ((new MatchesValuesRequest())->rules() as $key => $rule) {
            // matches_profile_id is not required when posting a profile,
            // because the id is read from the profile
            if ($key != 'matches_profile_id' ) {
                $rules['values.*.' . $key ] = $rule;
            }
        }

        return $rules;
    }
}
