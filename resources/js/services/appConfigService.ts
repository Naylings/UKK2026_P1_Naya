
import { appConfigApi } from "@/api/appconfig";
import type { AppConfig, UpdateAppConfigPayload } from "@/types/appconfig";

export const appConfigService = {
  
  async fetchConfig(): Promise<AppConfig> {
    try {
      const config = await appConfigApi.get();
      return config;
    } catch (error) {
      console.error("Failed to fetch app config:", error);
      throw error;
    }
  },

  
  async updateConfig(payload: UpdateAppConfigPayload): Promise<AppConfig> {
    try {
      const response = await appConfigApi.update(payload);
      return response.data;
    } catch (error) {
      console.error("Failed to update app config:", error);
      throw error;
    }
  },

  
  validatePayload(payload: Partial<UpdateAppConfigPayload>): string[] {
    const errors: string[] = [];

    if (!payload.name || payload.name.trim().length === 0) {
      errors.push("Nama aplikasi/instansi wajib diisi");
    }

    if (typeof payload.late_point !== "number" || payload.late_point < 0) {
      errors.push("Poin keterlambatan harus bilangan positif");
    }

    if (typeof payload.broken_point !== "number" || payload.broken_point < 0) {
      errors.push("Poin kerusakan harus bilangan positif");
    }

    if (typeof payload.lost_point !== "number" || payload.lost_point < 0) {
      errors.push("Poin kehilangan harus bilangan positif");
    }

    if (typeof payload.late_fine !== "number" || payload.late_fine < 0 || payload.late_fine > 100) {
      errors.push("Denda keterlambatan (%) harus antara 0-100");
    }

    if (typeof payload.broken_fine !== "number" || payload.broken_fine < 0 || payload.broken_fine > 100) {
      errors.push("Denda kerusakan (%) harus antara 0-100");
    }

    if (typeof payload.lost_fine !== "number" || payload.lost_fine < 0 || payload.lost_fine > 100) {
      errors.push("Denda kehilangan (%) harus antara 0-100");
    }

    return errors;
  },
};
