
import apiClient from "./client";
import type {
  Settlement,
  SettlementResponse,
  PaginatedSettlementResponse,
  CreateSettlementPayload,
} from "@/types/settlement";
import type { LaravelApiResponse } from "@/types/auth";

export const settlementApi = {
  
  settle: async (
    violationId: number,
    payload: CreateSettlementPayload,
  ): Promise<LaravelApiResponse<Settlement>> => {
    const res = await apiClient.post<LaravelApiResponse<Settlement>>(
      `/violations/${violationId}/settle`,
      payload,
    );
    return res.data;
  },

  
  list: async (params?: {
    search?: string;
    per_page?: number;
    page?: number;
  }): Promise<PaginatedSettlementResponse> => {
    const res = await apiClient.get<PaginatedSettlementResponse>(
      "/settlements",
      { params },
    );
    return res.data;
  },

  
  get: async (id: number): Promise<Settlement> => {
    const res = await apiClient.get<SettlementResponse>(
      `/settlements/${id}`,
    );
    return res.data.data;
  },
};