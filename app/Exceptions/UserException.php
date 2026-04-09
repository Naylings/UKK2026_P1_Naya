<?php

namespace App\Exceptions;


class UserException extends ApiException
{

    // -------------------------------------------------------------------------
    // Named constructors — satu tempat untuk semua skenario error user
    // -------------------------------------------------------------------------

    public static function nikAlreadyExists(): self
    {
        return new self(
            'NIK sudah terdaftar.',
            422,
            'NIK_ALREADY_EXISTS',
        );
    }





    public static function notFound(): self
    {
        return new self(
            'User tidak ditemukan.',
            404,
            'USER_NOT_FOUND',
        );
    }

    public static function emailAlreadyExists(): self
    {
        return new self('Email sudah terdaftar.', 422, 'EMAIL_ALREADY_EXISTS');
    }

    public static function createFailed(string $reason = ''): self
    {
        return new self(
            'Gagal membuat user' . ($reason ? ': ' . $reason : '.'),
            500,
            'USER_CREATE_FAILED',
        );
    }

    public static function updateFailed(string $reason = ''): self
    {
        return new self(
            'Gagal update user' . ($reason ? ': ' . $reason : '.'),
            500,
            'USER_UPDATE_FAILED',
        );
    }

    public static function creditNegative(): self
    {
        return new self(
            'Credit score tidak boleh negatif.',
            422,
            'INVALID_CREDIT_SCORE',
        );
    }

    public static function updateCreditFailed(string $reason = ''): self
    {
        return new self(
            'Gagal update credit' . ($reason ? ': ' . $reason : '.'),
            500,
            'UPDATE_CREDIT_FAILED',
        );
    }

    public static function hasRelations(): self
    {
        return new self(
            'User tidak dapat dihapus karena masih memiliki data terkait.',
            422,
            'USER_HAS_RELATIONS',
        );
    }

    public static function deleteFailed(string $reason = ''): self
    {
        return new self(
            'Gagal menghapus user' . ($reason ? ': ' . $reason : '.'),
            500,
            'USER_DELETE_FAILED',
        );
    }

}
