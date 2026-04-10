// ─────────────────────────────────────────────
// stores/user.ts
// Pinia store untuk user management — state, getters, actions
// ─────────────────────────────────────────────

import { defineStore } from "pinia";
import { computed, ref } from "vue";
import { categoryService } from "@/services/categoryService";
import type {
  Category,
  CreateCategoryPayload,
  UpdateCategoryPayload,
} from "@/types/category";

export const useCategoryStore = defineStore("category", () => {
  // ── State ──────────────────────────────────────────────────────────────

  const categories = ref<Category[]>([]);
  const currentCategory = ref<Category | null>(null);
  const loading = ref(false);
  const error = ref<string | null>(null);
  const successMessage = ref<string | null>(null);

  // ── Pagination ────────────────────────────────────────────────────────────

  const currentPage = ref(1);
  const lastPage = ref(1);
  const total = ref(0);
  const perPage = ref(10);

  // ── Getters ────────────────────────────────────────────────────────────

  const categoryCount = computed(() => categories.value.length);
  const hasCategories = computed(() => categories.value.length > 0);

  // ── Actions ────────────────────────────────────────────────────────────

  /**
   * Ambil list semua category.
   */
  async function fetchCategories(params?: {
    search?: string;
    per_page?: number;
    page?: number;
  }): Promise<boolean> {
    loading.value = true;
    error.value = null;

    try {
      const res = await categoryService.getAll(params);

      categories.value = res.data;

      currentPage.value = res.meta.current_page;
      lastPage.value = res.meta.last_page;
      total.value = res.meta.total;
      perPage.value = res.meta.per_page;


      return true;
    } catch (err) {
      error.value = err as string;
      return false;
    } finally {
      loading.value = false;
    }
  }

  
  /**
   * Buat category baru.
   */
  async function createCategory(payload: CreateCategoryPayload): Promise<boolean> {
    loading.value = true;
    error.value = null;
    successMessage.value = null;

    try {
      const result = await categoryService.create(payload);
      categories.value.push(result.category);
      successMessage.value = result.message;
      return true;
    } catch (err) {
      error.value = err as string;
      return false;
    } finally {
      loading.value = false;
    }
  }

  /**
   * Update category.
   */
  async function updateCategory(
    id: number,
    payload: UpdateCategoryPayload,
  ): Promise<boolean> {
    loading.value = true;
    error.value = null;
    successMessage.value = null;

    try {
      const result = await categoryService.update(id, payload);
      const updated = result.category;
      successMessage.value = result.message;

      // Update di local list
      const index = categories.value.findIndex((c) => c.id === id);
      if (index !== -1) {
        categories.value[index] = updated;
      }

      // Update currentCategory jika sedang diedit
      if (currentCategory.value?.id === id) {
        currentCategory.value = updated;
      }

      return true;
    } catch (err) {
      error.value = err as string;
      return false;
    } finally {
      loading.value = false;
    }
  }

  /**
   * Hapus category.
   */
  async function deleteCategory(id: number): Promise<boolean> {
    loading.value = true;
    error.value = null;
    successMessage.value = null;

    try {
      successMessage.value = await categoryService.delete(id);

      // Hapus dari list
      categories.value = categories.value.filter((c) => c.id !== id);

      // Clear currentCategory jika yang dihapus sedang dilihat
      if (currentCategory.value?.id === id) {
        currentCategory.value = null;
      }

      return true;
    } catch (err) {
      error.value = err as string;
      return false;
    } finally {
      loading.value = false;
    }
  }

  
  /**
   * Bersihkan error dari state.
   */
  function clearError(): void {
    error.value = null;
  }

  /**
   * Bersihkan semua state.
   */
  function reset(): void {
    categories.value = [];
    currentCategory.value = null;
    loading.value = false;
    error.value = null;
  }

  return {
    currentPage,
    perPage,
    lastPage,
    total,
    // state
    categories,
    currentCategory,
    loading,
    error,
    successMessage,
    // getters
    categoryCount,
    hasCategories,
    // actions
    fetchCategories,
    createCategory,
    updateCategory,
    deleteCategory,
    clearError,
    reset,
  };
});
