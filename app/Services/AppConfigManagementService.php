<?php

namespace App\Services;

use App\Exceptions\AppConfigException;
use App\Models\AppConfig;

class AppConfigManagementService
{
    public function getConfig(): AppConfig
    {
        $config = AppConfig::first();

        if (!$config) {
            throw AppConfigException::notFound();
        }

        return $config;
    }

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

    public function initializeConfig(array $defaultConfig): AppConfig
    {
        $config = AppConfig::first();

        if (!$config) {
            $config = AppConfig::create($defaultConfig);
        }

        return $config;
    }
}
