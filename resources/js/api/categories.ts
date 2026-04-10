// ─────────────────────────────────────────────
// api/users.ts
// Pure HTTP calls untuk user management — tidak ada side effect
// ─────────────────────────────────────────────

import apiClient from "./client";
import type {
  Category,
  CategoryResponse,
  CreateCategoryPayload,
  UpdateCategoryPayload,
  PaginatedCategoriesResponse,
} from "@/types/category";
import type { ApiMessageResponse, LaravelApiResponse } from "@/types/auth";

export const categoriesApi = {
  /**
   * GET /api/categories
   * Ambil semua category
   */
  list: async (params?: {
    search?: string;
    per_page?: number;
    page?: number;
  }): Promise<PaginatedCategoriesResponse> => {
    const res = await apiClient.get<PaginatedCategoriesResponse>("/categories", { params });
    return res.data;
  },

  /**
   * POST /api/categories
   * Buat category baru 
   */
  create: async (
    payload: CreateCategoryPayload,
  ): Promise<LaravelApiResponse<Category>> => {
    const res = await apiClient.post<LaravelApiResponse<Category>>(
      "/categories",
      payload,
    );
    return res.data;
  },

  /**
   * PUT /api/categories/:id
   * Update category (name + description fields only)
   */
  update: async (
    id: number,
    payload: UpdateCategoryPayload,
  ): Promise<LaravelApiResponse<Category>> => {
    const res = await apiClient.put<LaravelApiResponse<Category>>(
      `/categories/${id}`,
      payload,
    );
    return res.data;
  },

  /**
   * DELETE /api/categories/:id
   * Hapus category (dengan validasi relasi)
   */
  delete: async (id: number): Promise<string> => {
    const res = await apiClient.delete<ApiMessageResponse>(`/categories/${id}`);
    return res.data.message;
  },

};
