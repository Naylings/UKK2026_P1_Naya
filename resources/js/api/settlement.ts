// ─────────────────────────────────────────────
// api/settlement.ts
// Pure HTTP calls untuk settlement (pelunasan)
// ─────────────────────────────────────────────

import apiClient from "./client";
import type {
  Settlement,
  SettlementResponse,
  PaginatedSettlementResponse,
  CreateSettlementPayload,
} from "@/types/settlement";
import type { LaravelApiResponse } from "@/types/auth";

export const settlementApi = {
  /**
   * POST /api/violations/:id/settle
   * Melakukan pelunasan pelanggaran
   */
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

  /**
   * GET /api/settlements
   * Ambil list settlement (optional/reporting)
   */
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

  /**
   * GET /api/settlements/:id
   * Detail settlement
   */
  get: async (id: number): Promise<Settlement> => {
    const res = await apiClient.get<SettlementResponse>(
      `/settlements/${id}`,
    );
    return res.data.data;
  },
};