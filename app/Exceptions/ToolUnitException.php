<?php

namespace App\Exceptions;

class ToolUnitException extends ApiException
{

    public static function notFound(): self
    {
        return new self(
            'Unit tool tidak ditemukan.',
            404,
            'TOOL_UNIT_NOT_FOUND',
        );
    }

    public static function createFailed(string $reason = ''): self
    {
        return new self(
            'Gagal membuat unit' . ($reason ? ': ' . $reason : '.'),
            500,
            'TOOL_UNIT_CREATE_FAILED',
        );
    }

    public static function updateFailed(string $reason = ''): self
    {
        return new self(
            'Gagal mengupdate unit' . ($reason ? ': ' . $reason : '.'),
            500,
            'TOOL_UNIT_UPDATE_FAILED',
        );
    }

    public static function deleteFailed(string $reason = ''): self
    {
        return new self(
            'Gagal menghapus unit' . ($reason ? ': ' . $reason : '.'),
            500,
            'TOOL_UNIT_DELETE_FAILED',
        );
    }

    public static function invalidStatus(string $status): self
    {
        return new self(
            "Status '{$status}' tidak valid. Gunakan: available, lent, nonactive.",
            422,
            'INVALID_UNIT_STATUS',
        );
    }

    public static function unitIsLent(): self
    {
        return new self(
            'Unit sedang dalam peminjaman dan tidak dapat dihapus.',
            409,
            'UNIT_IS_LENT',
        );
    }

    public static function codeAlreadyExists(string $code): self
    {
        return new self(
            "Kode unit '{$code}' sudah ada.",
            409,
            'UNIT_CODE_ALREADY_EXISTS',
        );
    }

    public static function invalidToolId(): self
    {
        return new self(
            'Tool ID tidak valid atau tool tidak ditemukan.',
            404,
            'INVALID_TOOL_ID',
        );
    }

    public static function recordConditionFailed(string $reason = ''): self
    {
        return new self(
            'Gagal mencatat kondisi' . ($reason ? ': ' . $reason : '.'),
            500,
            'RECORD_CONDITION_FAILED',
        );
    }

    public static function fetchAvailableFailed(string $reason = ''): self
    {
        return new self(
            'Gagal mengambil unit tersedia' . ($reason ? ': ' . $reason : '.'),
            500,
            'FETCH_AVAILABLE_UNITS_FAILED',
        );
    }
}