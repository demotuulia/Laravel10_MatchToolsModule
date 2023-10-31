<?php

namespace Modules\Matches\Http\Requests;

use Illuminate\Http\Response;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;

class BaseApiRequest extends FormRequest
{

    /**
     * @throw HttpResponseException
     */
    public function failedValidation(Validator $validator)
    {
        // TODO:
        // How to set http  'statusCode' => 400 bad request?
        throw new HttpResponseException(
            response()->json([
                'meta' => [
                    'status' => Response::HTTP_BAD_REQUEST,
                ],
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors(),
            ]),
        );

    }
}
