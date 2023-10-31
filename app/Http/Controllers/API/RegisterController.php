<?php

namespace App\Http\Controllers\API;

use App\Http\Requests\RegisterRequest;
use App\Services\UsersService;
use Illuminate\Http\Request;
use App\Http\Controllers\API\BaseController as BaseController;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Role;
use Validator;
use Illuminate\Http\JsonResponse;

class RegisterController extends BaseController
{
    public function register(RegisterRequest $request, UsersService $service)
    {
        $input = $request->all();
        $response = $service->register($input);
        return $this->sendResponse($response, 'User register successfully.');
    }

    public function login(Request $request)
    {
        if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {

            $user = Auth::user();
            $success['token'] = $user->createToken('MyApp')->plainTextToken;
            $success['name'] = $user->name;
            /**  @var Role $userRole */
            $userRole = $user->roles->first();
            $success['role'] = $userRole->name;
            $success['id'] = $user->id;
            return $this->sendResponse($success, 'User login successfully.');
        } else {
            return $this->sendError('Unauthorised.', ['error' => 'Unauthorised']);
        }
    }

    public function logout(Request $request): JsonResponse
    {
        $user = Auth::user();
        $user->tokens()->delete();
        return $this->sendResponse([], 'User logged out successfully.');
    }
}
