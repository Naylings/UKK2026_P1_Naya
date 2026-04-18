<script setup lang="ts">
import { ref, watch, computed } from "vue";
import { useFormatter } from "@/utils/useFormatter";
import type { ReturnResponse, ReviewReturnPayload } from "@/types/return";
import dayjs from "dayjs";

const props = defineProps<{
  visible: boolean;
  selectedReturn: ReturnResponse | null;
  loading: boolean;
  config: any;
}>();

const emit = defineEmits<{
  (e: "update:visible", val: boolean): void;
  (e: "submit", payload: ReviewReturnPayload): void;
}>();

const { formatDate, storageUrl, formatCurrency } = useFormatter();

const activeTab = ref("0");
const reviewType = ref<"aman" | "bermasalah">("aman");
const selectedViolation = ref<"damaged" | "lost" | "late" | "other" | null>(
  null,
);


const form = ref<ReviewReturnPayload>({
  condition: "good",
  condition_notes: "",
  violation_type: null,
  total_score: 0,
  fine: 0,
  description: "",
});

const isLate = computed(() => {
  const dueDate = props.selectedReturn?.loan?.due_date;
  const returnDate = props.selectedReturn?.return_date;
  if (!dueDate || !returnDate) return false;

  return dayjs(returnDate).isAfter(dayjs(dueDate), "day");
});

const violationOptions = computed(() => {
  const options: {
    label: string;
    value: "damaged" | "lost" | "late" | "other";
  }[] = [
    { label: "Rusak", value: "damaged" },
    { label: "Hilang", value: "lost" },
  ];

  if (isLate.value) {
    options.push({ label: "Terlambat", value: "late" });
  }

  options.push({ label: "Lainnya (Manual)", value: "other" });

  return options;
});

const basePrice = computed(() => props.selectedReturn?.loan?.tool?.price || 0);

const violationConfig = computed(() => {
  if (!props.config) return null;
  return {
    damaged: {
      percent: props.config.broken_fine,
      score: props.config.broken_point,
    },
    lost: { percent: props.config.lost_fine, score: props.config.lost_point },
    late: {
      percent: props.config.late_fine || 0,
      score: props.config.late_point || 0,
    },
    other: { percent: 0, score: 0 },
  };
});

const calculatedFine = computed(() => {
  if (!selectedViolation.value || !violationConfig.value) return 0;
  if (selectedViolation.value === "other") return manualFine.value;

  const cfg = violationConfig.value[selectedViolation.value];
  return Math.round((basePrice.value * (cfg?.percent || 0)) / 100);
});

const calculatedScore = computed(() => {
  if (!selectedViolation.value || !violationConfig.value) return 0;
  if (selectedViolation.value === "other") return manualScore.value;

  return violationConfig.value[selectedViolation.value]?.score || 0;
});


watch(
  () => props.visible,
  (val) => {
    if (val) {
      reviewType.value = isLate.value ? "bermasalah" : "aman";
      selectedViolation.value = isLate.value ? "late" : null;
      manualFine.value = 0;
      manualScore.value = 0;
    }
  },
);

watch(reviewType, (val) => {
  if (val === "aman") {
    form.value.condition = "good";
    selectedViolation.value = null;
  } else {
    form.value.condition = "maintenance";
    if (isLate.value) selectedViolation.value = "late";
  }
});

const manualFine = ref(0);
const manualScore = ref(0);

const defaultFine = computed(() => {
  if (
    !selectedViolation.value ||
    !violationConfig.value ||
    selectedViolation.value === "other"
  )
    return 0;
  const cfg = violationConfig.value[selectedViolation.value];
  return Math.round((basePrice.value * (cfg?.percent || 0)) / 100);
});

const defaultScore = computed(() => {
  if (
    !selectedViolation.value ||
    !violationConfig.value ||
    selectedViolation.value === "other"
  )
    return 0;
  return violationConfig.value[selectedViolation.value]?.score || 0;
});

