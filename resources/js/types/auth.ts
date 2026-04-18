




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

export interface AuthTokenResponse {
  access_token: string;
  token_type: 'bearer';
  expires_in: number;
  user: AuthUser;
}

export interface AuthMeResponse {
  access_token: null;
  token_type: null;
  expires_in: null;
  user: AuthUser;
}

export interface LaravelResource<T> {
  data: T;
}

export interface LaravelApiResponse<T> {
  data: T;
  message?: string;
}

export interface ApiMessageResponse {
  message: string;
}

export interface ApiErrorResponse {
  message: string;
  error: string;
}
