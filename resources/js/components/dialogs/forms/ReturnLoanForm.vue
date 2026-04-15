<script setup lang="ts">
import { computed, ref, watch } from "vue";
import { useFormatter } from "@/utils/useFormatter";

const props = defineProps<{
    visible: boolean;
    selectedLoan: any;
    loading: boolean;
}>();

const emit = defineEmits<{
    (e: "update:visible", val: boolean): void;
    (e: "submit", payload: { proof: File | null; notes: string | null }): void;
}>();

const { formatDate } = useFormatter();

const modelVisible = computed({
    get: () => props.visible,
    set: (val) => emit("update:visible", val),
});

const proof = ref<File | null>(null);
const notes = ref<string | null>(null);
const showValidation = ref(false);

// reset saat modal ditutup
watch(
    () => props.visible,
    (val) => {
        if (!val) {
            proof.value = null;
            notes.value = null;
            showValidation.value = false;
        }
    }
);

const handleFileChange = (e: Event) => {
    const target = e.target as HTMLInputElement;
    if (target.files?.length) {
        proof.value = target.files[0];
    }
};

const close = () => {
    modelVisible.value = false;
};

const submit = () => {
    if (!proof.value) {
        showValidation.value = true;
        return;
    }

    emit("submit", {
        proof: proof.value,
        notes: notes.value,
    });
};
</script>

<template>
    <Dialog v-model:visible="modelVisible" modal header="Return Loan" class="w-[30rem]">

        <!-- LOAN INFO -->
        <div v-if="selectedLoan" class="mb-4 p-4 bg-blue-50 rounded-lg">
            <h3 class="font-semibold text-lg mb-1">
                {{ selectedLoan.tool?.name }} ({{ selectedLoan.unit?.code }})
            </h3>
            <p class="text-sm text-gray-600 mb-1">
                {{ selectedLoan.purpose }}
            </p>
            <div class="text-xs text-gray-500">
                Loan: {{ formatDate(selectedLoan.loan_date) }}
                → Due: {{ formatDate(selectedLoan.due_date) }}
            </div>
        </div>

        <!-- FORM -->
        <div class="flex flex-col gap-3">

            <div v-if="showValidation && !proof" class="text-red-500 text-sm">
                Proof wajib diupload
            </div>

            <!-- FILE -->
            <div>
                <label class="block mb-1">Proof *</label>
                <input type="file" @change="handleFileChange" />
            </div>

            <!-- NOTES -->
            <div>
                <label class="block mb-1">Notes</label>
                <textarea
                    v-model="notes"
                    class="w-full border p-2 rounded"
                    rows="3"
                    placeholder="Optional notes..."
                />
            </div>

            <!-- ACTION -->
            <div class="flex justify-end gap-2 mt-4">
                <Button
                    label="Cancel"
                    severity="secondary"
                    @click="close"
                />

                <Button
                    label="Submit Return"
                    :loading="loading"
                    @click="submit"
                />
            </div>
        </div>

    </Dialog>
</template>