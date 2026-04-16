import { AxiosError } from "axios";
import { activityLogApi } from "@/api/activity-log";
import type { ApiErrorResponse } from "@/types/auth";
import type { ActivityLogItem } from "@/types/activity-log";

export function parseActivityLogError(error: unknown): string {
  if (error instanceof AxiosError) {
    const data = error.response?.data as ApiErrorResponse | undefined;

    if (data?.message) return data.message;

    switch (error.response?.status) {
      case 401:
        return "Sesi tidak valid. Silakan login ulang.";
      case 403:
        return "Anda tidak memiliki akses.";
      case 404:
        return "Data activity log tidak ditemukan.";
      case 500:
        return "Terjadi kesalahan pada server.";
    }
  }

  return "Terjadi kesalahan tidak diketahui.";
}

export const activityLogService = {
  async getAll(params?: {
    search?: string;
    module?: string;
    action?: string;
    per_page?: number;
    page?: number;
  }) {
    try {
      return await activityLogApi.list(params);
    } catch (error) {
      throw parseActivityLogError(error);
    }
  },

  async getById(id: number): Promise<ActivityLogItem> {
    try {
      return await activityLogApi.get(id);
    } catch (error) {
      throw parseActivityLogError(error);
    }
  },
};
