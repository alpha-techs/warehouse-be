<?php

namespace App\Contracts\Services;

use App\Models\User;

interface AuthServiceInterface
{
    public function login(string $email, string $password): array;

    public function refresh(string $refreshToken): array;

    public function logout(string $refreshToken): void;

    public function buildProfile(User $user): array;
}
