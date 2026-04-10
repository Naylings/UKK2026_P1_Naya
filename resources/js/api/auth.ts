// ─────────────────────────────────────────────
// api/auth.ts
// Pure HTTP calls untuk auth — tidak ada side effect
// ─────────────────────────────────────────────

import apiClient from './client';
import type {
  ApiMessageResponse,
  AuthMeResponse,
  AuthTokenResponse,
  LaravelApiResponse,
  LoginPayload,
} from '@/types/auth';

export const authApi = {
    
  login: async (payload: LoginPayload): Promise<LaravelApiResponse<AuthTokenResponse>> => {
    const res = await apiClient.post<LaravelApiResponse<AuthTokenResponse>>('/auth/login', payload);
    return res.data;
  },

  /**
   * POST /api/auth/logout
   */
  logout: () =>
    apiClient.post<ApiMessageResponse>('/auth/logout'),

  /**
   * POST /api/auth/refresh
   */
  refresh: async (): Promise<AuthTokenResponse> => {
    const res = await apiClient.post<LaravelApiResponse<AuthTokenResponse>>('/auth/refresh');
    return res.data.data;
  },

  /**
   * GET /api/auth/me
   */
  me: async (): Promise<AuthMeResponse> => {
    const res = await apiClient.get<LaravelApiResponse<AuthMeResponse>>('/auth/me');
    return res.data.data;
  },
};
