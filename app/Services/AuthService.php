<?php

namespace App\Services;

use App\Contracts\Services\AuthServiceInterface;
use App\Models\AuthToken;
use App\Models\User;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Contracts\Hashing\Hasher;
use Illuminate\Database\DatabaseManager;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class AuthService implements AuthServiceInterface
{
    public function __construct(
        private readonly Hasher $hasher,
        private readonly DatabaseManager $database,
    ) {
    }

    public function login(string $email, string $password): array
    {
        /** @var User|null $user */
        $user = User::query()->where('email', $email)->first();

        if (! $user || ! $this->hasher->check($password, $user->password)) {
            throw new AuthenticationException('Invalid credentials.');
        }

        return $this->database->transaction(function () use ($user) {
            $this->ensureEmployeeCode($user);
            $user->forceFill(['last_login_at' => now()])->save();

            $this->revokeExpiredTokens($user);

            $tokens = $this->issueTokenPair($user);

            return [
                'tokens' => $tokens,
                'employee' => $this->buildProfile($user),
            ];
        });
    }

    public function refresh(string $refreshToken): array
    {
        $hashed = $this->hashToken($refreshToken);

        /** @var AuthToken|null $token */
        $token = AuthToken::query()
            ->where('type', AuthToken::TYPE_REFRESH)
            ->where('token_hash', $hashed)
            ->where('revoked', false)
            ->first();

        if (! $token || $token->isExpired()) {
            throw new AuthenticationException('Invalid refresh token.');
        }

        return $this->database->transaction(function () use ($token) {
            $user = $token->user;

            if (! $user) {
                throw new AuthenticationException('Token user not found.');
            }

            $token->markUsed();
            $this->revokeTokenFamily($token);

            $tokens = $this->issueTokenPair($user);

            return [
                'tokens' => $tokens,
                'employee' => $this->buildProfile($user),
            ];
        });
    }

    public function logout(string $refreshToken): void
    {
        $hashed = $this->hashToken($refreshToken);

        /** @var AuthToken|null $token */
        $token = AuthToken::query()
            ->where('type', AuthToken::TYPE_REFRESH)
            ->where('token_hash', $hashed)
            ->where('revoked', false)
            ->first();

        if (! $token) {
            throw new AuthenticationException('Invalid refresh token.');
        }

        $this->database->transaction(function () use ($token) {
            $this->revokeTokenFamily($token);
        });
    }

    public function buildProfile(User $user): array
    {
        $roles = Arr::wrap($user->roles ?? []);

        return [
            'id' => $user->employee_code ?? sprintf('emp_%s', $user->getKey()),
            'email' => $user->email,
            'name' => $user->name,
            'roles' => array_values($roles),
            'lastLoginAt' => optional($user->last_login_at)->toIso8601String(),
            'avatarUrl' => $user->avatar_url,
        ];
    }

    private function issueTokenPair(User $user): array
    {
        $accessTtl = (int) config('auth.tokens.access.expires_in', 3600);
        $refreshTtl = (int) config('auth.tokens.refresh.expires_in', 60 * 60 * 24 * 7);

        $plainRefreshToken = $this->generateTokenString(120);
        $refresh = AuthToken::query()->create([
            'user_id' => $user->getKey(),
            'token_hash' => $this->hashToken($plainRefreshToken),
            'type' => AuthToken::TYPE_REFRESH,
            'expires_at' => now()->addSeconds($refreshTtl),
        ]);

        $plainAccessToken = $this->generateTokenString(64);
        AuthToken::query()->create([
            'user_id' => $user->getKey(),
            'token_hash' => $this->hashToken($plainAccessToken),
            'type' => AuthToken::TYPE_ACCESS,
            'refresh_token_id' => $refresh->getKey(),
            'expires_at' => now()->addSeconds($accessTtl),
        ]);

        return [
            'tokenType' => 'Bearer',
            'accessToken' => $plainAccessToken,
            'expiresIn' => $accessTtl,
            'refreshToken' => $plainRefreshToken,
            'refreshTokenExpiresIn' => $refreshTtl,
        ];
    }

    private function hashToken(string $plain): string
    {
        return hash('sha256', $plain);
    }

    private function generateTokenString(int $length): string
    {
        return Str::random($length);
    }

    private function ensureEmployeeCode(User $user): void
    {
        if ($user->employee_code) {
            return;
        }

        $user->forceFill([
            'employee_code' => sprintf('emp_%05d', $user->getKey()),
        ])->save();
    }

    private function revokeExpiredTokens(User $user): void
    {
        AuthToken::query()
            ->where('user_id', $user->getKey())
            ->where('revoked', false)
            ->whereNotNull('expires_at')
            ->where('expires_at', '<=', now())
            ->update(['revoked' => true]);
    }

    private function revokeTokenFamily(AuthToken $refreshToken): void
    {
        $refreshToken->refresh();
        $refreshToken->revoke();

        AuthToken::query()
            ->where('refresh_token_id', $refreshToken->getKey())
            ->update(['revoked' => true]);
    }
}
