<?php

namespace Modules\Matches\Http\Requests\BaseApiRequest;

use Modules\Matches\Http\Requests\BaseApiRequest;

class MatchesOptionsRequest extends BaseApiRequest
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
            'matches_id' => 'required|' .
                'exists:Modules\Matches\Models\Matches,id',
            'value' => 'required|alpha_dash|max:255',
            'code' => 'required|alpha_dash|max:255',
        ];
    }
}
