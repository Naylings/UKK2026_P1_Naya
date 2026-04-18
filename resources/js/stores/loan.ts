import { defineStore } from "pinia";
import { ref } from "vue";
import { loanRequestService } from "@/services/loanService";
import type {
    AvailableToolUnit,
    CreateLoanPayload,
    LoanResponse,
    LoanListResponse,
} from "@/types/loan";

export const useLoanStore = defineStore("loan", () => {
    

    const toolId = ref<number | null>(null);
    const loanDate = ref<string | null>(null);
    const dueDate = ref<string | null>(null);

    const availableUnits = ref<AvailableToolUnit[]>([]);
    const selectedUnit = ref<AvailableToolUnit | null>(null);

    const loading = ref(false);
    const error = ref<string | null>(null);
    const successMessage = ref<string | null>(null);

    const reviewLoading = ref(false);
    const reviewError = ref<string | null>(null);
    const reviewSuccess = ref<string | null>(null);

    

    const myLoans = ref<LoanResponse[]>([]);
    const allLoans = ref<LoanResponse[]>([]);

    const meta = ref<LoanListResponse["meta"] | null>(null);

    

    async function searchAvailableUnits() {
        if (!toolId.value || !loanDate.value || !dueDate.value) return;

        loading.value = true;
        error.value = null;

        try {
            availableUnits.value = await loanRequestService.getAvailableUnits({
                tool_id: toolId.value,
                loan_date: loanDate.value,
                due_date: dueDate.value,
            });
        } catch (err: any) {
            error.value = err;
        } finally {
            loading.value = false;
        }
    }

    function selectUnit(unit: AvailableToolUnit) {
        selectedUnit.value = unit;
    }

    function resetLoanFlow() {
        toolId.value = null;
        loanDate.value = null;
        dueDate.value = null;
        availableUnits.value = [];
        selectedUnit.value = null;
        error.value = null;
        successMessage.value = null;
    }

    async function createRequest(payload: CreateLoanPayload): Promise<boolean> {
        loading.value = true;
        error.value = null;
        successMessage.value = null;

        try {
            const res = await loanRequestService.submitLoan(payload);

            successMessage.value = res.message;

            resetLoanFlow();

            return true;
        } catch (err: any) {
            error.value = err;
            return false;
        } finally {
            loading.value = false;
        }
    }

    
    
    

    async function fetchMyLoans(params?: {
        status?: string;
        page?: number;
        per_page?: number;
    }) {
        loading.value = true;
        error.value = null;

        try {
            const res = await loanRequestService.getMyLoans(params);

            myLoans.value = res.data;
            meta.value = res.meta ?? null;

            return true;
        } catch (err: any) {
            error.value = err;
            return false;
        } finally {
            loading.value = false;
        }
    }

    
    
    

    async function fetchAllLoans(params?: {
        status?: string;
        search?: string;
        page?: number;
        per_page?: number;
    }) {
        loading.value = true;
        error.value = null;

        try {
            const res = await loanRequestService.getAllLoans(params);

            allLoans.value = res.data;
            meta.value = res.meta ?? null;

            return true;
        } catch (err: any) {
            error.value = err;
            return false;
        } finally {
            loading.value = false;
        }
    }

    async function approveLoan(
        loanId: number,
        notes?: string,
    ): Promise<boolean> {
        reviewLoading.value = true;
        reviewError.value = null;
        reviewSuccess.value = null;

        try {
            const res = await loanRequestService.approveLoan(loanId, {
                notes,
            });

            reviewSuccess.value = res.message;

            const index = allLoans.value.findIndex((l) => l.id === loanId);
            if (index !== -1) {
                allLoans.value[index] = res.data;
            }

            return true;
        } catch (err: any) {
            reviewError.value = err;
            return false;
        } finally {
            reviewLoading.value = false;
        }
    }

    async function rejectLoan(
        loanId: number,
        notes?: string,
    ): Promise<boolean> {
        reviewLoading.value = true;
        reviewError.value = null;
        reviewSuccess.value = null;

        try {
            const res = await loanRequestService.rejectLoan(loanId, {
                notes,
            });

            reviewSuccess.value = res.message;

            const index = allLoans.value.findIndex((l) => l.id === loanId);
            if (index !== -1) {
                allLoans.value[index] = res.data;
            }

            return true;
        } catch (err: any) {
            reviewError.value = err;
            return false;
        } finally {
            reviewLoading.value = false;
        }
    }

    

    return {
        
        toolId,
        loanDate,
        dueDate,
        availableUnits,
        selectedUnit,

        
        loading,
        error,
        successMessage,

        
        reviewLoading,
        reviewError,
        reviewSuccess,

        
        myLoans,
        allLoans,
        meta,

        
        searchAvailableUnits,
        selectUnit,
        resetLoanFlow,
        createRequest,

        
        fetchMyLoans,
        fetchAllLoans,

         
         approveLoan,
        rejectLoan,

        
        $reset() {
          toolId.value = null;
          loanDate.value = null;
          dueDate.value = null;
          availableUnits.value = [];
          selectedUnit.value = null;
          loading.value = false;
          error.value = null;
          successMessage.value = null;
          reviewLoading.value = false;
          reviewError.value = null;
          reviewSuccess.value = null;
          myLoans.value = [];
          allLoans.value = [];
          meta.value = null;
        },
    };
});
