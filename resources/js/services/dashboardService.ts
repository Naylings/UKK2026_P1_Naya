import { AxiosError } from "axios";
import { dashboardApi } from "@/api/dashboard";
import type {
  DashboardParams,
  UserDashboardResponse,
  AdminDashboardResponse,
} from "@/types/dashboard";
import type { ApiErrorResponse } from "@/types/auth";

export function parseDashboardError(error: unknown): string {
  if (error instanceof AxiosError) {
    const data = error.response?.data as ApiErrorResponse | undefined;

    if (data?.message) return data.message;

    switch (error.response?.status) {
      case 401:
        return "Sesi tidak valid. Silakan login ulang.";
      case 403:
        return "Anda tidak memiliki akses ke dashboard.";
      case 500:
        return "Server error. Coba refresh halaman.";
    }
  }
  return "Gagal memuat dashboard. Coba lagi.";
}

export const dashboardService = {
  async getUserDashboard(
    params?: DashboardParams
  ): Promise<UserDashboardResponse> {
    try {
      return await dashboardApi.getUserDashboard(params);
    } catch (error) {
      throw new Error(parseDashboardError(error));
    }
  },

  async getAdminDashboard(
    params?: DashboardParams
  ): Promise<AdminDashboardResponse> {
    try {
      return await dashboardApi.getAdminDashboard(params);
    } catch (error) {
      throw new Error(parseDashboardError(error));
    }
  },
};