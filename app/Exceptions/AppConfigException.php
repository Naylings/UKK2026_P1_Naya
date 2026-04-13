<?php

namespace App\Exceptions;

class AppConfigException extends ApiException
{
    public static function notFound(): self
    {
        return new self(
            'Konfigurasi aplikasi tidak ditemukan.',
            404,
            'APP_CONFIG_NOT_FOUND',
        );
    }

    public static function updateFailed(): self
    {
        return new self(
            'Gagal memperbarui konfigurasi aplikasi.',
            422,
            'APP_CONFIG_UPDATE_FAILED',
        );
    }
}
