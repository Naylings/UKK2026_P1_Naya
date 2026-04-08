// ─────────────────────────────────────────────
// stores/user.ts
// Pinia store untuk user management — state, getters, actions
// ─────────────────────────────────────────────

import { defineStore } from "pinia";
import { computed, ref } from "vue";
import { userService } from "@/services/userService";
import type {
  User,
  CreateUserPayload,
  UpdateUserPayload,
  UpdateUserCreditPayload,
} from "@/types/user";

export const useUserStore = defineStore("user", () => {
  // ── State ──────────────────────────────────────────────────────────────

  const users = ref<User[]>([]);
  const currentUser = ref<User | null>(null);
  const loading = ref(false);
  const error = ref<string | null>(null);

  // ── Pagination ────────────────────────────────────────────────────────────

  const currentPage = ref(1);
  const lastPage = ref(1);
  const total = ref(0);
  const perPage = ref(10);

  // ── Getters ────────────────────────────────────────────────────────────

  const userCount = computed(() => users.value.length);
  const hasUsers = computed(() => users.value.length > 0);

  // ── Actions ────────────────────────────────────────────────────────────

  /**
   * Ambil list semua user.
   */
  async function fetchUsers(params?: {
    search?: string;
    role?: string;
    per_page?: number;
    page?: number;
  }): Promise<boolean> {
    loading.value = true;
    error.value = null;

    try {
      const res = await userService.getAll(params);

      users.value = res.data;

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
   * Ambil detail user tertentu.
   */
  async function fetchUserById(id: number): Promise<boolean> {
    loading.value = true;
    error.value = null;

    try {
      currentUser.value = await userService.getById(id);
      return true;
    } catch (err) {
      error.value = err as string;
      return false;
    } finally {
      loading.value = false;
    }
  }

  /**
   * Buat user baru.
   */
  async function createUser(payload: CreateUserPayload): Promise<boolean> {
    loading.value = true;
    error.value = null;

    try {
      const newUser = await userService.create(payload);
      users.value.push(newUser);
      return true;
    } catch (err) {
      error.value = err as string;
      return false;
    } finally {
      loading.value = false;
    }
  }

  /**
   * Update user.
   */
  async function updateUser(
    id: number,
    payload: UpdateUserPayload,
  ): Promise<boolean> {
    loading.value = true;
    error.value = null;

    try {
      const updated = await userService.update(id, payload);

      // Update di local list
      const index = users.value.findIndex((u) => u.id === id);
      if (index !== -1) {
        users.value[index] = updated;
      }

      // Update currentUser jika sedang diedit
      if (currentUser.value?.id === id) {
        currentUser.value = updated;
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
   * Hapus user.
   */
  async function deleteUser(id: number): Promise<boolean> {
    loading.value = true;
    error.value = null;

    try {
      await userService.delete(id);

      // Hapus dari list
      users.value = users.value.filter((u) => u.id !== id);

      // Clear currentUser jika yang dihapus sedang dilihat
      if (currentUser.value?.id === id) {
        currentUser.value = null;
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
   * Update credit user.
   */
  async function updateUserCredit(
    id: number,
    payload: UpdateUserCreditPayload,
  ): Promise<boolean> {
    loading.value = true;
    error.value = null;

    try {
      const updated = await userService.updateCredit(id, payload);

      // Update di local list
      const index = users.value.findIndex((u) => u.id === id);
      if (index !== -1) {
        users.value[index] = updated;
      }

      // Update currentUser jika sedang diedit
      if (currentUser.value?.id === id) {
        currentUser.value = updated;
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
    users.value = [];
    currentUser.value = null;
    loading.value = false;
    error.value = null;
  }

  return {
    currentPage,
    perPage,
    lastPage,
    total,
    // state
    users,
    currentUser,
    loading,
    error,
    // getters
    userCount,
    hasUsers,
    // actions
    fetchUsers,
    fetchUserById,
    createUser,
    updateUser,
    deleteUser,
    updateUserCredit,
    clearError,
    reset,
  };
});
