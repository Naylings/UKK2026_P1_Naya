import apiClient from "./client";
import type {
  Violation,
  ViolationListResponse,
  ViolationResponse,
} from "@/types/violation";

export const violationApi = {
  list: async (params?: {
    search?: string;
    status?: string;
    type?: string;
    page?: number;
    per_page?: number;
  }): Promise<ViolationListResponse> => {
    const res = await apiClient.get<ViolationListResponse>("/violations", {
      params,
    });

    return res.data;
  },

  get: async (id: number): Promise<Violation> => {
    const res = await apiClient.get<ViolationResponse>(`/violations/${id}`);
    return res.data.data;
  },
};
