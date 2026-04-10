<?php

namespace App\Exceptions;


class CategoryException extends ApiException
{

    // -------------------------------------------------------------------------
    // Named constructors — satu tempat untuk semua skenario error kategori
    // -------------------------------------------------------------------------
    public static function NotFound(): self
    {
        return new self(
            'Kategori tidak ditemukan.',
            404,
            'CATEGORY_NOT_FOUND',
        );
    }

    public static function AlreadyExists(): self
    {
        return new self(
            'Kategori dengan nama tersebut sudah ada.',
            422,
            'CATEGORY_ALREADY_EXISTS',
        );
    }

    public static function CreateFailed(string $reason = ''): self
    {
        return new self(
            'Gagal membuat kategori' . ($reason ? ': ' . $reason : '.'),
            500,
            'CATEGORY_CREATE_FAILED',
        );
    }

    public static function UpdateFailed(string $reason = ''): self
    {
        return new self(
            'Gagal memperbarui kategori' . ($reason ? ': ' . $reason : '.'),
            500,
            'CATEGORY_UPDATE_FAILED',
        );
    }

    public static function DeleteFailed(string $reason = ''): self
    {
        return new self(
            'Gagal menghapus kategori' . ($reason ? ': ' . $reason : '.'),
            500,
            'CATEGORY_DELETE_FAILED',
        );
    }

    public static function HasRelations(): self
    {
        return new self(
            'Kategori tidak dapat dihapus karena masih digunakan pada data lain.',
            422,
            'CATEGORY_HAS_RELATIONS',
        );
    }
}
