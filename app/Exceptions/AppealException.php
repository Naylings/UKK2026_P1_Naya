<?php

namespace App\Exceptions;

use App\Exceptions\ApiException;

class AppealException extends ApiException
{
    public static function createFailed(string $reason = ''): self
    {
        return new self(
            'Gagal membuat appeal' . ($reason ? ': ' . $reason : '.'),
            500,
            'APPEAL_CREATE_FAILED',
        );
    }

    public static function updateFailed(string $reason = ''): self
    {
        return new self(
            'Gagal update appeal' . ($reason ? ': ' . $reason : '.'),
            500,
            'APPEAL_UPDATE_FAILED',
        );
    }

    public static function notFound(): self
    {
        return new self(
            'Data appeal tidak ditemukan.',
            404,
            'APPEAL_NOT_FOUND',
        );
    }

    public static function fetchFailed(string $reason = ''): self
    {
        return new self(
            'Gagal mengambil data appeal' . ($reason ? ': ' . $reason : '.'),
            500,
            'APPEAL_FETCH_FAILED',
        );
    }

    public static function notPending(): self
    {
        return new self(
            'Hanya appeal dengan status pending yang dapat direview.',
            422,
            'APPEAL_NOT_PENDING',
        );
    }

    public static function alreadyReviewed(): self
    {
        return new self(
            'Appeal ini sudah direview.',
            409,
            'APPEAL_ALREADY_REVIEWED',
        );
    }

    public static function invalidStatusTransition(): self
    {
        return new self(
            'Status appeal tidak valid untuk perubahan ini.',
            422,
            'INVALID_STATUS_TRANSITION',
        );
    }

    public static function userNotAllowed(): self
    {
        return new self(
            'User tidak diizinkan melakukan aksi ini.',
            403,
            'USER_NOT_ALLOWED',
        );
    }

    public static function recentAppealExists(): self
    {
        return new self(
            'Anda sudah memiliki appeal yang masih pending.',
            422,
            'RECENT_APPEAL_EXISTS',
        );
    }
}
?>

