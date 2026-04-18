import apiClient from "./client";
import type {
  DashboardParams,
  UserDashboardResponse,
  AdminDashboardResponse,
} from "@/types/dashboard";

export const dashboardApi = {
  async getUserDashboard(
    params?: DashboardParams
  ): Promise<UserDashboardResponse> {
    const res = await apiClient.get<UserDashboardResponse>(
      "/dashboard/user",
      { params }
    );
    return res.data;
  },

  async getAdminDashboard(
    params?: DashboardParams
  ): Promise<AdminDashboardResponse> {
    const res = await apiClient.get<AdminDashboardResponse>(
      "/dashboard/admin",
      { params }
    );
    return res.data;
  },
};