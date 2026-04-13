// ─────────────────────────────────────────────
// types/appconfig.ts
// Semua type yang berhubungan dengan app config domain
// ─────────────────────────────────────────────

export interface AppConfig {
  id: number;
  name: string;
  late_point: number;
  broken_point: number;
  lost_point: number;
  late_fine: number;
  broken_fine: number;
  lost_fine: number;
  updated_at: string;
}

export interface UpdateAppConfigPayload {
  name: string;
  late_point: number;
  broken_point: number;
  lost_point: number;
  late_fine: number;
  broken_fine: number;
  lost_fine: number;
}

export interface AppConfigResponse {
  data: AppConfig;
  message?: string;
}
