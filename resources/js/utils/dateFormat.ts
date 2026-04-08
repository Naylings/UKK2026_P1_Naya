import dayjs from "dayjs";

import 'dayjs/locale/id';

dayjs.locale('id');

function formatDate(date?: string) {
  if (!date) return '-';
  return dayjs(date).format('DD MMMM YYYY'); 
}

export { formatDate };