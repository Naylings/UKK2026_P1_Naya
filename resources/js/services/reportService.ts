
import { AxiosError } from 'axios';
import { reportApi } from '@/api/report';
import type { ReportType, ReportFilters, ReportPreviewResponse, ReportItem } from '@/types/report';
import type { ApiErrorResponse } from '@/types/auth';


export function parseReportError(error: unknown): string {
  if (error instanceof AxiosError) {
    const data = error.response?.data as ApiErrorResponse | undefined;

    if (data?.message) return data.message;

    switch (error.response?.status) {
      case 401:
        return 'Sesi tidak valid. Silakan login ulang.';
      case 403:
        return 'Anda tidak memiliki akses.';
      case 404:
        return 'Data tidak ditemukan.';
      case 422:
        return 'Data tidak valid.';
      case 500:
        return 'Terjadi kesalahan server.';
    }
  }

  return 'Terjadi kesalahan tidak diketahui.';
}


export const reportService = {
  
  async getPreview(
    type: ReportType,
    filters: ReportFilters = {},
  ): Promise<ReportItem[]> {
    try {
      const res = await reportApi.getPreview(type, filters);
      return res.data;
    } catch (error) {
      throw parseReportError(error);
    }
  },

  
  async exportReport(
    type: ReportType,
    filters: ReportFilters = {},
  ): Promise<Blob> {
    try {
      const blob = await reportApi.export(type, filters);
      return blob;
    } catch (error) {
      throw parseReportError(error);
    }
  },

  
  downloadExcel(blob: Blob, filename: string): void {
    const url = window.URL.createObjectURL(blob);
    const link = document.createElement('a');
    link.href = url;
    link.download = filename;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    window.URL.revokeObjectURL(url);
  },
};
