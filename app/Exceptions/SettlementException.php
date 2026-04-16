<?php

namespace App\Exceptions;

class SettlementException extends ApiException
{
    // ─────────────────────────────────────────────
    // BASIC
    // ─────────────────────────────────────────────

    public static function notFound(): self
    {
        return new self(
            'Data pelanggaran tidak ditemukan.',
            404,
            'VIOLATION_NOT_FOUND'
        );
    }

    public static function settlementNotFound(): self
    {
        return new self(
            'Data pelunasan tidak ditemukan.',
            404,
            'SETTLEMENT_NOT_FOUND'
        );
    }

    // ─────────────────────────────────────────────
    // BUSINESS RULES
    // ─────────────────────────────────────────────

    public static function alreadySettled(): self
    {
        return new self(
            'Pelanggaran sudah diselesaikan sebelumnya.',
            422,
            'ALREADY_SETTLED'
        );
    }

    public static function violationNotActive(): self
    {
        return new self(
            'Pelanggaran sudah tidak aktif atau tidak valid.',
            422,
            'VIOLATION_NOT_ACTIVE'
        );
    }

    public static function invalidSettlement(): self
    {
        return new self(
            'Data pelunasan tidak valid.',
            422,
            'INVALID_SETTLEMENT'
        );
    }

    // ─────────────────────────────────────────────
    // AUTHORIZATION
    // ─────────────────────────────────────────────

    public static function notAllowed(): self
    {
        return new self(
            'Anda tidak memiliki izin untuk melakukan pelunasan.',
            403,
            'SETTLEMENT_NOT_ALLOWED'
        );
    }

    // ─────────────────────────────────────────────
    // SYSTEM ERROR
    // ─────────────────────────────────────────────

    public static function createFailed(string $reason = ''): self
    {
        return new self(
            'Gagal mencatat pelunasan' . ($reason ? ': ' . $reason : '.'),
            500,
            'SETTLEMENT_CREATE_FAILED'
        );
    }



    public static function violationNotFound(): self
    {
        return new self('Pelanggaran tidak ditemukan.', 404, 'VIOLATION_NOT_FOUND');
    }

}
