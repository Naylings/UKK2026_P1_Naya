// ─────────────────────────────────────────────
// types/auth.ts
// Semua type yang berhubungan dengan auth domain
// ─────────────────────────────────────────────

export interface AuthUser {
  id: number;
  email: string;
  role: 'Admin' | 'Employee' | 'User';
  credit_score: number;
  is_restricted: boolean;
  name: string | null;
  created_at: string | null;
}

export interface LoginPayload {
  email: string;
  password: string;
}

/** Shape inner payload dari BE */
export interface AuthTokenResponse {
  access_token: string;
  token_type: 'bearer';
  expires_in: number;
  user: AuthUser;
}

/** Shape inner payload dari BE saat /me (tidak ada token) */
export interface AuthMeResponse {
  access_token: null;
  token_type: null;
  expires_in: null;
  user: AuthUser;
}

/**
 * Laravel Resource selalu membungkus response dalam { data: ... }
 * Semua response dari BE harus pakai wrapper ini.
 */
export interface LaravelResource<T> {
  data: T;
}

/** Laravel JSON success response dengan optional message */
export interface LaravelApiResponse<T> {
  data: T;
  message?: string;
}

/** Success response sederhana tanpa resource data */
export interface ApiMessageResponse {
  message: string;
}

/** Error response dari BE */
export interface ApiErrorResponse {
  message: string;
  error: string;
}
