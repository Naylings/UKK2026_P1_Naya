import AppLayout from "@/layout/AppLayout.vue";
import { useAuthStore } from "@/stores/auth";
import { createRouter, createWebHistory } from "vue-router";

const router = createRouter({
    history: createWebHistory(),
    routes: [
        {
            path: "/",
            component: AppLayout,
            meta: { auth: true },
            children: [
                {
                    path: "",
                    name: "root",
                    redirect: () => {
                        const auth = useAuthStore();

                        if (!auth.user) return { name: "login" };

                        return { name: getDashboardByRole(auth.user.role) };
                    },
                },
                {
                    path: "/staff",
                    name: "dashboard pengelola",
                    component: () => import("@/pages/Dashboard.vue"),
                    meta: {
                        title: "Dashboard Pengelola",
                        roles: ["Admin", "Employee"],
                    },
                },
                {
                    path: "/dashboard",
                    name: "dashboard",
                    component: () => import("@/pages/peminjam/Dashboard.vue"),
                    meta: { title: "Dashboard Peminjam", roles: ["User"] },
                },
                {
                    path: "/users",
                    name: "user management",
                    component: () => import("@/pages/admin/users/Index.vue"),
                    meta: { title: "Manajemen User", roles: ["Admin"] },
                },
                {
                    path: "/categories",
                    name: "category management",
                    component: () =>
                        import("@/pages/admin/categories/Index.vue"),
                    meta: { title: "Manajemen Kategori", roles: ["Admin"] },
                },
                {
                    path: "/tools",
                    name: "tool management",
                    component: () => import("@/pages/admin/tools/Index.vue"),
                    meta: { title: "Manajemen Alat", roles: ["Admin"] },
                },
                {
                    path: "/tools/:id",
                    name: "tool detail",
                    component: () => import("@/pages/admin/tools/Detail.vue"),
                    meta: { title: "Detail Alat", roles: ["Admin"] },
                },
                {
                    path: "/app-config",
                    name: "app config",
                    component: () => import("@/pages/admin/AppConfig.vue"),
                    meta: { title: "Konfigurasi Aplikasi", roles: ["Admin"] },
                },
                {
                    path: "/tools/user",
                    name: "user tools",
                    component: () => import("@/pages/peminjam/tools/Index.vue"),
                    meta: { title: "Daftar Alat", roles: ["User"] },
                },
                {
                    path: "/tools/user/:id",
                    name: "user tool detail",
                    component: () =>
                        import("@/pages/peminjam/tools/Detail.vue"),
                    meta: { title: "Detail Alat", roles: ["User"] },
                },
                {
                    path: "/loan-request/:toolId",
                    name: "loan-request",
                    component: () =>
                        import("@/pages/peminjam/loan/Request.vue"),
                    meta: { title: "Ajukan Peminjaman", roles: ["User"] },
                },
                {
                    path: "/loans",
                    name: "peminjam-loans",
                    component: () => import("@/pages/peminjam/loan/Index.vue"),
                    meta: { title: "Riwayat Peminjaman", roles: ["User"] },
                },
                {
                    path: "/staff/loans",
                    name: "petugas-loans",
                    component: () => import("@/pages/petugas/loan/Index.vue"),
                    meta: {
                        title: "Pengajuan Peminjaman",
                        roles: ["Employee"],
                    },
                },
                {
                    path: "/staff/returns",
                    name: "petugas-returns",
                    component: () => import("@/pages/petugas/return/Index.vue"),
                    meta: {
                        title: "Pengembalian Peminjaman",
                        roles: ["Employee"],
                    },
                }
            ],
        },
        {
            path: "/pages/notfound",
            name: "notfound",
            component: () => import("@/pages/NotFound.vue"),
            meta: { title: "Not Found" },
        },
        {
            path: "/auth/login",
            name: "login",
            component: () => import("@/pages/auth/Login.vue"),
            meta: { title: "Login", guest: true }, // ← tambah guest: true
        },
        {
            path: "/auth/access",
            name: "accessDenied",
            component: () => import("@/pages/auth/Access.vue"),
            meta: { title: "Access Denied" },
        },
        {
            path: "/auth/error",
            name: "error",
            component: () => import("@/pages/auth/Error.vue"),
            meta: { title: "Error" },
        },
        {
            path: "/:pathMatch(.*)*",
            redirect: { name: "notfound" },
        },
    ],
});

// ── Helper: tentukan dashboard berdasarkan role ───────────────────────────

function getDashboardByRole(role: string): string {
    if (role === "Admin" || role === "Employee") {
        return "dashboard pengelola";
    }
    return "dashboard";
}

// ── Guard ─────────────────────────────────────────────────────────────────

router.beforeEach(async (to) => {
    const auth = useAuthStore();

    // Pastikan session sudah dicek sebelum guard berjalan
    await auth.fetchMe();

    // Halaman guest (login): redirect ke dashboard sesuai role jika sudah login
    if (to.meta.guest && auth.isLoggedIn && auth.user) {
        return { name: getDashboardByRole(auth.user.role) };
    }

    // Halaman protected: redirect ke login jika belum login
    if (to.meta.auth && !auth.isLoggedIn) {
        return { name: "login" };
    }

    // Role tidak diizinkan: redirect ke accessDenied
    const allowedRoles = to.meta.roles as string[] | undefined;
    if (allowedRoles && auth.user && !allowedRoles.includes(auth.user.role)) {
        return { name: "accessDenied" };
    }
});

export default router;
