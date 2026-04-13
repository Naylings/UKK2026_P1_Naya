<?php

namespace App\Services;

use App\Exceptions\AppConfigException;
use App\Models\AppConfig;

class AppConfigManagementService
{
    /**
     * Ambil konfigurasi aplikasi (hanya 1 record)
     *
     * @return AppConfig
     */
    public function getConfig(): AppConfig
    {
        $config = AppConfig::first();

        if (!$config) {
            throw AppConfigException::notFound();
        }

        return $config;
    }

    /**
     * Update konfigurasi aplikasi
     *
     * @param array $data
     * @return AppConfig
     */
    public function updateConfig(array $data): AppConfig
    {
        $config = AppConfig::first();

        if (!$config) {
            throw AppConfigException::notFound();
        }

        $data['updated_at'] = now();

        if (!$config->update($data)) {
            throw AppConfigException::updateFailed();
        }

        return $config->fresh();
    }

    /**
     * Initialize konfigurasi aplikasi jika belum ada
     *
     * @param array $defaultConfig
     * @return AppConfig
     */
    public function initializeConfig(array $defaultConfig): AppConfig
    {
        $config = AppConfig::first();

        if (!$config) {
            $config = AppConfig::create($defaultConfig);
        }

        return $config;
    }
}
