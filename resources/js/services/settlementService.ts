
import { AxiosError } from "axios";
import type { Settlement } from "@/types/settlement";
import type { ApiErrorResponse } from "@/types/auth";
import { settlementApi } from "@/api/settlement";

interface SettlementMutationResult {
  settlement: Settlement;
  message: string;
}


export function parseSettlementError(error: unknown): string {
  if (error instanceof AxiosError) {
    const data = error.response?.data as ApiErrorResponse | undefined;

    if (data?.message) return data.message;

    switch (error.response?.status) {
      case 401:
        return "Sesi tidak valid. Silakan login ulang.";
      case 403:
        return "Anda tidak memiliki akses.";
      case 404:
        return "Data pelanggaran / settlement tidak ditemukan.";
      case 422:
        return "Data tidak valid.";
      case 409:
        return "Pelanggaran sudah diselesaikan.";
      case 500:
        return "Terjadi kesalahan pada server.";
    }
  }

  return "Terjadi kesalahan tidak diketahui.";
}


export const settlementService = {
  
  async getAll(params?: {
    search?: string;
    per_page?: number;
    page?: number;
  }) {
    try {
      return await settlementApi.list(params);
    } catch (error) {
      throw parseSettlementError(error);
    }
  },

  
  async getById(id: number): Promise<Settlement> {
    try {
      return await settlementApi.get(id);
    } catch (error) {
      throw parseSettlementError(error);
    }
  },

  
  async settle(
    violationId: number,
    description: string
  ): Promise<SettlementMutationResult> {
    try {
      const response = await settlementApi.settle(violationId, { description });

      return {
        settlement: response.data,
        message: response.message ?? "Pelanggaran berhasil diselesaikan.",
      };
    } catch (error) {
      throw parseSettlementError(error);
    }
  },
};