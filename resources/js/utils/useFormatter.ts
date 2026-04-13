import dayjs from "dayjs";
import 'dayjs/locale/id';

// Set locale secara global untuk file ini
dayjs.locale('id');

export function useFormatter() {
  /**
   * Mengolah path file menjadi URL storage yang valid
   */
  function storageUrl(path: string | null): string | null {
    if (!path) return null;
    if (path.startsWith("http") || path.startsWith("/storage")) return path;
    return `/storage/${path}`;
  }

  /**
   * Memformat angka ke dalam mata uang Rupiah (IDR)
   */
  function formatCurrency(value: number): string {
    return new Intl.NumberFormat("id-ID", {
      style: "currency",
      currency: "IDR",
      minimumFractionDigits: 0,
    }).format(value);
  }

  /**
   * Memformat string tanggal ke format "DD MMMM YYYY" (Bahasa Indonesia)
   */
  function formatDate(date?: string): string {
    if (!date) return '-';
    return dayjs(date).format('DD MMMM YYYY');
  }

  return {
    storageUrl,
    formatCurrency,
    formatDate,
  };
}