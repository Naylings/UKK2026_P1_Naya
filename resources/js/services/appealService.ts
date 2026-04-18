import { AxiosError } from "axios";
import type { ApiErrorResponse } from "@/types/auth";

import { appealApi } from "@/api/appeal";
import type {
  Appeal,
  AppealFilters,
  CreateAppealPayload,
  CreateAppealResponse,
  ReviewAppealPayload,
  ReviewAppealResponse,
  AppealListResponse,
} from "@/types/appeal";

export function parseAppealError(error: unknown): string {
  if (error instanceof AxiosError) {
    const data = error.response?.data as ApiErrorResponse | undefined;

    if (data?.message) return data.message;

    switch (error.response?.status) {
      case 401:
        return "Sesi tidak valid. Silakan login ulang.";
      case 403:
        return "Anda tidak memiliki akses untuk appeal ini.";
      case 404:
        return "Data appeal tidak ditemukan.";
      case 422:
        return "Data tidak valid.";
      case 500:
        return "Terjadi kesalahan server.";
    }
  }

  return "Terjadi kesalahan tidak diketahui.";
}


export const appealService = {
  async createAppeal(payload: CreateAppealPayload): Promise<CreateAppealResponse> {
    try {
      const res = await appealApi.createAppeal(payload);
      return res;
    } catch (error) {
      throw parseAppealError(error);
    }
  },

  async getAll(params?: AppealFilters): Promise<AppealListResponse> {
    try {
      const res = await appealApi.getAll(params);
      return res;
    } catch (error) {
      throw parseAppealError(error);
    }
  },

  async getMy(params?: AppealFilters): Promise<AppealListResponse> {
    try {
      const res = await appealApi.getMy(params);
      return res;
    } catch (error) {
      throw parseAppealError(error);
    }
  },

  async reviewAppeal(
    id: number,
    payload: ReviewAppealPayload
  ): Promise<ReviewAppealResponse> {
    try {
      const res = await appealApi.reviewAppeal(id, payload);
      return res;
    } catch (error) {
      throw parseAppealError(error);
    }
  },
};

