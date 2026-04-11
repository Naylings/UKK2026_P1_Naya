// ─────────────────────────────────────────────
// api/tools.ts
// Pure HTTP calls untuk tool management — tidak ada side effect
// ─────────────────────────────────────────────

import type {
  PaginatedToolsResponse,
  Tool,
  ToolResponse,
  UpdateToolPayload,
} from "@/types/tool";
import apiClient from "./client";
import type { ApiMessageResponse, LaravelApiResponse } from "@/types/auth";

export const toolsApi = {
  /**
   * GET /api/tools
   * Ambil semua tool
   */
  list: async (params?: {
    search?: string;
    category?: string;
    per_page?: number;
    page?: number;
  }): Promise<PaginatedToolsResponse> => {
    const res = await apiClient.get<PaginatedToolsResponse>("/tools", { params });
    return res.data;
  },

  /**
   * GET /api/tools/:id
   * Ambil detail tool tertentu
   */
  get: async (id: number): Promise<Tool> => {
    const res = await apiClient.get<ToolResponse>(`/tools/${id}`);
    return res.data.data;
  },

  /**
   * POST /api/tools
   * Buat tool baru — menerima FormData untuk support file upload foto
   */
  create: async (payload: FormData): Promise<LaravelApiResponse<Tool>> => {
    const res = await apiClient.post<LaravelApiResponse<Tool>>("/tools", payload, {
      headers: {
        "Content-Type": "multipart/form-data",
      },
    });
    return res.data;
  },

  /**
   * PUT /api/tools/:id
   * Update tool
   */
  update: async (
    id: number,
    payload: UpdateToolPayload,
  ): Promise<LaravelApiResponse<Tool>> => {
    const res = await apiClient.put<LaravelApiResponse<Tool>>(
      `/tools/${id}`,
      payload,
    );
    return res.data;
  },

  /**
   * DELETE /api/tools/:id
   * Hapus tool (dengan validasi relasi)
   */
  delete: async (id: number): Promise<string> => {
    const res = await apiClient.delete<ApiMessageResponse>(`/tools/${id}`);
    return res.data.message;
  },
};