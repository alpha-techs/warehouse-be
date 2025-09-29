<?php

namespace App\Http\Controllers;

use App\Contracts\Services\AuthServiceInterface;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\LogoutRequest;
use App\Http\Requests\Auth\RefreshTokenRequest;
use App\Models\User;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class AuthController extends Controller
{
    public function __construct(
        private readonly AuthServiceInterface $authService,
    ) {
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $data = $this->authService->login(
            $request->validated('email'),
            $request->validated('password'),
        );

        return response()->json($data);
    }

    public function refresh(RefreshTokenRequest $request): JsonResponse
    {
        $data = $this->authService->refresh($request->validated('refreshToken'));

        return response()->json($data);
    }

    public function logout(LogoutRequest $request): Response
    {
        $this->authService->logout($request->validated('refreshToken'));

        return response()->noContent();
    }

    public function profile(Request $request): JsonResponse
    {
        $user = $request->user();

        if (! $user instanceof User) {
            throw new AuthenticationException('Unauthenticated.');
        }

        return response()->json([
            'employee' => $this->authService->buildProfile($user),
        ]);
    }
}
