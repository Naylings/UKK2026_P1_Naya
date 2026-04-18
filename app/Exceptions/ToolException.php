<?php

namespace App\Exceptions;


class ToolException extends ApiException
{

    public static function notFound(): self
    {
        return new self(
            'Alat tidak ditemukan.',
            404,
            'TOOL_NOT_FOUND',
        );
    }

    public static function alreadyExists(): self
    {
        return new self(
            'Alat dengan nama tersebut sudah ada.',
            422,
            'TOOL_ALREADY_EXISTS',
        );
    }

    public static function createFailed(string $reason = ''): self
    {
        return new self(
            'Gagal membuat alat' . ($reason ? ': ' . $reason : '.'),
            500,
            'TOOL_CREATE_FAILED',
        );
    }

    public static function updateFailed(string $reason = ''): self
    {
        return new self(
            'Gagal memperbarui alat' . ($reason ? ': ' . $reason : '.'),
            500,
            'TOOL_UPDATE_FAILED',
        );
    }

    public static function deleteFailed(string $reason = ''): self
    {
        return new self(
            'Gagal menghapus alat' . ($reason ? ': ' . $reason : '.'),
            500,
            'TOOL_DELETE_FAILED',
        );
    }

    public static function hasRelations(): self
    {
        return new self(
            'Alat tidak dapat dihapus karena masih digunakan pada data lain.',
            422,
            'TOOL_HAS_RELATIONS',
        );
    }

    public static function bundleComponentsRequired(): self
    {
        return new self(
            'Bundle harus memiliki minimal satu komponen alat.',
            422,
            'TOOL_BUNDLE_COMPONENTS_REQUIRED',
        );
    }

    public static function invalidBundleComponent(): self
    {
        return new self(
            'Data komponen bundle tidak valid.',
            422,
            'TOOL_INVALID_BUNDLE_COMPONENT',
        );
    }

    public static function invalidPhotoUpload(string $reason = ''): self
    {
        return new self(
            'Upload foto alat gagal' . ($reason ? ': ' . $reason : '.'),
            422,
            'TOOL_INVALID_PHOTO_UPLOAD',
        );
    }
}
