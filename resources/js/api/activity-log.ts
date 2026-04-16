import apiClient from "./client";
import type {
  ActivityLogItem,
  ActivityLogResponse,
  PaginatedActivityLogResponse,
} from "@/types/activity-log";

export const activityLogApi = {
  list: async (params?: {
    search?: string;
    module?: string;
    action?: string;
    per_page?: number;
    page?: number;
  }): Promise<PaginatedActivityLogResponse> => {
    const res = await apiClient.get<PaginatedActivityLogResponse>(
      "/activity-logs",
      { params },
    );
    return res.data;
  },

  get: async (id: number): Promise<ActivityLogItem> => {
    const res = await apiClient.get<ActivityLogResponse>(`/activity-logs/${id}`);
    return res.data.data;
  },
};
