<script setup lang="ts">
import { ref, watch, computed } from "vue";

const props = defineProps<{
    visible: boolean;
    selectedLoan: any;
    reviewLoading: boolean;
}>();

const emit = defineEmits([
    "update:visible",
    "approve",
    "reject",
]);

const modelVisible = computed({
    get: () => props.visible,
    set: (val) => emit("update:visible", val),
});

const reviewNote = ref("");
const showValidation = ref(false);

watch(
    () => props.visible,
    (val) => {
        if (!val) {
            reviewNote.value = "";
            showValidation.value = false;
        }
    }
);

const close = () => {
    modelVisible.value = false;
};

const approve = () => {
    emit("approve", reviewNote.value);
};

const reject = () => {
    if (!reviewNote.value) {
        showValidation.value = true;
        return;
    }
    emit("reject", reviewNote.value);
};

const formatDate = (date: string) => {
    return new Date(date).toLocaleString("id-ID", {
        dateStyle: "medium",
        timeStyle: "short",
    });
};
</script>

<template>
    <Dialog
        v-model:visible="modelVisible"
        modal
        header="Review Peminjaman"
        class="w-[700px]"
    >
        <div v-if="selectedLoan" class="space-y-5">

            <!-- 🔹 HEADER -->
            <div class="p-4 border rounded-lg bg-gray-50 space-y-3">
                <div>
                    <p class="text-xs text-gray-500">Tujuan</p>
                    <p class="font-semibold text-lg">
                        {{ selectedLoan.purpose }}
                    </p>
                </div>

                <!-- STATUS -->
                <span
                    class="px-3 py-1 rounded-full text-xs font-semibold capitalize"
                    :class="{
                        'bg-yellow-100 text-yellow-700': selectedLoan.status === 'pending',
                        'bg-green-100 text-green-700': selectedLoan.status === 'approve',
                        'bg-red-100 text-red-700': selectedLoan.status === 'rejected',
                        'bg-gray-200 text-gray-700': selectedLoan.status === 'expired',
                    }"
                >
                    {{ selectedLoan.status }}
                </span>
            </div>

            <!-- 🔹 TIMELINE -->
            <div class="grid grid-cols-2 gap-3 text-sm">
                <div class="p-3 border rounded-lg">
                    <p class="text-gray-500 text-xs">Tanggal Pinjam</p>
                    <p class="font-medium">
                        {{ formatDate(selectedLoan.loan_date) }}
                    </p>
                </div>

                <div class="p-3 border rounded-lg">
                    <p class="text-gray-500 text-xs">Jatuh Tempo</p>
                    <p class="font-medium">
                        {{ formatDate(selectedLoan.due_date) }}
                    </p>
                </div>
            </div>

            <!-- 🔹 PEMINJAM -->
            <div class="p-4 border rounded-lg">
                <p class="font-semibold mb-2">Peminjam</p>
                <div class="grid grid-cols-2 gap-2 text-sm">
                    <p><span class="text-gray-500">Nama:</span> {{ selectedLoan.user?.details?.name }}</p>
                    <p><span class="text-gray-500">NIK:</span> {{ selectedLoan.user?.details?.nik }}</p>
                    <p><span class="text-gray-500">No HP:</span> {{ selectedLoan.user?.details?.no_hp }}</p>
                    <p><span class="text-gray-500">Unit:</span> {{ selectedLoan.unit?.code }}</p>
                </div>
            </div>

            <!-- 🔹 BARANG -->
            <div class="p-4 border rounded-lg">
                <p class="font-semibold mb-2">Barang</p>
                <div class="text-sm space-y-1">
                    <p><span class="text-gray-500">Nama:</span> {{ selectedLoan.tool?.name }}</p>
                    <p><span class="text-gray-500">Status Unit:</span> {{ selectedLoan.unit?.status }}</p>
                </div>
            </div>

            <!-- 🔹 WARNING / INFO -->
            <div
                class="p-3 border rounded-lg text-sm"
                :class="{
                    'bg-yellow-50 border-yellow-300': selectedLoan.status === 'pending',
                    'bg-red-50 border-red-300': selectedLoan.status === 'expired',
                }"
            >
                <p v-if="selectedLoan.status === 'pending'">
                    Peminjaman menunggu persetujuan Anda.
                </p>
                <p v-else-if="selectedLoan.status === 'expired'">
                    Peminjaman sudah melewati batas waktu.
                </p>
                <p v-else>
                    Pastikan data sebelum melakukan aksi.
                </p>
            </div>

            <!-- 🔹 NOTE -->
            <div>
                <label class="text-sm font-medium">
                    Catatan Review <span class="text-red-500">*</span>
                </label>

                <Textarea
                    v-model="reviewNote"
                    rows="4"
                    class="w-full mt-2"
                    placeholder="Wajib diisi jika menolak..."
                    :class="{ 'border-red-400': !reviewNote && showValidation }"
                />

                <p class="text-xs text-gray-400 mt-1">
                    Catatan akan dikirim ke peminjam sebagai feedback
                </p>
            </div>
        </div>

        <template #footer>
            <div class="flex justify-between w-full">
                <Button
                    label="Tolak"
                    severity="danger"
                    icon="pi pi-times"
                    :loading="reviewLoading"
                    @click="reject"
                />

                <div class="flex gap-2">
                    <Button label="Tutup" severity="secondary" @click="close" />

                    <Button
                        label="Setujui"
                        icon="pi pi-check"
                        severity="success"
                        :loading="reviewLoading"
                        @click="approve"
                    />
                </div>
            </div>
        </template>
    </Dialog>
</template>