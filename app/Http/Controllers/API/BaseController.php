<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller as Controller;
use Illuminate\Http\JsonResponse;

class BaseController extends Controller
{
    public function sendResponse($result, $message): JsonResponse
    {
        $response = [
            'success' => true,
            'data' => $result,
            'message' => $message,
        ];
        return response()->json($response, 200);
    }

    public function jsonResponse(
        array $data,
        int   $status,
        array $meta = [],
        bool  $getContent = false,
    )
    {
        $responseSArr = [];
        $meta['status'] = $status;
        if (!empty($data)) {
            $meta['count'] = count($data);
            $responseSArr['data'] = $data;
        }
        if(isset($meta['data'])){
            unset($meta['data']);
        }
        $responseSArr['meta'] = $meta;

        $response = \response()->json(
            $responseSArr,
            $status
        );
        if ($getContent) {
            return $response->getContent();
        }
        return $response;
    }

    public function sendError($error, $errorMessages = [], $code = 404)
    {
        $response = [
            'success' => false,
            'message' => $error,
        ];


        if (!empty($errorMessages)) {
            $response['data'] = $errorMessages;
        }

        return response()->json($response, $code);
    }
}
