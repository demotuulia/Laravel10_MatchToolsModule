<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;
use Modules\Matches\Enums\EMatchRoles;
use Modules\Matches\Http\Requests\BaseApiRequest;

class RegisterRequest extends BaseApiRequest
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
        $roles = array_column(EMatchRoles::cases(), 'value');
        return [
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required',
            'c_password' => 'required|same:password',
            'role' => ['required',
                Rule::in($roles),
            ],
        ];
    }
}
