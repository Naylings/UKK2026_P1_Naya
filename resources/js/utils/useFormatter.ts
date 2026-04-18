import dayjs from "dayjs";
import 'dayjs/locale/id';


dayjs.locale('id');

export function useFormatter() {
  
  function storageUrl(path: string | null): string | null {
    if (!path) return null;
    if (path.startsWith("http") || path.startsWith("/storage")) return path;
    return `/storage/${path}`;
  }

  
  function formatCurrency(value: number): string {
    return new Intl.NumberFormat("id-ID", {
      style: "currency",
      currency: "IDR",
      minimumFractionDigits: 0,
    }).format(value);
  }

  
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