watch(selectedViolation, (newVal) => {
  if (newVal && newVal !== "other") {
    manualFine.value = defaultFine.value;
    manualScore.value = defaultScore.value;
  } else if (newVal === "other") {
    manualFine.value = 0;
    manualScore.value = 0;
  }
});

watch([manualFine, manualScore, selectedViolation], () => {
  form.value.violation_type = selectedViolation.value;
  form.value.fine = manualFine.value;
  form.value.total_score = manualScore.value;

  if (selectedViolation.value) {
    form.value.description =
      selectedViolation.value === "other"
        ? "Pelanggaran Manual"
        : `Pelanggaran: ${selectedViolation.value}`;
  }
});
</script>
<template>
  <Dialog
    :visible="visible"
    @update:visible="emit('update:visible', $event)"
    modal
    header="Verifikasi Pengembalian Alat"
    style="width: 1000px"
    class="p-fluid"
  >
    <div v-if="selectedReturn" class="space-y-4">
      <Tabs v-model:value="activeTab">
        <TabList>
          <Tab value="0"><i class="pi pi-file-edit mr-2"></i>Verifikasi</Tab>
          <Tab value="1"><i class="pi pi-box mr-2"></i>Detail Alat</Tab>
        </TabList>

        <TabPanels>
          <TabPanel value="0">
            <div class="grid grid-cols-1 md:grid-cols-12 gap-6 pt-4">
              <div class="md:col-span-4">
                <div class="p-4 bg-gray-50 rounded-2xl border text-center">
                  <span
                    class="text-xs font-bold text-gray-400 uppercase block mb-2"
                    >Bukti Pengembalian</span
                  >
                  <Image
                    :src="storageUrl(selectedReturn.proof || '')"
                    preview
                    class="rounded-xl w-full"
                  />
                </div>
              </div>

              <div class="md:col-span-8 space-y-4">
                <SelectButton
                  v-model="reviewType"
                  :options="[
                    { label: 'Aman', value: 'aman' },
                    { label: 'Bermasalah / Denda', value: 'bermasalah' },
                  ]"
                  optionLabel="label"
                  optionValue="value"
                />

                <div
                  :class="[
                    'p-4 border rounded-xl text-sm',
                    isLate
                      ? 'bg-red-50 border-red-100'
                      : 'bg-blue-50 border-blue-100',
                  ]"
                >
                  <div class="flex justify-between mb-1">
                    <span class="text-gray-500">Deadline:</span>
                    <span class="font-bold">{{
                      formatDate(selectedReturn.loan?.due_date)
                    }}</span>
                  </div>
                  <div class="flex justify-between">
                    <span class="text-gray-500">Dikembalikan:</span>
                    <span
                      :class="isLate ? 'text-red-600 font-bold' : 'font-bold'"
                      >{{ formatDate(selectedReturn.return_date) }}</span
                    >
                  </div>
                  <div
                    v-if="isLate"
                    class="mt-2 text-red-700 font-bold text-xs flex items-center gap-1"
                  >
                    <i class="pi pi-exclamation-circle"></i> Terlambat
                    dikembalikan!
                  </div>
                </div>

                <div
                  v-if="reviewType === 'aman'"
                  class="p-6 bg-green-50 rounded-xl border border-green-100 text-center"
                >
                  <i
                    class="pi pi-check-circle text-3xl text-green-500 mb-2"
                  ></i>
                  <p class="text-sm text-green-700 font-medium">
                    Semua dalam kondisi baik. Unit akan otomatis Available.
                  </p>
                </div>

                <div v-else class="space-y-4 animate-fade-in">
                  <div class="p-4 border rounded-xl bg-gray-50 space-y-4">
                    <label class="font-bold text-xs uppercase text-gray-500"
                      >Jenis Pelanggaran</label
                    >

                    <div class="grid grid-cols-1 gap-2">
                      <div
                        v-for="opt in violationOptions"
                        :key="opt.value"
                        class="flex items-center justify-between border p-3 rounded-xl cursor-pointer transition-all"
                        :class="
                          selectedViolation === opt.value
                            ? 'border-red-500 bg-red-100'
                            : 'bg-white'
                        "
                        @click="selectedViolation = opt.value"
                      >
                        <div class="flex items-center gap-3">
                          <RadioButton
                            v-model="selectedViolation"
                            :value="opt.value"
                          />
                          <span class="font-medium text-sm">{{
                            opt.label
                          }}</span>
                        </div>
                      </div>
                    </div>

                    <div
                      v-if="selectedViolation"
                      class="grid grid-cols-2 gap-4 p-4 bg-white rounded-xl border border-dashed border-gray-300 animate-fade-in"
                    >
                      <div class="flex flex-col gap-2">
                        <label class="text-xs font-bold text-gray-600">
                          {{
                            selectedViolation === "other"
                              ? "Input Denda"
                              : "Denda (Bisa Diedit)"
                          }}
                        </label>
                        <InputNumber
                          v-model="manualFine"
                          mode="currency"
                          currency="IDR"
                          locale="id-ID"
                          fluid
                          class="p-inputtext-sm"
                        />
                        <small
                          class="text-[10px] text-gray-400"
                          v-if="selectedViolation !== 'other'"
                        >
                          Default: {{ formatCurrency(defaultFine) }}
                        </small>
                      </div>

                      <div class="flex flex-col gap-2">
                        <label class="text-xs font-bold text-gray-600">
                          {{
                            selectedViolation === "other"
                              ? "Input Poin"
                              : "Poin (Bisa Diedit)"
                          }}
                        </label>
                        <InputNumber
                          v-model="manualScore"
                          :min="0"
                          fluid
                          class="p-inputtext-sm"
                        />
                        <small
                          class="text-[10px] text-gray-400"
                          v-if="selectedViolation !== 'other'"
                        >
                          Default: +{{ defaultScore }}
                        </small>
                      </div>
                    </div>

                    <div
                      v-if="selectedViolation"
                      class="flex justify-between p-4 bg-gray-900 text-white rounded-xl mt-4"
                    >
                      <div>
                        <p class="text-[10px] opacity-60 uppercase">
                          Total Denda Final
                        </p>
                        <p class="text-lg font-bold text-green-400">
                          {{ formatCurrency(manualFine) }}
                        </p>
                      </div>
                      <div class="text-right">
                        <p class="text-[10px] opacity-60 uppercase">
                          Total Poin Final
                        </p>
                        <p class="text-lg font-bold text-orange-400">
                          +{{ manualScore }}
                        </p>
                      </div>
                    </div>
                  </div>
                </div>

                <div class="space-y-2 mt-4">
                  <label
                    class="flex items-center gap-2 font-bold text-xs uppercase text-gray-500 ml-1"
                  >
                    <i class="pi pi-pencil text-blue-500"></i>
                    Kondisi Alat Terbaru
                  </label>
                  <div class="relative group">
                    <Textarea
                      v-model="form.condition_notes"
                      placeholder="Contoh: Lensa sedikit berdebu, kabel pengisi daya agak longgar, dsb..."
                      rows="4"
                      autoResize
                      class="w-full bg-white! border-gray-200! focus:border-blue-500! focus:ring-2! focus:ring-blue-100! transition-all duration-300 rounded-xl p-4 text-sm shadow-sm"
                    />
                    <div
                      class="absolute bottom-3 right-3 text-[10px] text-gray-400 opacity-0 group-focus-within:opacity-100 transition-opacity"
                    >
                      {{ form.condition_notes?.length || 0 }} Karakter
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </TabPanel>
          <TabPanel value="1">
            <div class="pt-4 space-y-6">
              <div class="p-5 border rounded-2xl bg-white shadow-sm space-y-4">
                <h3
                  class="font-bold text-gray-700 border-b pb-2 uppercase text-xs tracking-wider flex items-center gap-2"
                >
                  <i class="pi pi-info-circle text-blue-500"></i> Informasi
                  Utama Alat
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                  <div class="space-y-3">
                    <div class="flex justify-between">
                      <span class="text-gray-400">Nama Alat:</span
                      ><span class="font-bold text-gray-800">{{
                        selectedReturn.loan?.tool?.name
                      }}</span>
                    </div>
                    <div class="flex justify-between">
                      <span class="text-gray-400">Kode Unit:</span
                      ><span class="font-mono bg-gray-100 px-2 rounded">{{
                        selectedReturn.loan?.unit?.code
                      }}</span>
                    </div>
                  </div>
                  <div class="space-y-3">
                    <div class="flex justify-between">
                      <span class="text-gray-400">Tipe:</span
                      ><Tag
                        :value="selectedReturn.loan?.tool?.item_type"
                        severity="info"
                      />
                    </div>
                    <div class="flex justify-between border-t pt-2">
                      <span class="text-gray-400">Harga Total:</span
                      ><span class="font-bold text-green-600">{{
                        formatCurrency(basePrice)
                      }}</span>
                    </div>
                  </div>
                </div>
              </div>

              <div
                v-if="selectedReturn.loan?.tool?.bundle_components?.length"
                class="space-y-3"
              >
                <h3
                  class="font-bold text-gray-700 uppercase text-xs tracking-wider flex items-center gap-2 ml-1"
                >
                  <i class="pi pi-list text-orange-500"></i> Daftar Komponen
                  (Wajib Kembali)
                </h3>

                <div class="grid grid-cols-1 gap-3">
                  <div
                    v-for="(comp, index) in selectedReturn.loan.tool
                      .bundle_components"
                    :key="index"
                    class="flex items-center justify-between p-4 bg-gray-50 border border-gray-100 rounded-xl hover:bg-white hover:shadow-md transition-all"
                  >
                    <div class="flex items-center gap-4">
                      <div
                        class="w-10 h-10 bg-gray-200 rounded-lg flex items-center justify-center text-gray-500 font-bold"
                      >
                        {{ index + 1 }}
                      </div>
                      <div>
                        <p class="font-bold text-gray-800 leading-tight">
                          {{ comp.name }}
                        </p>
                        <p
                          class="text-[10px] text-gray-500 font-mono uppercase"
                        >
                          {{ comp.code }}
                        </p>
                      </div>
                    </div>

                    <div class="text-right">
                      <p class="text-xs text-gray-400 uppercase">Jumlah</p>
                      <p class="font-black text-lg text-blue-600">
                        {{ comp.qty }}
                        <span class="text-xs font-normal text-gray-500"
                          >Unit</span
                        >
                      </p>
                    </div>
                  </div>
                </div>

                <div
                  class="p-3 bg-blue-50 rounded-lg border border-blue-100 flex items-start gap-2"
                >
                  <i class="pi pi-info-circle text-blue-600 mt-0.5"></i>
                  <p class="text-[11px] text-blue-700">
                    Pastikan semua komponen di atas lengkap dan sesuai jumlahnya
                    sebelum mengonfirmasi status <b>"Aman"</b>.
                  </p>
                </div>
              </div>

              <div
                v-else
                class="p-8 border-2 border-dashed rounded-2xl flex flex-col items-center justify-center text-gray-400"
              >
                <i class="pi pi-box text-3xl mb-2"></i>
                <p class="text-sm">
                  Alat ini adalah unit tunggal (tidak memiliki sub-komponen).
                </p>
              </div>
            </div>
          </TabPanel>
        </TabPanels>
      </Tabs>
    </div>

    <template #footer>
      <div class="flex justify-between items-center w-full">
        <span class="text-[10px] text-gray-400 italic"
          >Verifikasi bersifat permanen.</span
        >
        <div class="flex gap-2">
          <Button
            label="Batal"
            severity="secondary"
            text
            @click="emit('update:visible', false)"
          />
          <Button
            label="Simpan Verifikasi"
            icon="pi pi-check"
            :loading="loading"
            :disabled="reviewType === 'bermasalah' && !selectedViolation"
            @click="emit('submit', form)"
          />
        </div>
      </div>
    </template>
  </Dialog>
</template>
