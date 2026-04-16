<?php

namespace App\Http\Controllers\AppConfig;

use App\Http\Controllers\Controller;
use App\Http\Requests\AppConfig\UpdateAppConfigRequest;
use App\Http\Resources\AppConfig\AppConfigResource;
use App\Services\ActivityLogService;
use App\Services\AppConfigManagementService;

class AppConfigController extends Controller
{
    public function __construct(
        private readonly AppConfigManagementService $appConfigService
    ) {}

    /**
     * Ambil konfigurasi aplikasi
     */
    public function show()
    {
        $config = $this->appConfigService->getConfig();

        return new AppConfigResource($config);
    }

    /**
     * Update konfigurasi aplikasi
     */
    public function update(UpdateAppConfigRequest $request)
    {
        $config = $this->appConfigService->updateConfig($request->validated());
        app(ActivityLogService::class)->log(
            'app_config.updated',
            'app_config',
            'Memperbarui konfigurasi aplikasi.',
            ['updated_fields' => array_keys($request->validated())]
        );

        return (new AppConfigResource($config))
            ->additional([
                'message' => 'Konfigurasi aplikasi berhasil diperbarui.',
            ])
            ->response()
            ->setStatusCode(200);
    }
}
