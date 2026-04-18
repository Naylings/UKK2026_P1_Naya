import { useAppealStore } from "@/stores/appeal";
import type { CreateAppealPayload } from "@/types/appeal";
import { ref, onMounted } from "vue";

export function useUserAppeal() {
  const store = useAppealStore();

  const showDialog = ref(false);
  const reason = ref("");

  async function loadAppeals() {
    await store.fetchMy();
  }

  onMounted(loadAppeals);

  function openDialog() {
    reason.value = "";
    showDialog.value = true;
  }

  function closeDialog() {
    showDialog.value = false;
  }

  async function submit() {
    if (!reason.value.trim()) return;

    const payload: CreateAppealPayload = {
      reason: reason.value,
    };

    const success = await store.create(payload);

    if (success) {
      closeDialog();
    }
  }

  return {
    store,
    showDialog,
    reason,

    openDialog,
    closeDialog,
    submit,
    loadAppeals,
  };
}