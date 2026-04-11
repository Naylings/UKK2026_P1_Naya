// ─────────────────────────────────────────────
// services/userService.ts
// Business logic FE: parsing response, error handling
// ─────────────────────────────────────────────

import { AxiosError } from "axios";
import { categoriesApi } from "@/api/categories";
import type {
    Category,
    CreateCategoryPayload,
    UpdateCategoryPayload,
    PaginatedCategoriesResponse,
} from "@/types/category";
import type { ApiErrorResponse } from "@/types/auth";

interface CategoryMutationResult {
    category: Category;
    message: string;
}

// ── Error helper ──────────────────────────────────────────────────────────

/**
 * Ekstrak pesan error dari response BE maupun network error.
 * Selalu mengembalikan string yang siap ditampilkan ke user.
 */
export function parseCategoryError(error: unknown): string {
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
                return "Category tidak ditemukan.";
            case 422:
                return "Data yang dikirim tidak valid.";
            case 500:
                return "Terjadi kesalahan pada server. Coba lagi nanti.";
        }
    }

    return "Terjadi kesalahan tidak diketahui.";
}

// ── Service methods ───────────────────────────────────────────────────────

export const categoryService = {
    /**
     * Ambil semua category.
     */
    async getAll(params?: {
        search?: string;
        role?: string;
        per_page?: number;
        page?: number;
    }): Promise<PaginatedCategoriesResponse> {
        try {
            const response = await categoriesApi.list(params);
            return response;
        } catch (error) {
            throw parseCategoryError(error);
        }
    },

    /**
     * Buat category baru.
     */
    async create(payload: CreateCategoryPayload): Promise<CategoryMutationResult> {
        try {
            const response = await categoriesApi.create(payload);
            return {
                category: response.data,
                message: response.message ?? "Category berhasil dibuat.",
            };
        } catch (error) {
            throw parseCategoryError(error);
        }
    },

    /**
     * Update category (name + description fields).
     */
    async update(
        id: number,
        payload: UpdateCategoryPayload,
    ): Promise<CategoryMutationResult> {
        try {
            const response = await categoriesApi.update(id, payload);
            return {
                category: response.data,
                message: response.message ?? "Category berhasil diupdate.",
            };
        } catch (error) {
            throw parseCategoryError(error);
        }
    },

    /**
     * Hapus category.
     * Melempar error jika category masih punya relasi atau ada masalah constraint.
     */
    async delete(id: number): Promise<string> {
        try {
            const response = await categoriesApi.delete(id);
            return response;
        } catch (error) {
            throw parseCategoryError(error);
        }
    },
};
