import { useLoanStore } from "@/stores/loan";
import { useReturnStore } from "@/stores/return";
import type { LoanResponse } from "@/types/loan";
import { ref } from "vue";

export function useReturnLoan() {
    const returnStore = useReturnStore();
    const loanStore = useLoanStore();

    const selectedLoan = ref<LoanResponse | null>(null);
    const isModalOpen = ref(false);

    const loading = ref(false);
    const error = ref<string | null>(null);
    const successMessage = ref<string | null>(null);

    function openReturnModal(loan: LoanResponse) {
        selectedLoan.value = loan;
        isModalOpen.value = true;
    }

    function closeReturnModal() {
        selectedLoan.value = null;
        isModalOpen.value = false;
        returnStore.reset();
    }

    async function submitReturn(payload: {
        proof: File | null;
        notes: string | null;
    }) {
        if (!selectedLoan.value) return;

        loading.value = true;
        error.value = null;

        try {
            returnStore.proof = payload.proof;
            returnStore.notes = payload.notes;

            const ok = await returnStore.createReturn(selectedLoan.value.id);

            if (ok) {
                successMessage.value = returnStore.successMessage;

                await loanStore.fetchMyLoans();
                closeReturnModal();
            }
        } catch (err: any) {
            error.value = err;
        } finally {
            loading.value = false;
        }
    }

    return {
        isModalOpen,
        selectedLoan,
        loading,
        error,
        successMessage,

        openReturnModal,
        closeReturnModal,
        submitReturn,
    };
}