import { AxiosError } from "axios";
import { violationApi } from "@/api/violation";
import type { ApiErrorResponse } from "@/types/auth";
import type { Violation } from "@/types/violation";

export function parseViolationError(error: unknown): string {
  if (error instanceof AxiosError) {
    const data = error.response?.data as ApiErrorResponse | undefined;

    if (data?.message) return data.message;

    switch (error.response?.status) {
      case 401:
        return "Sesi tidak valid. Silakan login ulang.";
      case 403:
        return "Anda tidak memiliki akses.";
      case 404:
        return "Data violation tidak ditemukan.";
      case 422:
        return "Data tidak valid.";
      case 500:
        return "Terjadi kesalahan pada server.";
    }
  }

  return "Terjadi kesalahan tidak diketahui.";
}

export const violationService = {
  async getAll(params?: {
    search?: string;
    status?: string;
    type?: string;
    per_page?: number;
    page?: number;
  }) {
    try {
      return await violationApi.list(params);
    } catch (error) {
      throw parseViolationError(error);
    }
  },

  async getById(id: number): Promise<Violation> {
    try {
      return await violationApi.get(id);
    } catch (error) {
      throw parseViolationError(error);
    }
  },
};
