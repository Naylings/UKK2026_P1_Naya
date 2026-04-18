
import { AxiosError } from 'axios';
import { authApi } from '@/api/auth';
import { tokenStorage } from '@/api/client';
import type {
  ApiErrorResponse,
  AuthUser,
  LoginPayload,
} from '@/types/auth';

interface LoginResult {
  user: AuthUser;
  message: string;
}



export function parseAuthError(error: unknown): string {
  if (error instanceof AxiosError) {
    const data = error.response?.data as ApiErrorResponse | undefined;

    if (data?.message) return data.message;

    switch (error.response?.status) {
      case 401: return 'Sesi tidak valid. Silakan login ulang.';
      case 403: return 'Akun Anda sedang dibatasi.';
      case 422: return 'Data yang dikirim tidak valid.';
      case 500: return 'Terjadi kesalahan pada server. Coba lagi nanti.';
    }
  }

  return 'Terjadi kesalahan tidak diketahui.';
}


export const authService = {
  
  async login(payload: LoginPayload): Promise<LoginResult> {
    try {
      const result = await authApi.login(payload);
      tokenStorage.set(result.data.access_token);
      return {
        user: result.data.user,
        message: result.message ?? 'Login berhasil.',
      };
    } catch (error) {
      throw parseAuthError(error);
    }
  },

  
  async logout(): Promise<string> {
    let message = 'Logout berhasil';
    try {
      const response = await authApi.logout();
      if (response.data.message) {
        message = response.data.message;
      }
    } catch {
    } finally {
      tokenStorage.clear();
    }
    return message;
  },

  
  async refresh(): Promise<AuthUser> {
    try {
      const result = await authApi.refresh();
      tokenStorage.set(result.access_token);
      return result.user;
    } catch (error) {
      tokenStorage.clear();
      throw parseAuthError(error);
    }
  },

  async me(): Promise<AuthUser | null> {
    if (!tokenStorage.get()) return null;

    try {
      const result = await authApi.me();
      return result.user;
    } catch {
      tokenStorage.clear();
      return null;
    }
  },

  hasToken: (): boolean => !!tokenStorage.get(),
};
