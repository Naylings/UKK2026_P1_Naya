// ─────────────────────────────────────────────
// types/user.ts
// Semua type yang berhubungan dengan user domain
// ─────────────────────────────────────────────

export interface UserDetail {
  nik: string;
  name: string;
  no_hp: string;
  address: string;
  birth_date: string;
}

export interface User {
  id: number;
  email: string;
  role: 'Admin' | 'Employee' | 'User';
  credit_score: number;
  is_restricted: boolean;
  created_at: string;
  updated_at: string;
  details: UserDetail | null;
}

export interface CreateUserPayload {
  email: string;
  password: string;
  role: 'Admin' | 'Employee' | 'User';
  nik: string;
  name: string;
  no_hp: string;
  address: string;
  birth_date: string;
}

export interface UpdateUserPayload {
  id: number;
  role?: 'Admin' | 'Employee' | 'User';
  nik?: string;
  name?: string;
  no_hp?: string;
  address?: string;
  birth_date?: string;
}

export interface UpdateUserCreditPayload {
  credit: number;
}

export interface UserListResponse {
  data: User[];
}

export interface UserResponse {
  data: User;
}

// types/user.ts - ADD this
export interface PaginatedUsersResponse {
  data: User[];
  meta: {
    current_page: number;
    from: number;
    last_page: number;
    per_page: number;
    to: number;
    total: number;
  };
  links: {
    first: string;
    last: string;
    next: string | null;
    prev: string | null;
  };
}
