<?php

namespace App\Exceptions;

use Exception;

class AuthException extends ApiException
{
    // -------------------------------------------------------------------------
    // Named constructors — satu tempat untuk semua skenario error auth
    // -------------------------------------------------------------------------

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

    // public static function logoutFailed(): self
    // {
    //     return new self(
    //         message:    'Logout berhasil. Sesi Anda sudah berakhir.',
    //         statusCode: 204,
    //         errorCode:  'LOGOUT_FAILED',
    //     );
    // }

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

    // -------------------------------------------------------------------------
    // Response builder
    // -------------------------------------------------------------------------

}
