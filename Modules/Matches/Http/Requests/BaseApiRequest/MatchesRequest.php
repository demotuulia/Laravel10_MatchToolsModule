<?php

namespace Modules\Matches\Http\Requests\BaseApiRequest;

use Illuminate\Validation\Rule;
use Modules\Matches\Enums\EMatchType;
use Modules\Matches\Http\Requests\BaseApiRequest;

class MatchesRequest extends BaseApiRequest
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
            'label' => 'required',
            'matches_form_id' => 'exclude_if:id,required|' .  // required if no id (inserting)
                'exists:Modules\Matches\Models\MatchesForm,id',
            'match_type' => [
                'exclude_if:id,required', // required if no id (inserting)
                Rule::in(array_column(EMatchType::cases(), 'name'))
            ],
            'ordering' => 'numeric',
        ];
    }
}
