// ─────────────────────────────────────────────
// api/auth.ts
// Pure HTTP calls untuk auth — tidak ada side effect
// ─────────────────────────────────────────────

import apiClient from './client';
import type {
  AuthMeResponse,
  AuthTokenResponse,
  LaravelResource,
  LoginPayload,
} from '@/types/auth';

export const authApi = {
    
  login: async (payload: LoginPayload): Promise<AuthTokenResponse> => {
    const res = await apiClient.post<LaravelResource<AuthTokenResponse>>('/auth/login', payload);
    return res.data.data;
  },

  /**
   * POST /api/auth/logout
   */
  logout: () =>
    apiClient.post<{ message: string }>('/auth/logout'),

  /**
   * POST /api/auth/refresh
   */
  refresh: async (): Promise<AuthTokenResponse> => {
    const res = await apiClient.post<LaravelResource<AuthTokenResponse>>('/auth/refresh');
    return res.data.data;
  },

  /**
   * GET /api/auth/me
   */
  me: async (): Promise<AuthMeResponse> => {
    const res = await apiClient.get<LaravelResource<AuthMeResponse>>('/auth/me');
    return res.data.data;
  },
};