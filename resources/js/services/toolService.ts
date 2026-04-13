// ─────────────────────────────────────────────
// services/toolService.ts
// Business logic FE: parsing response, error handling
// ─────────────────────────────────────────────

import { AxiosError } from "axios";
import { toolsApi } from "@/api/tools";
import type { Tool, PaginatedToolsResponse } from "@/types/tool";
import type { ApiErrorResponse } from "@/types/auth";

interface ToolMutationResult {
  tool: Tool;
  message: string;
}

// ── Error helper ──────────────────────────────────────────────────────────

/**
 * Ekstrak pesan error dari response BE maupun network error.
 * Selalu mengembalikan string yang siap ditampilkan ke user.
 */
export function parseToolError(error: unknown): string {
  if (error instanceof AxiosError) {
    const data = error.response?.data as ApiErrorResponse | undefined;

    if (data?.message) return data.message;

    switch (error.response?.status) {
      case 401:
        return "Sesi tidak valid. Silakan login ulang.";
      case 403:
        return "Anda tidak memiliki akses.";
      case 404:
        return "Alat tidak ditemukan.";
      case 422:
        return "Data yang dikirim tidak valid.";
      case 500:
        return "Terjadi kesalahan pada server. Coba lagi nanti.";
    }
  }

  return "Terjadi kesalahan tidak diketahui.";
}

// ── Service methods ───────────────────────────────────────────────────────

export const toolService = {
  /**
   * Ambil semua tool dengan pagination, search, dan filter kategori.
   */
  async getAll(params?: {
    search?: string;
    category?: string | number;
    per_page?: number;
    page?: number;
  }): Promise<PaginatedToolsResponse> {
    try {
      const apiParams = {
        search: params?.search,
        category: params?.category ? String(params.category) : undefined,
        per_page: params?.per_page,
        page: params?.page,
      };
      return await toolsApi.list(apiParams);
    } catch (error) {
      throw parseToolError(error);
    }
  },

  /**
   * Ambil tool berdasarkan ID.
   */
  async getById(id: number): Promise<Tool> {
    try {
      return await toolsApi.get(id);
    } catch (error) {
      throw parseToolError(error);
    }
  },

  /**
   * Buat tool baru.
   */
  async create(payload: FormData): Promise<ToolMutationResult> {
    try {
      const response = await toolsApi.create(payload);
      return {
        tool: response.data,
        message: response.message ?? "Alat berhasil dibuat.",
      };
    } catch (error) {
      throw parseToolError(error);
    }
  },

  /**
   * Update tool.
   */
  async update(
    id: number,
    payload: FormData,
  ): Promise<ToolMutationResult> {
    try {
      const response = await toolsApi.update(id, payload);
      return {
        tool: response.data,
        message: response.message ?? "Alat berhasil diupdate.",
      };
    } catch (error) {
      throw parseToolError(error);
    }
  },

  /**
   * Hapus tool.
   */
  async delete(id: number): Promise<string> {
    try {
      const response = await toolsApi.delete(id);
      if (typeof response === "string") {
        return response;
      }
      return (response as any).message ?? "Alat berhasil dihapus.";
    } catch (error) {
      throw parseToolError(error);
    }
  },
};
