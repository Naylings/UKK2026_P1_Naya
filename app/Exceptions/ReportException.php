<?php

namespace App\Exceptions;

class ReportException extends ApiException
{
    public static function invalidType(): self
    {
        return new self(
            'Jenis laporan tidak valid.',
            422,
            'INVALID_REPORT_TYPE',
        );
    }

    public static function invalidDateRange(): self
    {
        return new self(
            'Rentang tanggal tidak valid.',
            422,
            'INVALID_DATE_RANGE',
        );
    }

    public static function dateRangeTooLarge(int $maxDays): self
    {
        return new self(
            "Rentang tanggal terlalu besar. Maksimal {$maxDays} hari.",
            422,
            'DATE_RANGE_TOO_LARGE',
        );
    }

    public static function missingDate(): self
    {
        return new self(
            'Tanggal awal dan akhir wajib diisi.',
            422,
            'DATE_REQUIRED',
        );
    }

    public static function noData(): self
    {
        return new self(
            'Tidak ada data untuk laporan ini.',
            404,
            'REPORT_NO_DATA',
        );
    }

    
    public static function exportFailed(string $reason = ''): self
    {
        return new self(
            'Gagal membuat file laporan' . ($reason ? ': ' . $reason : '.'),
            500,
            'REPORT_EXPORT_FAILED',
        );
    }

    public static function fileGenerationFailed(): self
    {
        return new self(
            'Gagal generate file Excel.',
            500,
            'FILE_GENERATION_FAILED',
        );
    }

    
    public static function tooManyRows(int $limit): self
    {
        return new self(
            "Data terlalu besar untuk diexport. Maksimal {$limit} baris.",
            422,
            'TOO_MANY_ROWS',
        );
    }

    public static function timeout(): self
    {
        return new self(
            'Proses export terlalu lama. Silakan coba lagi.',
            500,
            'EXPORT_TIMEOUT',
        );
    }

    
    public static function unauthorized(): self
    {
        return new self(
            'Anda tidak memiliki akses ke laporan ini.',
            403,
            'REPORT_UNAUTHORIZED',
        );
    }
}