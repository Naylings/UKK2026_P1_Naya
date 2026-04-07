// ─────────────────────────────────────────────
// api/client.ts
// Axios instance terpusat — satu-satunya tempat config HTTP
// ─────────────────────────────────────────────

import axios, { type AxiosError, type InternalAxiosRequestConfig } from 'axios';
import type { ApiErrorResponse } from '@/types/auth';

const TOKEN_KEY = 'access_token';

// ── Instance ──────────────────────────────────────────────────────────────

const apiClient = axios.create({
  /**
   * Laravel SPA: Vue dan Laravel satu domain → baseURL cukup '/api'.
   * Jika berbeda domain, ganti dengan full URL: 'https://api.example.com/api'
   */
  baseURL: '/api',
  headers: {
    Accept: 'application/json',
    'Content-Type': 'application/json',
  },
});

// ── Request interceptor — inject token ────────────────────────────────────

apiClient.interceptors.request.use(
  (config: InternalAxiosRequestConfig) => {
    const token = localStorage.getItem(TOKEN_KEY);
    if (token) {
      config.headers.Authorization = `Bearer ${token}`;
    }
    return config;
  },
  (error) => Promise.reject(error),
);

// ── Response interceptor — handle 401 global ──────────────────────────────

apiClient.interceptors.response.use(
  (response) => response,
  (error: AxiosError<ApiErrorResponse>) => {
    const status = error.response?.status;

    if (status === 401) {
      // Token expired / tidak valid → bersihkan storage dan redirect ke login
      localStorage.removeItem(TOKEN_KEY);

      // Hindari import circular dengan useAuthStore — pakai event bus sederhana
      window.dispatchEvent(new CustomEvent('auth:unauthenticated'));
    }

    return Promise.reject(error);
  },
);

// ── Token helpers (dipakai oleh authService) ──────────────────────────────

export const tokenStorage = {
  get: (): string | null => localStorage.getItem(TOKEN_KEY),
  set: (token: string): void => localStorage.setItem(TOKEN_KEY, token),
  clear: (): void => localStorage.removeItem(TOKEN_KEY),
};

export default apiClient;