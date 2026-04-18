
import { AxiosError } from "axios";
import { usersApi } from "@/api/users";
import type {
  User,
  CreateUserPayload,
  UpdateUserPayload,
  UpdateUserCreditPayload,
  PaginatedUsersResponse,
} from "@/types/user";
import type { ApiErrorResponse } from "@/types/auth";

interface UserMutationResult {
  user: User;
  message: string;
}



export function parseUserError(error: unknown): string {
  if (error instanceof AxiosError) {
    const data = error.response?.data as ApiErrorResponse | undefined;

    if (data?.message) return data.message;

    switch (error.response?.status) {
      case 401:
        return "Sesi tidak valid. Silakan login ulang.";
      case 403:
        return "Anda tidak memiliki akses.";
      case 404:
        return "User tidak ditemukan.";
      case 422:
        return "Data yang dikirim tidak valid.";
      case 500:
        return "Terjadi kesalahan pada server. Coba lagi nanti.";
    }
  }

  return "Terjadi kesalahan tidak diketahui.";
}


export const userService = {
  
  async getAll(params?: {
    search?: string;
    role?: string;
    per_page?: number;
    page?: number;
  }): Promise<PaginatedUsersResponse> {
    try {
      const response = await usersApi.list(params);
      return response;
    } catch (error) {
      throw parseUserError(error);
    }
  },

  
  async getById(id: number): Promise<User> {
    try {
      return await usersApi.get(id);
    } catch (error) {
      throw parseUserError(error);
    }
  },

  
  async create(payload: CreateUserPayload): Promise<UserMutationResult> {
    try {
      const response = await usersApi.create(payload);
      return {
        user: response.data,
        message: response.message ?? "User berhasil dibuat. Credit awal: 100.",
      };
    } catch (error) {
      throw parseUserError(error);
    }
  },

  
  async update(id: number, payload: UpdateUserPayload): Promise<UserMutationResult> {
    try {
      const response = await usersApi.update(id, payload);
      return {
        user: response.data,
        message: response.message ?? "User berhasil diupdate.",
      };
    } catch (error) {
      throw parseUserError(error);
    }
  },

  
  async delete(id: number): Promise<string> {
    try {
      return await usersApi.delete(id);
    } catch (error) {
      throw parseUserError(error);
    }
  },

  
  async updateCredit(
    id: number,
    payload: UpdateUserCreditPayload,
  ): Promise<User> {
    try {
      const response = await usersApi.updateCredit(id, payload);
      return response;
    } catch (error) {
      throw parseUserError(error);
    }
  },
};
