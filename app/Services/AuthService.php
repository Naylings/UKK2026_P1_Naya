<?php

namespace App\Services;

use App\Exceptions\AuthException;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

class AuthService
{
    public function login(array $credentials): array
    {
        
        $user = User::where('email', $credentials['email'])->first();

        if (! $user) {
            throw AuthException::invalidCredentials();
        }

        
        if (! Hash::check($credentials['password'], $user->password)) {
            throw AuthException::invalidCredentials();
        }

        
        $token = JWTAuth::fromUser($user);

        if (! $token) {
            throw AuthException::tokenGenerationFailed();
        }

        return $this->buildTokenPayload($token, $user);
    }

    public function logout(): void
    {
        
            JWTAuth::invalidate(JWTAuth::getToken());
        
        
        
        
    }

    public function refresh(): array
    {
        try {
            $newToken = JWTAuth::refresh(JWTAuth::getToken());
            $user     = JWTAuth::setToken($newToken)->toUser();
        } catch (\Throwable $e) {
            throw AuthException::tokenRefreshFailed();
        }

        return $this->buildTokenPayload($newToken, $user);
    }

    public function me(): array
    {
        $user = JWTAuth::parseToken()->authenticate();

        if (! $user) {
            throw AuthException::unauthenticated();
        }

        return [
            'token'      => null,
            'token_type' => null,
            'expires_in' => null,
            'user'       => $user,
        ];
    }

    
    
    

    private function buildTokenPayload(string $token, User $user): array
    {
        return [
            'token'      => $token,
            'token_type' => 'bearer',
            'expires_in' => config('jwt.ttl') * 60, 
            'user'       => $user,
        ];
    }
}
