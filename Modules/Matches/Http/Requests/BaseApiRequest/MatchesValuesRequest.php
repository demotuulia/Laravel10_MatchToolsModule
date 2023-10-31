<?php

namespace Modules\Matches\Http\Requests\BaseApiRequest;

use Illuminate\Validation\Rule;
use Modules\Matches\Enums\EMatchType;
use Modules\Matches\Http\Requests\BaseApiRequest;

class MatchesValuesRequest extends BaseApiRequest
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
        return [
            'value' => 'max:255',

            'id' => 'exclude_if:matches_id,required|' .
                'exclude_if:option_values,exists:Modules\Matches\Models\MatchesValues,id',

            'matches_id' => 'exclude_if:id,required|' .
                'exists:Modules\Matches\Models\Matches,id',

            'matches_profile_id' => 'required|' .
                'exists:Modules\Matches\Models\MatchesProfile,id',
        ];
    }
}
