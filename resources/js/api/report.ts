
import apiClient from './client';
import type { ReportType, ReportFilters, ReportPreviewResponse } from '@/types/report';

export const reportApi = {
  
  getPreview: async (
    type: ReportType,
    filters: ReportFilters = {},
  ): Promise<ReportPreviewResponse> => {
    const res = await apiClient.get<ReportPreviewResponse>(
      `/reports/${type}/preview`,
      { params: filters },
    );
    return res.data;
  },

  
  export: async (
    type: ReportType,
    filters: ReportFilters = {},
  ): Promise<Blob> => {
    const res = await apiClient.get<Blob>(
      `/reports/${type}/export`,
      {
        params: filters,
        responseType: 'blob',
      },
    );
    return res.data;
  },
};
