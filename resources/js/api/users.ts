
import apiClient from "./client";
import type {
  User,
  UserResponse,
  CreateUserPayload,
  UpdateUserPayload,
  UpdateUserCreditPayload,
  PaginatedUsersResponse,
} from "@/types/user";
import type { ApiMessageResponse, LaravelApiResponse } from "@/types/auth";

export const usersApi = {
  
  list: async (params?: {
    search?: string;
    role?: string;
    per_page?: number;
    page?: number;
  }): Promise<PaginatedUsersResponse> => {
    const res = await apiClient.get<PaginatedUsersResponse>("/users", { params });
    return res.data;
  },

  
  get: async (id: number): Promise<User> => {
    const res = await apiClient.get<UserResponse>(`/users/${id}`);
    return res.data.data;
  },

  
  create: async (
    payload: CreateUserPayload,
  ): Promise<LaravelApiResponse<User>> => {
    const res = await apiClient.post<LaravelApiResponse<User>>(
      "/users",
      payload,
    );
    return res.data;
  },

  
  update: async (
    id: number,
    payload: UpdateUserPayload,
  ): Promise<LaravelApiResponse<User>> => {
    const res = await apiClient.put<LaravelApiResponse<User>>(
      `/users/${id}`,
      payload,
    );
    return res.data;
  },

  
  delete: async (id: number): Promise<string> => {
    const res = await apiClient.delete<ApiMessageResponse>(`/users/${id}`);
    return res.data.message;
  },

  
  updateCredit: async (
    id: number,
    payload: UpdateUserCreditPayload,
  ): Promise<User> => {
    const res = await apiClient.post<LaravelApiResponse<User>>(
      `/users/${id}/credit`,
      payload,
    );
    return res.data.data;
  },
};
