<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;

class UserException extends Exception
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
    // Named constructors — satu tempat untuk semua skenario error user
    // -------------------------------------------------------------------------

    public static function nikAlreadyExists(): self
    {
        return new self(
            message: 'NIK sudah terdaftar.',
            statusCode: 422,
            errorCode: 'NIK_ALREADY_EXISTS',
        );
    }



    

    public static function notFound(): self
    {
        return new self(
            message: 'User tidak ditemukan.',
            statusCode: 404,
            errorCode: 'USER_NOT_FOUND',
        );
    }

    public static function emailAlreadyExists(): self
    {
        return new self(
            message: 'Email sudah terdaftar.',
            statusCode: 422,
            errorCode: 'EMAIL_ALREADY_EXISTS',
        );
    }

    public static function createFailed(string $reason = ''): self
    {
        return new self(
            message: 'Gagal membuat user' . ($reason ? ': ' . $reason : '.'),
            statusCode: 500,
            errorCode: 'USER_CREATE_FAILED',
        );
    }

    public static function updateFailed(string $reason = ''): self
    {
        return new self(
            message: 'Gagal update user' . ($reason ? ': ' . $reason : '.'),
            statusCode: 500,
            errorCode: 'USER_UPDATE_FAILED',
        );
    }

    public static function creditNegative(): self
    {
        return new self(
            message: 'Credit score tidak boleh negatif.',
            statusCode: 422,
            errorCode: 'INVALID_CREDIT_SCORE',
        );
    }

    public static function updateCreditFailed(string $reason = ''): self
    {
        return new self(
            message: 'Gagal update credit' . ($reason ? ': ' . $reason : '.'),
            statusCode: 500,
            errorCode: 'UPDATE_CREDIT_FAILED',
        );
    }

    public static function hasRelations(): self
    {
        return new self(
            message: 'User tidak dapat dihapus karena masih memiliki data terkait.',
            statusCode: 422,
            errorCode: 'USER_HAS_RELATIONS',
        );
    }

    public static function deleteFailed(string $reason = ''): self
    {
        return new self(
            message: 'Gagal menghapus user' . ($reason ? ': ' . $reason : '.'),
            statusCode: 500,
            errorCode: 'USER_DELETE_FAILED',
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
