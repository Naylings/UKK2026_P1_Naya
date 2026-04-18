




export type ItemType = "single" | "bundle" | "bundle_tool";

export interface Category {
  id: number;
  name: string;
}

export interface BundleComponentTool {
  id: number;
  name: string;
  code_slug: string;
  item_type: ItemType;
  price: number;
  description: string | null;
  photo_path: string | null;
  category_id: number;
  min_credit_score: number;
}

export interface BundleComponent {
  id: number;
  tool_id: number;
  qty: number;
  tool: BundleComponentTool | null;
}

export interface Tool {
  id: number;
  category_id: number;
  category?: Category;
  name: string;
  item_type: ItemType;
  price: number;
  min_credit_score: number;
  description: string | null;
  code_slug: string;
  photo_path: string | null;
  bundle_components?: BundleComponent[];
  created_at: string;

  
  
  
  has_units: boolean;
  units_count: number;
  available_units: number;
  borrowed_units: number;
  nonactive_units: number;
  has_loans: boolean;
  has_bundles: boolean;
  can_delete: boolean;
}






export interface CreateToolPayloadFormData extends Omit<
  CreateToolPayload,
  "photo_path"
> {
  photo?: File; 
}


export interface BundleComponentPayload {
  name: string;
  price: number;
  qty: number;
  description?: string | null;
  photo?: File; 
  photo_path?: string | null; 
}

export interface CreateToolPayload {
  category_id: number;
  name: string;
  item_type: ItemType;
  price: number;
  min_credit_score: number;
  description?: string | null;
  code_slug: string;
  photo_path?: string | null;
  bundle_components?: BundleComponentPayload[] | null;
}





export interface UpdateBundleComponentPayload {
  name?: string;
  price?: number;
  qty?: number;
  description?: string | null;
  photo_path?: string | null;
  category_id?: number | null;
  min_credit_score?: number | null;
  code_slug?: string | null;
}

export interface UpdateToolPayload {
  id: number;
  category_id?: number;
  name?: string;
  item_type?: ItemType;
  price?: number;
  min_credit_score?: number;
  description?: string | null;
  code_slug?: string;
  photo_path?: string | null;
  bundle_components?: BundleComponentPayload[] | null;
}





export interface ToolResponse {
  data: Tool;
  message?: string;
}

export interface ToolListResponse {
  data: Tool[];
}

export interface PaginatedToolsResponse {
  data: Tool[];
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





export interface ToolQueryParams {
  per_page?: number;
  search?: string;
  category?: string | number;
}
