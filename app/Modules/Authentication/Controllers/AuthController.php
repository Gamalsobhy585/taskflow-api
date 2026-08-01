<?php

namespace App\Modules\Authentication\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Traits\ResponseTrait;
use App\Modules\Authentication\DTOs\ChangePasswordData;
use App\Modules\Authentication\DTOs\LoginData;
use App\Modules\Authentication\DTOs\RegisterUserData;
use App\Modules\Authentication\Requests\LoginRequest;
use App\Modules\Authentication\Requests\RegisterRequest;
use App\Modules\Authentication\Requests\UpdatePasswordRequest;
use App\Modules\Authentication\Resources\UserResource;
use App\Modules\Authentication\Services\Interface\IAuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    use ResponseTrait;

    public function __construct(
        private readonly IAuthService $authService
    ) {
    }

    public function register(
        RegisterRequest $request
    ): JsonResponse {
        $data = RegisterUserData::fromArray(
            $request->validated()
        );

        $user = $this->authService->register($data);

        return $this->returnData(
            __('messages.register.success'),
            201,
            new UserResource($user)
        );
    }

    public function login(
        LoginRequest $request
    ): JsonResponse {
        $data = LoginData::fromArray(
            $request->validated()
        );

        $result = $this->authService->login($data);

        return $this->returnData(
            __('messages.login.success'),
            200,
            $result
        );
    }

    public function logout(
        Request $request
    ): JsonResponse {
        $user = $request->user();

        $this->authService->logout($user);

        return $this->success(
            __('messages.logout.success'),
            200
        );
    }

    public function renewPassword(
        UpdatePasswordRequest $request
    ): JsonResponse {
        $data = ChangePasswordData::fromArray([
            'email' => $request->user()->email,
            'old_password' => $request->validated('old_password'),
            'new_password' => $request->validated('new_password'),
        ]);

        $this->authService->updateOldPassword($data);

        return $this->success(
            __('messages.renew.success'),
            200
        );
    }

    public function getUserInfo(
        Request $request
    ): JsonResponse {
        $userResource = $this->authService->getUserInfo(
            $request->user()
        );

        return $this->returnData(
            __('messages.user.success'),
            200,
            $userResource
        );
    }
}