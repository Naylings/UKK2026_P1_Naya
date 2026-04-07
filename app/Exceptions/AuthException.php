<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;

class AuthException extends Exception
{
    private int $statusCode;
    private string $errorCode;

    private function __construct(string $message, int $statusCode, string $errorCode)
    {
        parent::__construct($message);
        $this->statusCode = $statusCode;
        $this->errorCode  = $errorCode;
    }

    // -------------------------------------------------------------------------
    // Named constructors — satu tempat untuk semua skenario error auth
    // -------------------------------------------------------------------------

    public static function invalidCredentials(): self
    {
        return new self(
            message:    'Email atau password salah.',
            statusCode: 401,
            errorCode:  'INVALID_CREDENTIALS',
        );
    }

    public static function tokenGenerationFailed(): self
    {
        return new self(
            message:    'Gagal membuat token. Silakan coba lagi.',
            statusCode: 500,
            errorCode:  'TOKEN_GENERATION_FAILED',
        );
    }

    public static function logoutFailed(): self
    {
        return new self(
            message:    'Gagal melakukan logout. Token mungkin sudah tidak valid.',
            statusCode: 400,
            errorCode:  'LOGOUT_FAILED',
        );
    }

    public static function tokenRefreshFailed(): self
    {
        return new self(
            message:    'Gagal memperbarui token. Silakan login ulang.',
            statusCode: 401,
            errorCode:  'TOKEN_REFRESH_FAILED',
        );
    }

    public static function unauthenticated(): self
    {
        return new self(
            message:    'Sesi tidak ditemukan. Silakan login ulang.',
            statusCode: 401,
            errorCode:  'UNAUTHENTICATED',
        );
    }

    // -------------------------------------------------------------------------
    // Response builder
    // -------------------------------------------------------------------------

    public function render(): JsonResponse
    {
        return response()->json([
            'message' => $this->getMessage(),
            'error'   => $this->errorCode,
        ], $this->statusCode);
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }
}
