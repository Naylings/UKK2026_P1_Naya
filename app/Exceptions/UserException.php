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
    // Named constructors — satu tempat untuk semua skenario error auth
    // -------------------------------------------------------------------------

    public static function notFound(): self
    {
        return new self(
            message:    'User tidak ditemukan.',
            statusCode: 404,
            errorCode:  'USER_NOT_FOUND',
        );
    }

    public static function emailAlreadyExists(): self
    {
        return new self(
            message:    'Email sudah digunakan oleh user lain.',
            statusCode: 422,
            errorCode:  'EMAIL_ALREADY_EXISTS',
        );
    }

    public static function detailNotFound(): self
    {
        return new self(
            message:    'Detail user tidak ditemukan.',
            statusCode: 404,
            errorCode:  'DETAIL_NOT_FOUND',
        );
    }

    public static function invalidOperation(): self
    {
        return new self(
            message:    'Operasi tidak valid.',
            statusCode: 400,
            errorCode:  'INVALID_OPERATION',
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
