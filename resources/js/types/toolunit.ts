




export type UnitStatus = "available" | "lent" | "nonactive";
export type ConditionType = "good" | "broken" | "maintenance";





export interface UnitTool {
    id: number;
    name: string;
    code_slug: string;
    item_type: string;
}





export interface UnitCondition {
    id: string;
    unit_code: string;
    return_id: number | null;
    conditions: ConditionType;
    notes: string;
    recorded_at: string;
}





export interface ToolUnit {
    code: string;
    tool_id: number;
    tool?: UnitTool;
    status: UnitStatus;
    notes: string | null;
    created_at: string;

    
    
    
    latest_condition?: UnitCondition | null;
    is_available: boolean;
    is_lent: boolean;
    is_nonactive: boolean;
    has_loans: boolean;
}





export interface CreateToolUnitPayload {
    tool_id: number;
    quantity?: number; 
    notes?: string;
    condition?: ConditionType; 
}





export interface UpdateToolUnitPayload {
    status: UnitStatus;
    notes?: string;
}

export interface RecordConditionPayload {
    condition: ConditionType;
    notes?: string;
    return_id?: number | null;
}





export interface ToolUnitResponse {
    data: ToolUnit;
    message?: string;
}

export interface ToolUnitListResponse {
    data: ToolUnit[];
}

export interface PaginatedToolUnitsResponse {
    data: ToolUnit[];
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

export interface BulkCreateToolUnitsResponse {
    data: ToolUnit[];
    message: string;
}

export interface ConditionHistoryResponse {
    data: UnitCondition[];
}





export interface ToolUnitQueryParams {
    per_page?: number;
    tool_id?: number;
    status?: UnitStatus;
    search?: string;
    page?: number;
}
