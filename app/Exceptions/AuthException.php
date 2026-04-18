<?php

namespace App\Exceptions;

use Exception;

class AuthException extends ApiException
{
   
    
    public static function invalidCredentials(): self
    {
        return new self(
            'Email atau password salah.',
            401,
            'INVALID_CREDENTIALS',
        );
    }

    public static function tokenGenerationFailed(): self
    {
        return new self(
            'Gagal membuat token. Silakan coba lagi.',
            500,
            'TOKEN_GENERATION_FAILED',
        );
    }

    

    public static function tokenRefreshFailed(): self
    {
        return new self(
            'Gagal memperbarui token. Silakan login ulang.',
            401,
            'TOKEN_REFRESH_FAILED',
        );
    }

    public static function unauthenticated(): self
    {
        return new self(
            'Sesi tidak ditemukan. Silakan login ulang.',
            401,
            'UNAUTHENTICATED',
        );
    }

    
}
