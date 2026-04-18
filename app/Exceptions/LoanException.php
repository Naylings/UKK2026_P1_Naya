<?php

namespace App\Exceptions;

use App\Exceptions\ApiException;

class LoanException extends ApiException
{
    public static function unitNotAvailable(): self
    {
        return new self(
            'Unit tidak tersedia untuk periode yang dipilih.',
            422,
            'UNIT_NOT_AVAILABLE',
        );
    }

    public static function invalidDate(): self
    {
        return new self(
            'Tanggal tidak valid.',
            422,
            'INVALID_DATE',
        );
    }

    public static function toolNotFound(): self
    {
        return new self(
            'Tool tidak ditemukan.',
            404,
            'TOOL_NOT_FOUND',
        );
    }

    public static function unitNotFound(): self
    {
        return new self(
            'Unit tidak ditemukan.',
            404,
            'UNIT_NOT_FOUND',
        );
    }

    public static function alreadyBorrowed(): self
    {
        return new self(
            'Unit sedang dipinjam.',
            409,
            'UNIT_ALREADY_BORROWED',
        );
    }

    public static function createFailed(string $reason = ''): self
    {
        return new self(
            'Gagal membuat peminjaman' . ($reason ? ': ' . $reason : '.'),
            500,
            'LOAN_CREATE_FAILED',
        );
    }

    public static function updateFailed(string $reason = ''): self
    {
        return new self(
            'Gagal update peminjaman' . ($reason ? ': ' . $reason : '.'),
            500,
            'LOAN_UPDATE_FAILED',
        );
    }

    public static function deleteFailed(string $reason = ''): self
    {
        return new self(
            'Gagal menghapus peminjaman' . ($reason ? ': ' . $reason : '.'),
            500,
            'LOAN_DELETE_FAILED',
        );
    }

    public static function notFound(): self
    {
        return new self(
            'Data peminjaman tidak ditemukan.',
            404,
            'LOAN_NOT_FOUND',
        );
    }

    public static function fetchFailed(string $reason = ''): self
    {
        return new self(
            'Gagal mengambil data peminjaman' . ($reason ? ': ' . $reason : '.'),
            500,
            'LOAN_FETCH_FAILED',
        );
    }

    public static function userHasActiveLoan(): self
    {
        return new self(
            'Anda masih memiliki peminjaman yang aktif atau pending.',
            422,
            'USER_HAS_ACTIVE_LOAN',
        );
    }


    public static function invalidStatusTransition(): self
    {
        return new self(
            'Status peminjaman tidak valid untuk perubahan ini.',
            422,
            'INVALID_STATUS_TRANSITION',
        );
    }

    public static function notPending(): self
    {
        return new self(
            'Hanya peminjaman dengan status pending yang dapat direview.',
            422,
            'LOAN_NOT_PENDING',
        );
    }


    public static function alreadyReviewed(): self
    {
        return new self(
            'Peminjaman ini sudah direview.',
            409,
            'LOAN_ALREADY_REVIEWED',
        );
    }

    public static function unitAlreadyAssigned(): self
    {
        return new self(
            'Unit sudah dialokasikan ke peminjaman lain.',
            409,
            'UNIT_ALREADY_ASSIGNED',
        );
    }


    public static function missingReviewer(): self
{
    return new self(
        'Reviewer (employee) tidak boleh kosong saat status berubah.',
        422,
        'MISSING_REVIEWER',
    );
}
}
