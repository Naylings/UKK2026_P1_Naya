
import { defineStore } from 'pinia';
import { computed, ref } from 'vue';
import { authService } from '@/services/authService';
import type { AuthUser, LoginPayload } from '@/types/auth';

export const useAuthStore = defineStore('auth', () => {

  const user    = ref<AuthUser | null>(null);
  const loading = ref(false);
  const error   = ref<string | null>(null);
  const loginMessage = ref<string | null>(null);
  const logoutMessage = ref<string | null>(null);

  
  const initialized = ref(false);


  const isLoggedIn   = computed(() => !!user.value);
  const isAdmin      = computed(() => user.value?.role === 'Admin');
  const isEmployee   = computed(() => user.value?.role === 'Employee');


  
  async function login(payload: LoginPayload): Promise<boolean> {
    loading.value = true;
    error.value   = null;

    try {
      const result = await authService.login(payload);
      user.value = result.user;
      loginMessage.value = result.message;
      return true;
    } catch (err) {
      error.value = err as string;
      return false;
    } finally {
      loading.value = false;
    }
  }

  
  async function logout(): Promise<string> {
    loading.value = true;

    try {
      const message = await authService.logout();
      logoutMessage.value = message;
      return message;
    } finally {
      user.value        = null;
      initialized.value = false;
      loading.value     = false;
      error.value       = null;
    }
  }

  
  async function fetchMe(): Promise<void> {
    if (initialized.value) return;

    loading.value = true;

    try {
      user.value = await authService.me();
    } finally {
      initialized.value = true;
      loading.value     = false;
    }
  }

  function clearSession(): void {
    user.value        = null;
    initialized.value = false;
    error.value       = null;
  }

  function clearError(): void {
    error.value = null;
  }

  return {
    user,
    loading,
    error,
    loginMessage,
    initialized,
    logoutMessage,
    isLoggedIn,
    isAdmin,
    isEmployee,
    login,
    logout,
    fetchMe,
    clearSession,
    clearError,
    $reset() {
      user.value = null;
      loading.value = false;
      error.value = null;
      loginMessage.value = null;
      logoutMessage.value = null;
      initialized.value = false;
    },
  };
});

