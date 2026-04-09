<?php

namespace App\Services;

use App\Exceptions\AuthException;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

class AuthService
{
    /**
     * Proses login: validasi kredensial, cek status user, terbitkan token.
     *
     * @param  array{email: string, password: string}  $credentials
     * @return array{token: string, token_type: string, expires_in: int, user: User}
     *
     * @throws AuthException
     */
    public function login(array $credentials): array
    {
        // 1. Cek apakah user dengan email ini ada
        $user = User::where('email', $credentials['email'])->first();

        if (! $user) {
            throw AuthException::invalidCredentials();
        }

        // 2. Verifikasi password
        if (! Hash::check($credentials['password'], $user->password)) {
            throw AuthException::invalidCredentials();
        }

        // 3. Terbitkan JWT token
        $token = JWTAuth::fromUser($user);

        if (! $token) {
            throw AuthException::tokenGenerationFailed();
        }

        return $this->buildTokenPayload($token, $user);
    }

    /**
     * Proses logout: invalidasi token aktif di blacklist JWT.
     *
     * @throws AuthException
     */
    public function logout(): void
    {
        // try {
            JWTAuth::invalidate(JWTAuth::getToken());
        // } 
        // catch (\Throwable $e) {
        //     throw AuthException::logoutFailed();
        // }
    }

    /**
     * Refresh token: terbitkan token baru dari token lama yang masih valid.
     *
     * @return array{token: string, token_type: string, expires_in: int, user: User}
     *
     * @throws AuthException
     */
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

    /**
     * Ambil data user yang sedang login dari token aktif.
     *
     * @return array{token: null, token_type: null, expires_in: null, user: User}
     *
     * @throws AuthException
     */
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

    // -------------------------------------------------------------------------
    // Private helpers
    // -------------------------------------------------------------------------

    private function buildTokenPayload(string $token, User $user): array
    {
        return [
            'token'      => $token,
            'token_type' => 'bearer',
            'expires_in' => config('jwt.ttl') * 60, // detik
            'user'       => $user,
        ];
    }
}
