<?php

namespace Modules\Matches\Http\Requests\BaseApiRequest;

use Modules\Matches\Http\Requests\BaseApiRequest;


class MatchesFormRequest extends BaseApiRequest
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
                'name' => 'required',
            ],
            // rules to insert
            'POST' => [
                'name' => 'required|unique:matches_form|max:255',
            ],
        };

        // Add rules for matches
        foreach ((new MatchesRequest())->rules() as $key => $rule) {
            // matches_form_id is not required when posting a form,
            // because the id is read from the form
            if ($key != 'matches_form_id') {
                $rules['matches.*.' . $key] = $rule;
            }
        }
        return $rules;
    }
}
