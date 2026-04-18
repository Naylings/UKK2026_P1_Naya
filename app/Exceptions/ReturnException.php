<?php

namespace App\Exceptions;

use App\Exceptions\ApiException;

class ReturnException extends ApiException
{
    
    public static function notApproved(): self
    {
        return new self(
            'Peminjaman belum disetujui, tidak dapat dikembalikan.',
            422,
            'LOAN_NOT_APPROVED',
        );
    }

    public static function notFound(): self
    {
        return new self(
            'Peminjaman tidak ditemukan.',
            404,
            'LOAN_NOT_FOUND',
        );
    }

    public static function alreadyReturned(): self
    {
        return new self(
            'Peminjaman ini sudah dikembalikan.',
            409,
            'LOAN_ALREADY_RETURNED',
        );
    }

    public static function returnTooEarly(): self
    {
        return new self(
            'Alat belum bisa dikembalikan sebelum tanggal peminjaman dimulai.',
            422,
            'RETURN_TOO_EARLY',
        );
    }

    public static function returnLate(): self
    {
        return new self(
            'Pengembalian melewati batas waktu peminjaman.',
            422,
            'RETURN_LATE',
        );
    }

    public static function invalidReturn(): self
    {
        return new self(
            'Data pengembalian tidak valid.',
            422,
            'INVALID_RETURN',
        );
    }

    public static function returnFailed(string $reason = ''): self
    {
        return new self(
            'Gagal memproses pengembalian' . ($reason ? ': ' . $reason : '.'),
            500,
            'RETURN_FAILED',
        );
    }

    public static function returnNotAllowed(): self
    {
        return new self(
            'Peminjaman ini tidak dapat dikembalikan pada status saat ini.',
            422,
            'RETURN_NOT_ALLOWED',
        );
    }
    
    public static function invalidProofUpload(string $reason = ''): self
    {
        return new self(
            'Upload bukti tidak valid' . ($reason ? ': ' . $reason : '.'),
            422,
            'INVALID_PROOF_UPLOAD',
        );
    }
}
