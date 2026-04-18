
import apiClient from "./client";
import type {
  AppealFilters,
  AppealListResponse,
  CreateAppealPayload,
  CreateAppealResponse,
  ReviewAppealPayload,
  ReviewAppealResponse,
} from '@/types/appeal';


export const appealApi = {
  async createAppeal(payload: CreateAppealPayload): Promise<CreateAppealResponse> {
    const response = await apiClient.post('/appeals', payload);
    return response.data;
  },

  async getAll(filters?: AppealFilters): Promise<AppealListResponse> {
    const params = new URLSearchParams();
    if (filters?.status) params.append('status', filters.status);
    if (filters?.search) params.append('search', filters.search);
    if (filters?.page) params.append('page', filters.page.toString());
    if (filters?.per_page) params.append('per_page', filters.per_page.toString());

    const response = await apiClient.get(`/appeals?${params}`);
    return response.data;
  },

  async getMy(filters?: AppealFilters): Promise<AppealListResponse> {
    const params = new URLSearchParams();
    if (filters?.status) params.append('status', filters.status);
    if (filters?.search) params.append('search', filters.search);
    if (filters?.page) params.append('page', filters.page.toString());
    if (filters?.per_page) params.append('per_page', filters.per_page.toString());

    const response = await apiClient.get(`/appeals/my?${params}`);
    return response.data;
  },

  async reviewAppeal(id: number, payload: ReviewAppealPayload): Promise<ReviewAppealResponse> {
    const response = await apiClient.patch(`/appeals/${id}/review`, payload);
    return response.data;
  },
};

