<script setup lang="ts">
import { computed } from "vue";

const props = defineProps<{
    visible: boolean;
    detailLoan: any;
}>();

const emit = defineEmits(["update:visible"]);

const modelVisible = computed({
    get: () => props.visible,
    set: (val) => emit("update:visible", val),
});

const close = () => {
    modelVisible.value = false;
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
        header="Detail Peminjaman"
        style="width: 800px"
    >
        <div v-if="detailLoan" class="space-y-5">

            <!-- 🔹 HEADER + STATUS -->
            <div class="p-5 rounded-xl border bg-gray-50 flex justify-between items-start">
                <div>
                    <p class="text-sm text-gray-500">Tujuan</p>
                    <p class="text-xl font-semibold">
                        {{ detailLoan.purpose }}
                    </p>

                    <p class="text-xs text-gray-400 mt-1">
                        Dibuat: {{ formatDate(detailLoan.created_at) }}
                    </p>
                </div>

                <!-- STATUS BADGE -->
                <span
                    class="px-3 py-1 rounded-full text-xs font-semibold capitalize"
                    :class="{
                        'bg-yellow-100 text-yellow-700': detailLoan.status === 'pending',
                        'bg-green-100 text-green-700': detailLoan.status === 'approve',
                        'bg-red-100 text-red-700': detailLoan.status === 'rejected',
                        'bg-gray-200 text-gray-700': detailLoan.status === 'expired',
                    }"
                >
                    {{ detailLoan.status }}
                </span>
            </div>

            <!-- 🔹 TIMELINE -->
            <div class="grid grid-cols-2 gap-4">
                <div class="p-4 border rounded-lg">
                    <p class="text-gray-500 text-xs">Tanggal Pinjam</p>
                    <p class="font-medium">
                        {{ formatDate(detailLoan.loan_date) }}
                    </p>
                </div>

                <div class="p-4 border rounded-lg">
                    <p class="text-gray-500 text-xs">Jatuh Tempo</p>
                    <p class="font-medium">
                        {{ formatDate(detailLoan.due_date) }}
                    </p>
                </div>
            </div>

            <!-- 🔹 PEMINJAM -->
            <div class="p-4 border rounded-lg">
                <p class="font-semibold mb-3">Data Peminjam</p>

                <div class="grid grid-cols-2 gap-3 text-sm">
                    <p><span class="text-gray-500">Nama:</span> {{ detailLoan.user?.details?.name }}</p>
                    <p><span class="text-gray-500">NIK:</span> {{ detailLoan.user?.details?.nik }}</p>
                    <p><span class="text-gray-500">No HP:</span> {{ detailLoan.user?.details?.no_hp }}</p>
                    <p><span class="text-gray-500">Alamat:</span> {{ detailLoan.user?.details?.address }}</p>
                </div>
            </div>

            <!-- 🔹 BARANG -->
            <div class="p-4 border rounded-lg">
                <p class="font-semibold mb-3">Detail Barang</p>

                <div class="grid grid-cols-2 gap-3 text-sm">
                    <p><span class="text-gray-500">Nama:</span> {{ detailLoan.tool?.name }}</p>
                    <p><span class="text-gray-500">Kode Unit:</span> {{ detailLoan.unit?.code }}</p>
                    <p>
                        <span class="text-gray-500">Status Unit:</span>
                        <span class="capitalize">{{ detailLoan.unit?.status }}</span>
                    </p>
                </div>
            </div>

            <!-- 🔹 REVIEW PETUGAS -->
            <div class="p-4 border rounded-lg">
                <p class="font-semibold mb-3">Review Petugas</p>

                <div v-if="detailLoan.review?.employee" class="space-y-2 text-sm">
                    <p><span class="text-gray-500">Nama:</span> {{ detailLoan.review.employee.details?.name }}</p>
                    <p><span class="text-gray-500">Email:</span> {{ detailLoan.review.employee.email }}</p>
                    <p><span class="text-gray-500">No HP:</span> {{ detailLoan.review.employee.details?.no_hp }}</p>
                    <p>
                        <span class="text-gray-500">Catatan:</span>
                        <span class="text-gray-700">
                            {{ detailLoan.review.notes || "-" }}
                        </span>
                    </p>
                </div>

                <div v-else class="text-sm text-gray-400">
                    Belum direview
                </div>
            </div>
        </div>

        <template #footer>
            <Button label="Tutup" severity="secondary" @click="close" />
        </template>
    </Dialog>
</template>