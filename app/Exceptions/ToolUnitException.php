<?php

namespace App\Exceptions;

use Exception;

class ToolUnitException extends Exception
{
    public static function notFound(): self
    {
        return new self('Unit tool tidak ditemukan.', 404);
    }

    public static function createFailed(string $message): self
    {
        return new self("Gagal membuat unit: {$message}", 422);
    }

    public static function updateFailed(string $message): self
    {
        return new self("Gagal mengupdate unit: {$message}", 422);
    }

    public static function deleteFailed(string $message): self
    {
        return new self("Gagal menghapus unit: {$message}", 422);
    }

    public static function invalidStatus(string $status): self
    {
        return new self("Status '{$status}' tidak valid. Gunakan: available, lent, nonactive.", 422);
    }

    public static function unitIsLent(): self
    {
        return new self('Unit sedang dalam peminjaman dan tidak dapat dihapus.', 422);
    }

    public static function codeAlreadyExists(string $code): self
    {
        return new self("Kode unit '{$code}' sudah ada.", 409);
    }

    public static function invalidToolId(): self
    {
        return new self('Tool ID tidak valid atau tool tidak ditemukan.', 404);
    }

    public static function recordConditionFailed(string $message): self
    {
        return new self("Gagal mencatat kondisi: {$message}", 422);
    }
}
