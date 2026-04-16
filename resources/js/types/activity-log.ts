export interface ActivityLogUser {
  id: number;
  email: string;
  role?: string;
  details?: {
    name: string;
  } | null;
}

export interface ActivityLogItem {
  id: number;
  action: string;
  module: string;
  description: string;
  meta?: Record<string, unknown> | null;
  ip_address?: string | null;
  created_at: string;
  user?: ActivityLogUser | null;
}

export interface ActivityLogResponse {
  data: ActivityLogItem;
}

export interface PaginatedActivityLogResponse {
  data: ActivityLogItem[];
  meta: {
    current_page: number;
    last_page: number;
    total: number;
    per_page: number;
  };
}
