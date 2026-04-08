// ─────────────────────────────────────────────
// api/users.ts
// Pure HTTP calls untuk user management — tidak ada side effect
// ─────────────────────────────────────────────

import apiClient from "./client";
import type {
  User,
  UserResponse,
  CreateUserPayload,
  UpdateUserPayload,
  UpdateUserCreditPayload,
  PaginatedUsersResponse,
} from "@/types/user";

export const usersApi = {
  /**
   * GET /api/users
   * Ambil semua user
   */
  list: async (params?: {
    search?: string;
    role?: string;
    per_page?: number;
    page?: number;
  }): Promise<PaginatedUsersResponse> => {
    const res = await apiClient.get<PaginatedUsersResponse>("/users", { params });
    return res.data;
  },

  /**
   * GET /api/users/:id
   * Ambil detail user tertentu
   */
  get: async (id: number): Promise<User> => {
    const res = await apiClient.get<UserResponse>(`/users/${id}`);
    return res.data.data;
  },

  /**
   * POST /api/users
   * Buat user baru dengan detail
   */
  create: async (payload: CreateUserPayload): Promise<User> => {
    const res = await apiClient.post<UserResponse>("/users", payload);
    return res.data.data;
  },

  /**
   * PUT /api/users/:id
   * Update user (role + detail fields only)
   */
  update: async (id: number, payload: UpdateUserPayload): Promise<User> => {
    const res = await apiClient.put<UserResponse>(`/users/${id}`, payload);
    return res.data.data;
  },

  /**
   * DELETE /api/users/:id
   * Hapus user (dengan validasi relasi)
   */
  delete: async (id: number): Promise<string> => {
    await apiClient.delete(`/users/${id}`);
    return "User berhasil dihapus.";
  },

  /**
   * POST /api/users/:id/credit
   * Update credit user
   */
  updateCredit: async (
    id: number,
    payload: UpdateUserCreditPayload,
  ): Promise<User> => {
    const res = await apiClient.post<UserResponse>(
      `/users/${id}/credit`,
      payload,
    );
    return res.data.data;
  },
};
