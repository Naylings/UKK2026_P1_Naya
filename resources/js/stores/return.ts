import { returnService } from "@/services/returnService";
import type { ReturnResponse } from "@/types/return";
import { defineStore } from "pinia";
import { ref } from "vue";

export const useReturnStore = defineStore("return", () => {

    const proof = ref<File | null>(null);
    const notes = ref<string | null>(null);

    const loading = ref(false);
    const error = ref<string | null>(null);
    const successMessage = ref<string | null>(null);

    const returns = ref<ReturnResponse[]>([]);
    const meta = ref<any>(null);

    function reset() {
        proof.value = null;
        notes.value = null;
        error.value = null;
        successMessage.value = null;
    }

    async function createReturn(loanId: number): Promise<boolean> {
        loading.value = true;
        error.value = null;

        try {
            const res = await returnService.createReturn(loanId, {
                proof: proof.value,
                notes: notes.value,
            });

            successMessage.value = res.message;

            reset();
            return true;

        } catch (err: any) {
            error.value = err;
            return false;

        } finally {
            loading.value = false;
        }
    }

    async function fetchReturns(params?: any) {
        loading.value = true;
        error.value = null;

        try {
            const res = await returnService.getReturns(params);

            returns.value = res.data;
            meta.value = res.meta ?? null;

            return true;

        } catch (err: any) {
            error.value = err;
            return false;

        } finally {
            loading.value = false;
        }
    }

    return {
        proof,
        notes,
        loading,
        error,
        successMessage,
        returns,
        meta,
        reset,
        createReturn,
        fetchReturns,
    };
});