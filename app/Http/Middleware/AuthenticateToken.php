<?php

namespace App\Http\Middleware;

use App\Models\AuthToken;
use Closure;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateToken
{
    public function handle(Request $request, Closure $next): Response
    {
        $bearer = $request->bearerToken();

        if (! $bearer) {
            throw new AuthenticationException('Missing bearer token.');
        }

        $hash = hash('sha256', $bearer);

        /** @var AuthToken|null $accessToken */
        $accessToken = AuthToken::query()
            ->activeAccessToken($hash)
            ->with('user')
            ->first();

        if (! $accessToken || ! $accessToken->user) {
            throw new AuthenticationException('Invalid access token.');
        }

        $accessToken->markUsed();

        Auth::setUser($accessToken->user);
        $request->setUserResolver(fn () => $accessToken->user);

        return $next($request);
    }
}
