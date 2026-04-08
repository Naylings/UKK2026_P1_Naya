// ─────────────────────────────────────────────
// pages/admin/users/composables/useUserManagement.ts
// Logic untuk user management page
// ─────────────────────────────────────────────

import { ref, computed } from "vue";
import { useUserStore } from "@/stores/user";
import { useToast } from "primevue/usetoast";
import { useConfirm } from "primevue/useconfirm";
import type {
  User,
  CreateUserPayload,
  UpdateUserPayload,
  UpdateUserCreditPayload,
} from "@/types/user";

type UserForm = Omit<CreateUserPayload, 'birth_date'> & {
  birth_date: Date | null;
};

export function useUserManagement() {
  const userStore = useUserStore();
  const toast = useToast();
  const confirm = useConfirm();

  // ── Dialog state ──────────────────────────────────────────────────────────

  const formVisible = ref(false);
  const detailVisible = ref(false);
  const creditVisible = ref(false);
  const isEditMode = ref(false);

  // ── Form data ─────────────────────────────────────────────────────────────

  const form = ref<Partial<UserForm>>({
    email: "",
    password: "",
    role: "User",
    nik: "",
    name: "",
    no_hp: "",
    address: "",
    birth_date: null,
  });

  const creditForm = ref({
    credit_score: 0,
  });

  // ── Selected user ─────────────────────────────────────────────────────────

  const selectedUser = ref<User | null>(null);
  const selectedUserId = ref<number | null>(null);

  // ── Filters ───────────────────────────────────────────────────────────────

  const filters = ref({
    role: "",
    search: "",
  });

  // ── Computed ──────────────────────────────────────────────────────────────

  const dialogTitle = computed(() =>
    isEditMode.value ? "Edit User" : "Tambah User Baru",
  );

  const submitButtonLabel = computed(() =>
    isEditMode.value ? "Update" : "Buat User",
  );

  // ── Actions ───────────────────────────────────────────────────────────────

  /**
   * Pagination handler
   */

  async function onPageChange(event: any) {
    const page = event.page + 1;
    const perPage = event.rows ?? userStore.perPage;
    await loadUsers({ page, per_page: perPage });
  }

  /**
   * Muat semua users
   */
  async function loadUsers(params?: { page?: number; per_page?: number }) {
    const success = await userStore.fetchUsers({
      page: params?.page ?? userStore.currentPage,
      per_page: params?.per_page ?? userStore.perPage,
      search: filters.value.search || undefined,
      role: filters.value.role || undefined,
    });

    

    if (!success) {
      toast.add({
        severity: "error",
        summary: "Error",
        detail: userStore.error,
        life: 3000,
      });
    }
  }

  /**
   * Buka dialog create
   */
  function openCreateDialog() {
    isEditMode.value = false;
    resetForm();
    formVisible.value = true;
  }

  /**
   * Buka dialog edit
   */
  function openEditDialog(user: User) {
    isEditMode.value = true;
    selectedUser.value = user;

    form.value = {
      email: user.email,
      role: user.role,
      nik: user.details?.nik || "",
      name: user.details?.name || "",
      no_hp: user.details?.no_hp || "",
      address: user.details?.address || "",
      birth_date:  user.details?.birth_date
        ? new Date(user.details.birth_date)
        : null,
    };

    formVisible.value = true;
  }

  /**
   * Submit form (create atau update)
   */
  async function submitForm() {
    const formatDate = (date: any) => {
      if (!date) return "";
      if (typeof date === "string") return date;
      const d = new Date(date);
      const year = d.getFullYear();
      const month = String(d.getMonth() + 1).padStart(2, "0");
      const day = String(d.getDate()).padStart(2, "0");
      return `${year}-${month}-${day}`;
    };

    if (isEditMode.value && selectedUser.value) {
      const payload: UpdateUserPayload = {
        id: selectedUser.value.id,
        role: form.value.role,
        nik: form.value.nik,
        name: form.value.name,
        no_hp: form.value.no_hp,
        address: form.value.address,
        birth_date: formatDate(form.value.birth_date),
      };

      const success = await userStore.updateUser(
        selectedUser.value.id,
        payload,
      );

      if (success) {
        toast.add({
          severity: "success",
          summary: "Berhasil",
          detail: "User berhasil diupdate.",
          life: 3000,
        });
        formVisible.value = false;
        resetForm();
      } else {
        toast.add({
          severity: "error",
          summary: "Error",
          detail: userStore.error,
          life: 3000,
        });
      }
    } else {
      const payload: CreateUserPayload = {
        email: form.value.email!,
        password: form.value.password!,
        role: form.value.role || "User",
        nik: form.value.nik!,
        name: form.value.name!,
        no_hp: form.value.no_hp!,
        address: form.value.address!,
        birth_date: formatDate(form.value.birth_date),
      };

      const success = await userStore.createUser(payload);

      if (success) {
        toast.add({
          severity: "success",
          summary: "Berhasil",
          detail: "User berhasil dibuat. (Credit awal: 100)",
          life: 3000,
        });
        formVisible.value = false;
        resetForm();
      } else {
        toast.add({
          severity: "error",
          summary: "Error",
          detail: userStore.error,
          life: 3000,
        });
      }
    }
  }

  /**
   * Buka dialog detail user
   */
  function openDetailDialog(user: User) {
    selectedUser.value = user;
    detailVisible.value = true;
  }

  /**
   * Buka dialog update credit
   */
  function openCreditDialog(user: User) {
    selectedUserId.value = user.id;
    creditForm.value.credit_score = user.credit_score;
    creditVisible.value = true;
  }

  /**
   * Submit update credit
   */
  async function submitCreditForm() {
    if (!selectedUserId.value) return;

    const payload: UpdateUserCreditPayload = {
      credit: creditForm.value.credit_score,
    };

    const success = await userStore.updateUserCredit(
      selectedUserId.value,
      payload,
    );

    if (success) {
      toast.add({
        severity: "success",
        summary: "Berhasil",
        detail: "Credit berhasil diupdate.",
        life: 3000,
      });
      creditVisible.value = false;
    } else {
      toast.add({
        severity: "error",
        summary: "Error",
        detail: userStore.error,
        life: 3000,
      });
    }
  }

  /**
   * Konfirmasi delete
   */
  function confirmDelete(user: User) {
    confirm.require({
      message: `Apakah Anda yakin ingin menghapus user "${user.details?.name || user.email}"?`,
      header: "Konfirmasi Hapus",
      icon: "pi pi-exclamation-triangle",
      accept: async () => {
        const success = await userStore.deleteUser(user.id);

        if (success) {
          toast.add({
            severity: "success",
            summary: "Berhasil",
            detail: "User berhasil dihapus.",
            life: 3000,
          });
        } else {
          toast.add({
            severity: "error",
            summary: "Error",
            detail: userStore.error,
            life: 3000,
          });
        }
      },
    });
  }

  /**
   * Reset form
   */
  function resetForm() {
    form.value = {
      email: "",
      password: "",
      role: "User",
      nik: "",
      name: "",
      no_hp: "",
      address: "",
      birth_date: "",
    };
    selectedUser.value = null;
  }

  /**
   * Clear filter
   */
  function clearFilter() {
    filters.value = {
      role: "",
      search: "",
    };
    loadUsers({ page: 1, per_page: userStore.perPage });
  }

  return {
    onPageChange,
    userStore,
    formVisible,
    detailVisible,
    creditVisible,
    isEditMode,
    form,
    creditForm,
    selectedUser,
    selectedUserId,
    filters,
    dialogTitle,
    submitButtonLabel,
    loadUsers,
    openCreateDialog,
    openEditDialog,
    submitForm,
    openDetailDialog,
    openCreditDialog,
    submitCreditForm,
    confirmDelete,
    resetForm,
    clearFilter,
  };
}
