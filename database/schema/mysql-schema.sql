/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
DROP TABLE IF EXISTS `activity_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `activity_logs` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int DEFAULT NULL COMMENT 'FK ke users yang melakukan aksi. NULL jika aksi otomatis sistem',
  `action` varchar(100) NOT NULL COMMENT 'Kode aksi: loan.created  ,   loan.approved  ,   return.recorded  ,   violation.created  ,   settlement.created  ,   appeal.approved  ,   dst',
  `module` varchar(50) NOT NULL COMMENT 'Modul terkait: loans  ,   returns  ,   violations  ,   settlements  ,   appeals  ,   tools  ,   users',
  `description` text NOT NULL COMMENT 'Penjelasan aksi dalam bahasa natural',
  `meta` text COMMENT 'Data konteks tambahan dalam format JSON string. Contoh: ARRAY[\\\\\\\\\\"loan_id\\\\\\\\',
  `ip_address` varchar(45) DEFAULT NULL COMMENT 'IP address pelaku aksi',
  `created_at` timestamp NOT NULL,
  PRIMARY KEY (`id`),
  KEY `activity_logs_user_id_foreign` (`user_id`),
  CONSTRAINT `activity_logs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `app_configs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `app_configs` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL COMMENT 'nama app/instansi',
  `late_point` int NOT NULL COMMENT 'poin penalty yang bertambah jika pengembalian alat terlambat',
  `broken_point` int NOT NULL COMMENT 'poin penalty yang bertambah jika alat rusak',
  `lost_point` int NOT NULL COMMENT 'poin penalty yang bertambah jika alat hilang',
  `late_fine` int NOT NULL COMMENT 'persenan default denda dari harga alat',
  `broken_fine` int NOT NULL COMMENT 'persenan default denda dari harga alat',
  `lost_fine` int NOT NULL COMMENT 'persenan default denda dari harga alat',
  `updated_at` timestamp NULL DEFAULT NULL COMMENT 'Terakhir diubah oleh Admin',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `appeals`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `appeals` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL COMMENT 'FK ke users   (  User  )   yang mengajukan banding',
  `reviewed_by` int DEFAULT NULL COMMENT 'FK ke users   (  Admin  )   yang mereview banding',
  `reason` text NOT NULL COMMENT 'Alasan atau refleksi dari user  ,   biasanya diajukan saat penalty_points tinggi sehingga tidak ada alat yang bisa dipinjam',
  `status` enum('pending','approved','rejected') NOT NULL,
  `credit_changed` int DEFAULT NULL COMMENT 'Jumlah poin credit yang berubah',
  `admin_notes` text COMMENT 'Catatan atau feedback Admin ke user',
  `created_at` timestamp NOT NULL,
  `reviewed_at` timestamp NULL DEFAULT NULL COMMENT 'Waktu Admin memutuskan. NULL jika masih pending',
  PRIMARY KEY (`id`),
  KEY `appeals_user_id_foreign` (`user_id`),
  KEY `appeals_reviewed_by_foreign` (`reviewed_by`),
  CONSTRAINT `appeals_reviewed_by_foreign` FOREIGN KEY (`reviewed_by`) REFERENCES `users` (`id`),
  CONSTRAINT `appeals_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `bundle_tools`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `bundle_tools` (
  `id` int NOT NULL AUTO_INCREMENT,
  `bundle_id` int NOT NULL COMMENT 'FK ke tools dimana item_type = bundle',
  `tool_id` int NOT NULL COMMENT 'FK ke tools dimana item_type = bundle_tool',
  `qty` int NOT NULL COMMENT 'Jumlah sub-tool ini dalam satu bundle',
  PRIMARY KEY (`id`),
  KEY `bundle_tools_bundle_id_foreign` (`bundle_id`),
  KEY `bundle_tools_tool_id_foreign` (`tool_id`),
  CONSTRAINT `bundle_tools_bundle_id_foreign` FOREIGN KEY (`bundle_id`) REFERENCES `tools` (`id`),
  CONSTRAINT `bundle_tools_tool_id_foreign` FOREIGN KEY (`tool_id`) REFERENCES `tools` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `categories` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL COMMENT 'Nama kategori alat',
  `description` text NOT NULL COMMENT 'Deskripsi kategori',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `loans`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `loans` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL COMMENT 'FK ke users  ,   peminjam yang mengajukan',
  `tool_id` int NOT NULL COMMENT 'FK ke tools  ,   template alat yang dipinjam',
  `unit_code` varchar(255) NOT NULL COMMENT 'FK ke tool_units  ,   unit fisik spesifik yang dipilih user   (  berlaku untuk single maupun bundle)',
  `employee_id` int DEFAULT NULL COMMENT 'FK ke users   (  Employee  )    ,   diisi saat approve atau reject',
  `status` enum('pending','approved','rejected','expired','returned') NOT NULL,
  `loan_date` date NOT NULL COMMENT 'Tanggal mulai peminjaman',
  `due_date` date NOT NULL COMMENT 'Tanggal wajib kembali',
  `purpose` text NOT NULL COMMENT 'Tujuan/keperluan peminjaman dari user',
  `notes` text COMMENT 'Catatan Employee saat approve atau reject',
  `created_at` timestamp NOT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `loans_user_id_foreign` (`user_id`),
  KEY `loans_tool_id_foreign` (`tool_id`),
  KEY `loans_unit_code_foreign` (`unit_code`),
  KEY `loans_employee_id_foreign` (`employee_id`),
  CONSTRAINT `loans_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `users` (`id`),
  CONSTRAINT `loans_tool_id_foreign` FOREIGN KEY (`tool_id`) REFERENCES `tools` (`id`),
  CONSTRAINT `loans_unit_code_foreign` FOREIGN KEY (`unit_code`) REFERENCES `tool_units` (`code`),
  CONSTRAINT `loans_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `returns`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `returns` (
  `id` int NOT NULL AUTO_INCREMENT,
  `loan_id` int NOT NULL COMMENT 'FK ke loans  ,   1 loan hanya bisa punya 1 return',
  `employee_id` int DEFAULT NULL COMMENT 'FK ke users   (  Employee  )   yang mencatat pengembalian',
  `reviewed` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Apakah pengembalian sudah di-review oleh Employee',
  `return_date` timestamp NOT NULL COMMENT 'Tanggal aktual alat dikembalikan',
  `proof` varchar(255) DEFAULT NULL COMMENT 'foto/video bukti',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `returns_loan_id_unique` (`loan_id`),
  KEY `returns_employee_id_foreign` (`employee_id`),
  CONSTRAINT `returns_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `users` (`id`),
  CONSTRAINT `returns_loan_id_foreign` FOREIGN KEY (`loan_id`) REFERENCES `loans` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `settlements`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `settlements` (
  `id` int NOT NULL AUTO_INCREMENT,
  `violation_id` int NOT NULL COMMENT 'FK ke violations  ,   1 pelanggaran hanya bisa dilunasi 1 kali',
  `employee_id` int NOT NULL COMMENT 'FK ke users   (  Employee  )   yang mencatat pelunasan',
  `description` text NOT NULL COMMENT 'Penjelasan pelunasan: bayar denda / ganti alat / kesepakatan lain',
  `settled_at` timestamp NOT NULL COMMENT 'Waktu pelunasan dicatat. Setelah ini violations.status = settled dan users.is_restricted = 0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `settlements_violation_id_unique` (`violation_id`),
  KEY `settlements_employee_id_foreign` (`employee_id`),
  CONSTRAINT `settlements_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `users` (`id`),
  CONSTRAINT `settlements_violation_id_foreign` FOREIGN KEY (`violation_id`) REFERENCES `violations` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `tool_units`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tool_units` (
  `code` varchar(255) NOT NULL COMMENT 'Kode unik unit fisik  ,   dibuat BE. Single: LPT-001 | Bundle: SET-PK-001',
  `tool_id` int NOT NULL COMMENT 'FK ke tools   (  template)',
  `status` enum('available','nonactive','lent') NOT NULL,
  `notes` text NOT NULL COMMENT 'Catatan tambahan unit',
  `created_at` timestamp NOT NULL,
  PRIMARY KEY (`code`),
  KEY `tool_units_tool_id_foreign` (`tool_id`),
  CONSTRAINT `tool_units_tool_id_foreign` FOREIGN KEY (`tool_id`) REFERENCES `tools` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `tools`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tools` (
  `id` int NOT NULL AUTO_INCREMENT,
  `category_id` int NOT NULL COMMENT 'FK ke categories',
  `name` varchar(255) NOT NULL COMMENT 'Nama template/jenis alat',
  `item_type` enum('single','bundle','bundle_tool') NOT NULL,
  `price` bigint NOT NULL COMMENT 'harga tool ,  sebagai patokan untuk perhitungan ,  misalkan pada config fine untuk telas 15 atau 15% dari harga alat\\ maka denda secara default akan berjumlah 15% dari harga alat',
  `min_credit_score` int DEFAULT NULL COMMENT 'min credit user yang dibutuhkan untuk pinjam alat',
  `description` text NOT NULL COMMENT 'Deskripsi umum alat atau bundle',
  `code_slug` varchar(15) NOT NULL COMMENT 'kode unik untuk unit,  dibuat BE. CTH: Single: LPT | Bundle: SET-PK',
  `photo_path` varchar(255) NOT NULL COMMENT 'Path foto representatif alat',
  `created_at` timestamp NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `tools_code_slug_unique` (`code_slug`),
  KEY `tools_category_id_foreign` (`category_id`),
  CONSTRAINT `tools_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `unit_conditions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `unit_conditions` (
  `id` varchar(255) NOT NULL COMMENT 'Kode unik riwayat kondisi  ,   dibuat BE',
  `unit_code` varchar(255) NOT NULL COMMENT 'FK ke tool_units',
  `return_id` int DEFAULT NULL COMMENT 'FK ke returns  ,   NULL jika dicatat di luar konteks pengembalian   (  entry awal  ,   maintenance  ,   inspeksi)',
  `conditions` enum('good','broken','maintenance') NOT NULL,
  `notes` text NOT NULL COMMENT 'Penjelasan kondisi saat dicatat',
  `recorded_at` timestamp NOT NULL COMMENT 'Waktu kondisi dicatat. Kondisi terkini = recorded_at paling baru',
  PRIMARY KEY (`id`),
  KEY `unit_conditions_unit_code_foreign` (`unit_code`),
  KEY `unit_conditions_return_id_foreign` (`return_id`),
  CONSTRAINT `unit_conditions_return_id_foreign` FOREIGN KEY (`return_id`) REFERENCES `returns` (`id`) ON DELETE SET NULL,
  CONSTRAINT `unit_conditions_unit_code_foreign` FOREIGN KEY (`unit_code`) REFERENCES `tool_units` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `user_details`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `user_details` (
  `nik` varchar(255) NOT NULL COMMENT 'Nomor Induk Kependudukan  ,   unik per orang',
  `user_id` int DEFAULT NULL COMMENT 'FK ke users',
  `name` varchar(255) DEFAULT NULL COMMENT 'Nama lengkap',
  `no_hp` varchar(255) DEFAULT NULL COMMENT 'Nomor handphone',
  `address` varchar(255) DEFAULT NULL COMMENT 'Alamat lengkap',
  `birth_date` date DEFAULT NULL COMMENT 'Tanggal lahir',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`nik`),
  KEY `user_details_user_id_foreign` (`user_id`),
  CONSTRAINT `user_details_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `email` varchar(255) NOT NULL COMMENT 'Email untuk login  ,   harus unik',
  `password` varchar(255) NOT NULL COMMENT 'Password ter-hash   (  bcrypt)',
  `role` enum('Admin','Employee','User') NOT NULL,
  `credit_score` int NOT NULL COMMENT 'Akumulasi poin pelanggaran  ,   bertambah tiap melanggar. Makin tinggi makin terbatas alat yang bisa dipinjam',
  `is_restricted` tinyint NOT NULL DEFAULT '0' COMMENT '1 = sedang ada pinjaman aktif atau belum settlement  ,   tidak bisa ajukan pinjaman baru',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `violations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `violations` (
  `id` int NOT NULL AUTO_INCREMENT,
  `loan_id` int NOT NULL COMMENT 'FK ke loans yang menghasilkan pelanggaran',
  `user_id` int NOT NULL COMMENT 'FK ke users yang dikenakan pelanggaran',
  `return_id` int DEFAULT NULL COMMENT 'FK ke returns. NULL jika type = lost karena tidak ada pengembalian',
  `type` enum('late','damaged','lost','other') NOT NULL,
  `total_score` int NOT NULL COMMENT 'total kredit user yang berkurang',
  `fine` double NOT NULL COMMENT 'Jumlah Denda',
  `description` text NOT NULL COMMENT 'Penjelasan detail pelanggaran',
  `status` enum('active','settled') NOT NULL,
  `created_at` timestamp NOT NULL,
  PRIMARY KEY (`id`),
  KEY `violations_loan_id_foreign` (`loan_id`),
  KEY `violations_user_id_foreign` (`user_id`),
  KEY `violations_return_id_foreign` (`return_id`),
  CONSTRAINT `violations_loan_id_foreign` FOREIGN KEY (`loan_id`) REFERENCES `loans` (`id`),
  CONSTRAINT `violations_return_id_foreign` FOREIGN KEY (`return_id`) REFERENCES `returns` (`id`),
  CONSTRAINT `violations_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (1,'2026_04_05_172215_create_categories_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (2,'2026_04_05_172216_create_users_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (3,'2026_04_05_172217_create_user_details_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (4,'2026_04_05_172218_create_tools_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (5,'2026_04_05_172219_create_tool_units_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (6,'2026_04_05_172220_create_unit_conditions_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (7,'2026_04_05_172221_create_bundle_tools_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (8,'2026_04_05_172222_create_loans_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (9,'2026_04_05_172223_create_returns_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (10,'2026_04_05_172224_create_violations_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (11,'2026_04_05_172225_create_settlements_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (12,'2026_04_05_172226_create_appeals_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (13,'2026_04_05_172227_create_activity_logs_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (14,'2026_04_05_172228_create_app_configs_table',1);
