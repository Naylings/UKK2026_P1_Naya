// ─────────────────────────────────────────────
// services/userService.ts
// Business logic FE: parsing response, error handling
// ─────────────────────────────────────────────

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

// ── Error helper ──────────────────────────────────────────────────────────

/**
 * Ekstrak pesan error dari response BE maupun network error.
 * Selalu mengembalikan string yang siap ditampilkan ke user.
 */
export function parseUserError(error: unknown): string {
  if (error instanceof AxiosError) {
    const data = error.response?.data as ApiErrorResponse | undefined;

    if (data?.message) return data.message;

    // Fallback berdasarkan HTTP status
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

// ── Service methods ───────────────────────────────────────────────────────

export const userService = {
  /**
   * Ambil semua user.
   */
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

  /**
   * Ambil user berdasarkan ID.
   */
  async getById(id: number): Promise<User> {
    try {
      return await usersApi.get(id);
    } catch (error) {
      throw parseUserError(error);
    }
  },

  /**
   * Buat user baru dengan detail.
   */
  async create(payload: CreateUserPayload): Promise<User> {
    try {
      return await usersApi.create(payload);
    } catch (error) {
      throw parseUserError(error);
    }
  },

  /**
   * Update user (role + detail fields).
   */
  async update(id: number, payload: UpdateUserPayload): Promise<User> {
    try {
      return await usersApi.update(id, payload);
    } catch (error) {
      throw parseUserError(error);
    }
  },

  /**
   * Hapus user.
   * Melempar error jika user masih punya relasi atau ada masalah constraint.
   */
  async delete(id: number): Promise<string> {
    try {
      return await usersApi.delete(id);
    } catch (error) {
      throw parseUserError(error);
    }
  },

  /**
   * Update credit user.
   */
  async updateCredit(
    id: number,
    payload: UpdateUserCreditPayload,
  ): Promise<User> {
    try {
      return await usersApi.updateCredit(id, payload);
    } catch (error) {
      throw parseUserError(error);
    }
  },
};
