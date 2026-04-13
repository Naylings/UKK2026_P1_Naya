// ─────────────────────────────────────────────
// api/appconfig.ts
// Pure HTTP calls untuk app config management — tidak ada side effect
// ─────────────────────────────────────────────

import apiClient from "./client";
import type { AppConfig, UpdateAppConfigPayload, AppConfigResponse } from "@/types/appconfig";
import type { LaravelApiResponse } from "@/types/auth";

export const appConfigApi = {
  /**
   * GET /api/app-config
   * Ambil konfigurasi aplikasi
   */
  get: async (): Promise<AppConfig> => {
    const res = await apiClient.get<AppConfigResponse>("/app-config");
    return res.data.data;
  },

  /**
   * PUT /api/app-config
   * Update konfigurasi aplikasi
   */
  update: async (payload: UpdateAppConfigPayload): Promise<LaravelApiResponse<AppConfig>> => {
    const res = await apiClient.put<LaravelApiResponse<AppConfig>>(
      "/app-config",
      payload,
    );
    return res.data;
  },
};
