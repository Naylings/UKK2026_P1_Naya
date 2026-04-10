// ─────────────────────────────────────────────
// types/category.ts
// Semua type yang berhubungan dengan category domain
// ─────────────────────────────────────────────


export interface Category {
  id: number;
  name: string;
  description: string;
}

export interface CreateCategoryPayload {
  name: string;
  description: string;
}

export interface UpdateCategoryPayload {
  id: number;
  name: string;
  description?: string;
}


export interface CategoryListResponse {
  data: Category[];
}

export interface CategoryResponse {
  data: Category;
}

// types/category.ts - ADD this
export interface PaginatedCategoriesResponse {
  data: Category[];
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
