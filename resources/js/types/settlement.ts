// ─────────────────────────────────────────────
// types/settlement.ts
// Semua type terkait settlement (pelunasan)
// ─────────────────────────────────────────────

import type { User } from "./user";

export interface Settlement {
  id: number;
  settled_at: string;
  description: string;

  // Petugas yang mencatat pelunasan
  employee: User;

  // Relasi ke violation (opsional tergantung endpoint)
  violation?: {
    id: number;
    type: "late" | "damaged" | "lost" | "other";
    total_score?: number;
    fine: number;
    status: "active" | "settled";

    // user yang melanggar (penting untuk table/search)
    user?: {
      id: number;
      details?: {
        name: string;
      };
    };
  };
}
export interface CreateSettlementPayload {
  description: string;
}
export interface SettlementResponse {
  data: Settlement;
  message?: string;
}
export interface PaginatedSettlementResponse {
  data: Settlement[];
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
