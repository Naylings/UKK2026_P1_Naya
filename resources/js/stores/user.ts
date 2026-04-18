




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
  

  const users = ref<User[]>([]);
  const currentUser = ref<User | null>(null);
  const loading = ref(false);
  const error = ref<string | null>(null);
  const successMessage = ref<string | null>(null);

  

  const currentPage = ref(1);
  const lastPage = ref(1);
  const total = ref(0);
  const perPage = ref(10);

  

  const userCount = computed(() => users.value.length);
  const hasUsers = computed(() => users.value.length > 0);

  

  
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

  
  async function createUser(payload: CreateUserPayload): Promise<boolean> {
    loading.value = true;
    error.value = null;
    successMessage.value = null;

    try {
      const result = await userService.create(payload);
      users.value.push(result.user);
      successMessage.value = result.message;
      return true;
    } catch (err) {
      error.value = err as string;
      return false;
    } finally {
      loading.value = false;
    }
  }

  
  async function updateUser(
    id: number,
    payload: UpdateUserPayload,
  ): Promise<boolean> {
    loading.value = true;
    error.value = null;
    successMessage.value = null;

    try {
      const result = await userService.update(id, payload);
      const updated = result.user;
      successMessage.value = result.message;

      
      const index = users.value.findIndex((u) => u.id === id);
      if (index !== -1) {
        users.value[index] = updated;
      }

      
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

  
  async function deleteUser(id: number): Promise<boolean> {
    loading.value = true;
    error.value = null;
    successMessage.value = null;

    try {
      successMessage.value = await userService.delete(id);

      
      users.value = users.value.filter((u) => u.id !== id);

      
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

  
  async function updateUserCredit(
    id: number,
    payload: UpdateUserCreditPayload,
  ): Promise<boolean> {
    loading.value = true;
    error.value = null;
    successMessage.value = null;

    try {
      const updated = await userService.updateCredit(id, payload);
      successMessage.value = "Credit berhasil diupdate.";

      
      const index = users.value.findIndex((u) => u.id === id);
      if (index !== -1) {
        users.value[index] = updated;
      }

      
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

  
  function clearError(): void {
    error.value = null;
  }

  
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
    
    users,
    currentUser,
    loading,
    error,
    successMessage,
    
    userCount,
    hasUsers,
    
    fetchUsers,
    fetchUserById,
    createUser,
    updateUser,
    deleteUser,
    updateUserCredit,
    clearError,
    reset,

    
    $reset() {
      users.value = [];
      currentUser.value = null;
      loading.value = false;
      error.value = null;
      successMessage.value = null;
      currentPage.value = 1;
      lastPage.value = 1;
      total.value = 0;
      perPage.value = 10;
    },
  };
});
