<script setup lang="ts">
import { computed } from "vue";
import { useFormatter } from "@/utils/useFormatter";

const { formatDate: formatDt, formatCurrency, storageUrl } = useFormatter();

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

const formatDate = (date: any) => {
  if (!date) return "-";
  return formatDt(date);
};

const getStatusLabel = (status: string) => {
  const labels: Record<string, string> = {
    pending: "Menunggu Persetujuan",
    approved: "Sedang Dipinjam",
    rejected: "Ditolak",
    returned: "Sudah Dikembalikan",
    expired: "Kadaluarsa",
  };
  return labels[status] || status;
};

const getStatusSeverity = (status: string) => {
  const severities: Record<string, string> = {
    pending: "warn",
    approved: "info",
    rejected: "danger",
    returned: "success",
    expired: "secondary",
  };
  return severities[status] || "info";
};
</script>

<template>
  <Dialog
    v-model:visible="modelVisible"
    modal
    header="Detail Lengkap Peminjaman"
    style="width: 800px"
    class="p-fluid"
  >
    <div v-if="detailLoan" class="space-y-4">
      <!-- 🔹 RINGKASAN ATAS -->
      <div
        class="flex justify-between items-center bg-gray-50 p-4 rounded-xl border border-gray-100 mb-2"
      >
        <div>
          <h2 class="text-xl font-bold text-gray-800">
            {{ detailLoan.tool?.name }}
          </h2>
          <p class="text-sm text-gray-500">
            ID: #{{ detailLoan.id }} | Unit: <b>{{ detailLoan.unit?.code }}</b>
          </p>
        </div>
        <div class="text-right">
          <Tag
            :value="getStatusLabel(detailLoan.status)"
            :severity="getStatusSeverity(detailLoan.status)"
            class="mb-1"
          />
          <p class="text-[10px] text-gray-400 uppercase tracking-widest">
            Status Peminjaman
          </p>
        </div>
      </div>

      <Tabs value="0">
        <TabList>
          <Tab value="0" class="flex items-center gap-2">
            <i class="pi pi-info-circle"></i> Peminjaman
          </Tab>
          <Tab
            value="1"
            v-if="detailLoan.tool_return || detailLoan.status === 'returned'"
            class="flex items-center gap-2"
          >
            <i class="pi pi-replay"></i> Pengembalian
          </Tab>
          <Tab
            value="2"
            v-if="detailLoan.violation"
            class="flex items-center gap-2"
          >
            <i class="pi pi-exclamation-triangle"></i> Pelanggaran
          </Tab>
        </TabList>

        <TabPanels>
          <!-- 📂 TAB 0: PEMINJAMAN -->
          <TabPanel value="0">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-4">
              <!-- Detail Pengajuan -->
              <div class="space-y-4">
                <div class="p-4 border rounded-xl bg-white shadow-sm">
                  <h3
                    class="font-bold text-gray-700 mb-3 border-b pb-2 text-sm uppercase"
                  >
                    Detail Pengajuan
                  </h3>
                  <div class="space-y-3 text-sm">
                    <div class="flex flex-col">
                      <span class="text-gray-400 text-xs">Tujuan:</span>
                      <span class="font-medium">{{ detailLoan.purpose }}</span>
                    </div>
                    <div class="flex justify-between">
                      <span class="text-gray-400">Tgl Pinjam:</span>
                      <span class="font-medium text-blue-600">{{
                        formatDate(detailLoan.loan_date)
                      }}</span>
                    </div>
                    <div class="flex justify-between">
                      <span class="text-gray-400">Batas Kembali:</span>
                      <span class="font-medium text-red-600">{{
                        formatDate(detailLoan.due_date)
                      }}</span>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Review Petugas -->
              <div class="space-y-4">
                <div class="p-4 border rounded-xl bg-white shadow-sm h-full">
                  <h3
                    class="font-bold text-gray-700 mb-3 border-b pb-2 text-sm uppercase"
                  >
                    Persetujuan Petugas
                  </h3>
                  <div v-if="detailLoan.review?.employee" class="space-y-3">
                    <div
                      class="flex items-center gap-3 p-2 bg-purple-50 rounded-lg"
                    >
                      <Avatar
                        icon="pi pi-user"
                        shape="circle"
                        class="bg-purple-200"
                      />
                      <div>
                        <p class="text-sm font-semibold">
                          {{ detailLoan.review.employee.details?.name }}
                        </p>
                        <p class="text-[10px] text-gray-500">
                          Petugas Peninjau
                        </p>
                      </div>
                    </div>
                    <div
                      class="p-3 bg-gray-50 rounded-lg border border-dashed text-sm"
                    >
                      <p class="text-[10px] text-gray-400 mb-1">
                        Catatan Petugas:
                      </p>
                      <p class="italic text-gray-700 leading-relaxed">
                        "{{ detailLoan.review.notes || "Tidak ada catatan" }}"
                      </p>
                    </div>
                  </div>
                  <div
                    v-else
                    class="flex flex-col items-center justify-center py-6 text-gray-400 italic text-sm text-center"
                  >
                    <i class="pi pi-hourglass text-2xl mb-2"></i>
                    <p>Menunggu peninjauan petugas...</p>
                  </div>
                </div>
              </div>
            </div>
          </TabPanel>

          <!-- 📂 TAB 1: PENGEMBALIAN -->
          <TabPanel value="1">
            <div class="pt-4">
              <div
                v-if="detailLoan.tool_return"
                class="grid grid-cols-1 md:grid-cols-2 gap-6"
              >
                <!-- Info Fisik/Waktu -->
                <div class="p-4 border rounded-xl bg-white shadow-sm">
                  <h3
                    class="font-bold text-gray-700 mb-3 border-b pb-2 text-sm uppercase"
                  >
                    Info Pengembalian
                  </h3>
                  <div class="space-y-3 text-sm">
                    <div class="flex justify-between">
                      <span class="text-gray-400">Waktu Kembali:</span>
                      <span class="font-medium">{{
                        formatDate(detailLoan.tool_return.return_date)
                      }}</span>
                    </div>
                    <div
                      v-if="detailLoan.tool_return.proof"
                      class="flex flex-col gap-2"
                    >
                      <span class="text-gray-400">Bukti Pengembalian:</span>
                      <Image
                        :src="storageUrl(detailLoan.tool_return.proof)"
                        alt="Bukti"
                        width="200"
                        preview
                        class="rounded-lg shadow-sm"
                      />
                    </div>
                  </div>
                </div>

                <!-- Konfirmasi Petugas -->
                <div class="p-4 border rounded-xl bg-white shadow-sm">
                  <h3
                    class="font-bold text-gray-700 mb-3 border-b pb-2 text-sm uppercase"
                  >
                    Konfirmasi Petugas
                  </h3>
                  <div v-if="detailLoan.tool_return.employee" class="space-y-3">
                    <div
                      class="flex items-center gap-3 p-2 bg-green-50 rounded-lg"
                    >
                      <Avatar
                        icon="pi pi-user-check"
                        shape="circle"
                        class="bg-green-200"
                      />
                      <div>
                        <p class="text-sm font-semibold">
                          {{ detailLoan.tool_return.employee.details?.name }}
                        </p>
                        <p class="text-[10px] text-gray-500">
                          Petugas Penerima
                        </p>
                      </div>
                    </div>
                    <div
                      class="p-3 bg-gray-50 rounded-lg border border-dashed text-sm"
                    >
                      <p class="text-[10px] text-gray-400 mb-1 text-right">
                        Catatan Penerimaan:
                      </p>
                      <p class="text-gray-700">
                        {{ detailLoan.tool_return.notes || "-" }}
                      </p>
                    </div>
                  </div>
                  <div
                    v-else
                    class="flex flex-col items-center justify-center py-6 text-amber-500 bg-amber-50 rounded-xl border border-amber-100"
                  >
                    <i class="pi pi-spinner pi-spin text-2xl mb-2"></i>
                    <p class="text-sm font-bold">Menunggu Konfirmasi Petugas</p>
                    <p class="text-[10px]">
                      Alat sudah Anda serahkan/kembalikan
                    </p>
                  </div>
                </div>
              </div>
            </div>
          </TabPanel>

          <!-- 📂 TAB 2: PELANGGARAN & PELUNASAN -->
          <TabPanel value="2">
            <div class="pt-4 space-y-6">
              <div
                v-if="detailLoan.violation"
                class="p-5 border rounded-xl bg-red-50 border-red-100 shadow-sm relative overflow-hidden"
              >
                <div class="absolute -right-4 -top-4 opacity-10">
                  <i
                    class="pi pi-exclamation-triangle"
                    style="font-size: 8rem"
                  ></i>
                </div>

                <div
                  class="grid grid-cols-1 md:grid-cols-2 gap-8 relative z-10"
                >
                  <!-- Rincian Masalah -->
                  <div>
                    <h3
                      class="font-bold text-red-700 mb-4 flex items-center gap-2"
                    >
                      <i class="pi pi-info-circle"></i> Rincian Pelanggaran
                    </h3>
                    <div class="space-y-3 text-sm">
                      <div class="flex justify-between">
                        <span class="text-red-400">Jenis:</span>
                        <Tag
                          :value="detailLoan.violation.type"
                          severity="danger"
                          class="uppercase"
                        />
                      </div>
                      <div
                        class="flex justify-between border-b border-red-200 pb-2"
                      >
                        <span class="text-red-400">Poin Pelanggaran:</span>
                        <span class="font-bold text-red-600"
                          >+{{ detailLoan.violation.total_score }} Poin</span
                        >
                      </div>
                      <div class="flex justify-between pt-1">
                        <span class="text-red-400">Nominal Denda:</span>
                        <span class="text-lg font-black text-red-700">{{
                          formatCurrency(detailLoan.violation.fine)
                        }}</span>
                      </div>
                      <div
                        class="mt-4 p-3 bg-white/60 rounded-lg text-xs text-red-800 border border-red-200"
                      >
                        <p class="font-bold mb-1">Deskripsi Masalah:</p>
                        {{ detailLoan.violation.description }}
                      </div>
                    </div>
                  </div>

                  <!-- Status Pelunasan -->
                  <div class="flex flex-col">
                    <h3 class="font-bold text-gray-700 mb-4 uppercase text-sm">
                      Status Penyelesaian
                    </h3>

                    <div
                      v-if="detailLoan.violation.settlement"
                      class="bg-white p-4 rounded-xl border border-green-200 space-y-3"
                    >
                      <div class="flex items-center gap-2 text-green-600 mb-2">
                        <i class="pi pi-check-circle"></i>
                        <span class="font-bold">LUNAS / SELESAI</span>
                      </div>
                      <div class="text-xs space-y-2">
                        <div class="flex justify-between">
                          <span class="text-gray-400">Waktu Pelunasan:</span>
                          <span class="font-medium">{{
                            formatDate(
                              detailLoan.violation.settlement.settled_at,
                            )
                          }}</span>
                        </div>
                        <div class="flex justify-between">
                          <span class="text-gray-400">Petugas:</span>
                          <span class="font-medium">{{
                            detailLoan.violation.settlement.employee?.name ||
                            "-"
                          }}</span>
                        </div>
                        <div class="mt-2 p-2 bg-gray-50 rounded border italic">
                          "{{
                            detailLoan.violation.settlement.notes ||
                            "Tanpa catatan"
                          }}"
                        </div>
                      </div>
                    </div>

                    <div
                      v-else
                      class="grow flex flex-col items-center justify-center bg-white/50 border border-dashed border-red-300 rounded-xl p-6"
                    >
                      <div
                        class="p-3 bg-red-100 text-red-600 rounded-full mb-3"
                      >
                        <i class="pi pi-wallet text-2xl"></i>
                      </div>
                      <p class="font-bold text-red-700">Belum Diselesaikan</p>
                      <p class="text-[10px] text-center text-red-500 mt-1">
                        Harap hubungi petugas atau lunasi denda untuk
                        mengaktifkan kembali hak pinjam Anda.
                      </p>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </TabPanel>
        </TabPanels>
      </Tabs>
    </div>

    <template #footer>
      <Button label="Tutup" severity="secondary" @click="close" outlined />
    </template>
  </Dialog>
</template>

<style scoped>
:deep(.p-tabview-panels) {
  padding: 0;
}
:deep(.p-tablist-tab-list) {
  border-bottom: 2px solid var(--p-content-border-color);
}
</style>
