-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Apr 02, 2026 at 02:31 AM
-- Server version: 8.0.30
-- PHP Version: 8.1.10

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `adh_dhuhaa`
--

-- --------------------------------------------------------

--
-- Table structure for table `detail_penilaian`
--

CREATE TABLE `detail_penilaian` (
  `id` int NOT NULL,
  `penilaian_id` int NOT NULL,
  `item_id` int NOT NULL,
  `nilai` tinyint(1) DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `guru`
--

CREATE TABLE `guru` (
  `id` int NOT NULL,
  `nama` varchar(100) NOT NULL,
  `nrg` varchar(50) DEFAULT NULL,
  `tmt_guru` date DEFAULT NULL,
  `jabatan` varchar(100) DEFAULT NULL,
  `status_kepegawaian` varchar(100) DEFAULT NULL,
  `tipe` varchar(20) NOT NULL DEFAULT 'guru_kelas' COMMENT 'FK ke tipe_guru.kode',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `guru`
--

INSERT INTO `guru` (`id`, `nama`, `nrg`, `tmt_guru`, `jabatan`, `status_kepegawaian`, `tipe`, `created_at`, `updated_at`) VALUES
(1, 'Sudila Wasih, S.Pd', '202007 02051998 024', '2020-07-01', 'Guru Tahsin & Tahfidz', 'Guru Tahsin & Tahfidz', 'guru_quran', '2026-03-30 02:49:31', '2026-03-30 02:49:31'),
(2, 'Siti Nur Holis, S.Pd', '202107 01031997 036', '2021-07-01', 'Guru Pengganti & Tes Jilid', 'Guru Pengganti & Tes Jilid', 'guru_quran', '2026-03-30 02:49:31', '2026-03-30 02:49:31'),
(3, 'Pramuja, S.M', '202107 113061996 035', '2021-07-01', 'Guru Tahsin & Tahfidz', 'Guru Tahsin & Tahfidz', 'guru_quran', '2026-03-30 02:49:31', '2026-03-30 02:49:31'),
(4, 'Ela Tarina, S.E', '202109 11121981 032', '2021-09-01', 'Guru Tahsin & Tahfidz', 'Guru Tahsin & Tahfidz', 'guru_quran', '2026-03-30 02:49:31', '2026-03-30 02:49:31'),
(5, 'Elni Yufina, S.Pd', '202007 09071999 023', '2021-07-01', 'Guru Tahsin & Tahfidz', 'Guru Tahsin & Tahfidz', 'guru_quran', '2026-03-30 02:49:31', '2026-03-30 02:49:31'),
(6, 'Wencu Ali Murtopo, A.Md', '20220702011981 001', '2022-07-01', 'Guru Tahsin & Tahfidz', 'Guru Tahsin & Tahfidz', 'guru_quran', '2026-03-30 02:49:31', '2026-03-30 02:49:31'),
(7, 'Kurniawan Achiryadi, S.T', '20220703081998 002', '2022-07-01', 'Guru Tahsin & Tahfidz', 'Guru Tahsin & Tahfidz', 'guru_quran', '2026-03-30 02:49:31', '2026-03-30 02:49:31'),
(8, 'Arina Hananan Taqiyya, S.Akun', '202407 11092002 008', '2024-07-01', 'Guru Tahsin & Tahfidz', 'Guru Tahsin & Tahfidz', 'guru_quran', '2026-03-30 02:49:31', '2026-03-30 02:49:31'),
(9, 'Nadia Novianti, S.Pd', '20220730111998 005', '2022-07-01', 'Guru Tahsin & Tahfidz', 'Guru Tahsin & Tahfidz', 'guru_quran', '2026-03-30 02:49:31', '2026-03-30 02:49:31'),
(10, 'Nur Khoirun Nisa, S.Pd', '20220726011999 008', '2022-07-01', 'Guru Tahsin & Tahfidz', 'Guru Tahsin & Tahfidz', 'guru_quran', '2026-03-30 02:49:31', '2026-03-30 02:49:31'),
(11, 'Musdalifah, S.Pd', '20220708121995 009', '2022-07-01', 'Guru Tahsin & Tahfidz', 'Guru Tahsin & Tahfidz', 'guru_quran', '2026-03-30 02:49:31', '2026-03-30 02:49:31'),
(12, 'Regas Sahromi, S.Pd', '20220703061998 013', '2022-07-01', 'Guru Tahsin & Tahfidz', 'Guru Tahsin & Tahfidz', 'guru_quran', '2026-03-30 02:49:31', '2026-03-30 02:49:31'),
(13, 'Kontana Hafiz', '202407 05042002 009', '2024-07-01', 'Guru Tahsin & Tahfidz', 'Guru Tahsin & Tahfidz', 'guru_quran', '2026-03-30 02:49:31', '2026-03-30 02:49:31'),
(14, 'Dandy Badrul Zaman', '202501 11112000 001', '2025-01-01', 'Guru Tahsin & Tahfidz', 'Guru Tahsin & Tahfidz', 'guru_quran', '2026-03-30 02:49:31', '2026-03-30 02:49:31'),
(15, 'Juraina, S.P', '202407 09061997 003', '2024-07-01', 'Guru Tahsin & Tahfidz', 'Guru Tahsin & Tahfidz', 'guru_quran', '2026-03-30 02:49:31', '2026-03-30 02:49:31'),
(16, 'Ani Kinanti, S.Kom', '202407 01101999 004', '2024-07-01', 'Operator Sekolah', 'Operator Sekolah', 'gtk', '2026-03-30 02:49:31', '2026-04-01 00:00:00'),
(17, 'Nurul Hidayati', '202407 13022002 007', '2024-07-01', 'Guru Tahsin & Tahfidz', 'Guru Tahsin & Tahfidz', 'guru_quran', '2026-03-30 02:49:31', '2026-03-30 02:49:31'),
(18, 'Anisa Alawiyah', '202407 06061999 010', '2024-07-01', 'Guru Siroh', 'Guru Siroh', 'mapel', '2026-03-30 02:49:31', '2026-04-01 00:00:00'),
(19, 'Ilham Syafriyullah', '202407 10112000 011', '2024-07-01', 'Guru Tahsin & Tahfidz', 'Guru Tahsin & Tahfidz', 'guru_quran', '2026-03-30 02:49:31', '2026-03-30 02:49:31'),
(20, 'Budiono Rahman, S.Pd', '202407 12041999 012', '2024-07-01', 'Guru Tahsin & Tahfidz', 'Guru Tahsin & Tahfidz', 'guru_quran', '2026-03-30 02:49:31', '2026-03-30 02:49:31'),
(21, 'Khuswatun Hasanah, S.Pd', '202107 31121991 034', '2021-07-01', 'Guru Kelas', 'Guru Kelas', 'guru_kelas', '2026-03-30 02:49:31', '2026-03-30 02:49:31'),
(22, 'Nia Olivia, S.Pd', '202407 01012001 014', '2024-07-01', 'Guru Kelas', 'Guru Kelas', 'guru_kelas', '2026-03-30 02:49:31', '2026-03-30 02:49:31'),
(23, 'Siti Maisaroh, Sos.I', '201907 08081990 006', '2019-07-01', 'Guru Kelas', 'Guru Kelas', 'guru_kelas', '2026-03-30 02:49:31', '2026-03-30 02:49:31'),
(24, 'Puji Astuti, S.Psi', '202207 11101988 007', '2022-07-01', 'Guru Kelas', 'Guru Kelas', 'guru_kelas', '2026-03-30 02:49:31', '2026-03-30 02:49:31'),
(25, 'Sapitri, S.Pd', '202407 07122001 006', '2024-07-01', 'Guru Kelas', 'Guru Kelas', 'guru_kelas', '2026-03-30 02:49:31', '2026-03-30 02:49:31'),
(26, 'Siti Rahmawati, S.Pd', '202207 14071999 006', '2022-07-01', 'Guru Kelas', 'Guru Kelas', 'guru_kelas', '2026-03-30 02:49:31', '2026-03-30 02:49:31'),
(27, 'Aldo Afuleno, S.Pd', '202407 26042000 015', '2024-07-01', 'Guru Kelas', 'Guru Kelas', 'guru_kelas', '2026-03-30 02:49:31', '2026-03-30 02:49:31'),
(28, 'Nurul Hudayah, S.E', '202107 03061999 030', '2021-07-01', 'Guru Kelas', 'Guru Kelas', 'guru_kelas', '2026-03-30 02:49:31', '2026-03-30 02:49:31'),
(29, 'Iren Prabugma, S.Pd', '202307 11012001 003', '2023-07-01', 'Guru Kelas', 'Guru Kelas', 'guru_kelas', '2026-03-30 02:49:31', '2026-03-30 02:49:31'),
(30, 'Yasa Putri, S.P', '202501 17061996 003', '2025-01-01', 'Guru Kelas', 'Guru Kelas', 'guru_kelas', '2026-03-30 02:49:31', '2026-04-01 00:00:00'),
(31, 'Khoirun Nisa, S.Pd', '202501 01012002 002', '2025-01-01', 'Guru Kelas', 'Guru Kelas', 'guru_kelas', '2026-03-30 02:49:31', '2026-04-01 00:00:00'),
(32, 'Wahyu Aditiya, S.Pd', '202407 16122000 002', '2024-07-01', 'Guru Kelas', 'Guru Kelas', 'guru_kelas', '2026-03-30 02:49:31', '2026-03-30 02:49:31'),
(33, 'Iis Arika, S.E', '202307 08081999 004', '2023-07-01', 'Guru Kelas', 'Guru Kelas', 'guru_kelas', '2026-03-30 02:49:31', '2026-03-30 02:49:31'),
(34, 'Siti Fatonah, S.Pd', '202407 24071999 005', '2024-07-01', 'Guru Kelas', 'Guru Kelas', 'guru_kelas', '2026-03-30 02:49:31', '2026-03-30 02:49:31'),
(35, 'Ari Firdaus, A.Ma', '202101 16061995 028', '2021-01-01', 'Guru Kelas', 'Guru Kelas', 'guru_kelas', '2026-03-30 02:49:31', '2026-03-30 02:49:31'),
(36, 'Ilam Maryam, S.Mat', '202411 29092001 015', '2024-11-01', 'Guru Kelas', 'Guru Kelas', 'guru_kelas', '2026-03-30 02:49:31', '2026-04-01 00:00:00'),
(37, 'Sunartik, S.Pd', '202007 13111992 018', '2020-07-01', 'Guru Kelas', 'Guru Kelas', 'guru_kelas', '2026-03-30 02:49:31', '2026-03-30 02:49:31'),
(38, 'Hapipah, S.Pd', '202303 02121996 001', '2024-11-05', 'Guru Kelas', 'Guru Kelas', 'guru_kelas', '2026-03-30 02:49:31', '2026-03-30 02:49:31'),
(39, 'Faradila Agustina, S.Pd', '202207 09081992 004', '2022-07-01', 'Guru Mapel B. Inggris', 'Guru Mapel B. Inggris', 'mapel', '2026-03-30 02:49:31', '2026-03-30 02:49:31'),
(40, 'Romadhon, S.Pd', '202109 02121995 016', '2021-09-01', 'Guru Mapel PAI', 'Guru Mapel PAI', 'mapel', '2026-03-30 02:49:31', '2026-03-30 02:49:31'),
(41, 'Bahrum, S.Pd', '202510 03072002 001', '2025-10-06', 'Guru PJOK', 'Guru PJOK', 'mapel', '2026-03-30 02:49:31', '2026-04-01 00:00:00'),
(42, 'Deri Lisnawati, S.Sos', '202107 15121997 033', '2021-07-01', 'Guru Tahsin & Tahfidz', 'Guru Tahsin & Tahfidz', 'guru_quran', '2026-03-30 02:49:31', '2026-04-01 00:00:00'),
(43, 'Istiqomah, S.Pd', '202307 19091987 012', '2023-07-01', 'Guru Siroh', 'Guru Siroh', 'mapel', '2026-03-30 02:49:31', '2026-03-30 02:49:31'),
(44, 'Hasyim Ashari, S.T., M.Pd', '202007 20041995 020', '2020-07-01', 'Kepala Sekolah', 'Kepala Sekolah', 'gtk', '2026-03-30 02:49:31', '2026-04-01 00:00:00'),
(45, 'Kamila, S.P', '202007 10121997 013', '2019-07-01', 'Waka. Bidang Kurikulum', 'Waka. Bidang Kurikulum', 'gtk', '2026-03-30 02:49:31', '2026-03-30 02:49:31'),
(46, 'Cempaka', '202007 13061982 019', '2012-07-01', 'Waka. Bidang Kesiswaan', 'Waka. Bidang Kesiswaan', 'gtk', '2026-03-30 02:49:31', '2026-03-30 02:49:31'),
(47, 'Ismail, SH', '201907 21011996 007', '2019-07-01', 'Waka. Bidang Keislaman', 'Waka. Bidang Keislaman', 'gtk', '2026-03-30 02:49:31', '2026-03-30 02:49:31'),
(48, 'Kessye Arisani, S.Si', '201907 23011988 008', NULL, 'Kepala Tata Usaha', 'Kepala Tata Usaha', 'gtk', '2026-03-30 02:49:31', '2026-03-30 02:49:31'),
(49, 'Arif Tria Firmansyah, A.Md', '202007 16011974 026', NULL, 'Operator Sekolah', 'Operator Sekolah', 'gtk', '2026-03-30 02:49:31', '2026-03-30 02:49:31'),
(50, 'Biswin', '202007 05111969 027', '2020-07-01', 'Keamanan & Koordinator Kebersihan', 'Keamanan & Koordinator Kebersihan', 'gtk', '2026-03-30 02:49:31', '2026-04-01 00:00:00'),
(51, 'Nadila Fasya Anggraeni, S.Kom', '202407 28012002 013', '2024-07-01', 'Tata Usaha', 'Tata Usaha', 'gtk', '2026-03-30 02:49:31', '2026-03-30 02:49:31'),
(52, 'Faturrahman Arif Rumata, S.Pd., M.Pd', '202507 03061998 007', '2025-07-01', 'Mapel PAI & Siroh', 'Mapel PAI & Siroh', 'mapel', '2026-04-01 00:00:00', '2026-04-01 00:00:00'),
(53, 'Inda, S.Pd', '202507 20102002 008', '2025-07-01', 'Guru Tahsin & Tahfidz', 'Guru Tahsin & Tahfidz', 'guru_quran', '2026-04-01 00:00:00', '2026-04-01 00:00:00'),
(54, 'Lia Susanti, S.Mat', '202507 13022002 009', '2025-07-01', 'Guru Tahsin & Tahfidz', 'Guru Tahsin & Tahfidz', 'guru_quran', '2026-04-01 00:00:00', '2026-04-01 00:00:00'),
(55, 'Muhamad Sidik, S.Pd', '202507 08041999 011', '2025-07-01', 'Guru Tahsin & Tahfidz', 'Guru Tahsin & Tahfidz', 'guru_quran', '2026-04-01 00:00:00', '2026-04-01 00:00:00'),
(56, 'Ari, S.Pd', '202507 06061999 002', '2025-07-01', 'Guru Kelas', 'Guru Kelas', 'guru_kelas', '2026-04-01 00:00:00', '2026-04-01 00:00:00'),
(57, 'Edwir Fyanurdin, S.Pd', '202507 31051999 004', '2025-07-01', 'Guru Kelas', 'Guru Kelas', 'guru_kelas', '2026-04-01 00:00:00', '2026-04-01 00:00:00'),
(58, 'Siti Redha Qadarsih, S.Pd', '202507 23112002 012', '2025-07-01', 'Guru Tahsin & Tahfidz', 'Guru Tahsin & Tahfidz', 'guru_quran', '2026-04-01 00:00:00', '2026-04-01 00:00:00'),
(59, 'Ema Purnama Sari, S.Pd', '202507 30052002 006', '2025-07-01', 'Guru Tahsin & Tahfidz', 'Guru Tahsin & Tahfidz', 'guru_quran', '2026-04-01 00:00:00', '2026-04-01 00:00:00'),
(60, 'Elisa Irmalia, S.KpG', '202507 18041994 005', '2025-07-01', 'UKS & Pengganti Guru Tahsin & Tahfidz', 'UKS & Pengganti Guru Tahsin & Tahfidz', 'gtk', '2026-04-01 00:00:00', '2026-04-01 00:00:00'),
(61, 'Chika Kalista Danila, S.Pd', '202507 02072001 003', '2025-07-01', 'Guru Kelas', 'Guru Kelas', 'guru_kelas', '2026-04-01 00:00:00', '2026-04-01 00:00:00'),
(62, 'Aprisia Zilfi, S.Pd', '202507 15042001 001', '2025-07-01', 'Guru Kelas', 'Guru Kelas', 'guru_kelas', '2026-04-01 00:00:00', '2026-04-01 00:00:00'),
(63, 'Marina, S.Pd', '202507 18051995 010', '2025-07-01', 'Guru Tahsin & Tahfidz', 'Guru Tahsin & Tahfidz', 'guru_quran', '2026-04-01 00:00:00', '2026-04-01 00:00:00'),
(64, 'Agustian Rana', '202509 23082004 001', '2025-09-01', 'Tenaga Kebersihan', 'Tenaga Kebersihan', 'gtk', '2026-04-01 00:00:00', '2026-04-01 00:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `guru_history`
--

CREATE TABLE `guru_history` (
  `id` int NOT NULL,
  `aksi` enum('tambah','edit','hapus') NOT NULL,
  `guru_id` int DEFAULT NULL,
  `nama` varchar(100) NOT NULL,
  `nrg` varchar(50) DEFAULT NULL,
  `tmt_guru` date DEFAULT NULL,
  `jabatan` varchar(100) DEFAULT NULL,
  `status_kepegawaian` varchar(100) DEFAULT NULL,
  `tipe` varchar(20) DEFAULT NULL COMMENT 'Snapshot tipe saat aksi dilakukan',
  `oleh` varchar(100) DEFAULT NULL,
  `keterangan` text,
  `waktu` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `item`
--

CREATE TABLE `item` (
  `id` int NOT NULL,
  `komponen_id` int NOT NULL,
  `nomor_item` varchar(10) NOT NULL,
  `nama_item` varchar(255) NOT NULL,
  `urutan` int DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `item`
--

INSERT INTO `item` (`id`, `komponen_id`, `nomor_item`, `nama_item`, `urutan`) VALUES
(1, 1, '1.1', 'Persentase Kehadiran', 1),
(2, 1, '1.2', 'Datang di sekolah tepat waktu dan kepulangan tepat waktu', 2),
(3, 1, '1.3', 'Berpakaian seragam sesuai ketentuan', 3),
(4, 1, '1.4', 'Ikut serta dalam upacara dan senam di sekolah', 4),
(5, 1, '1.5', 'Ikut serta dalam rapat-rapat di sekolah', 5),
(6, 1, '1.6', 'Ikut serta dalam Inspirasi Pagi', 6),
(7, 1, '1.7', 'Kehadiran pembinaan sabtu', 7),
(8, 1, '1.8', 'Adab minta izin dan menulis dibuku izin', 8),
(9, 2, '2.1', 'Menuliskan hasil evaluasi tahsin, tahfizh dan adab', 1),
(10, 2, '2.2', 'Menyampaikan materi pembelajaran bahasa arab', 2),
(11, 2, '2.3', 'Metode pengajaran', 3),
(12, 2, '2.4', 'Membuat halaqoh yang tertib', 4),
(13, 2, '2.5', 'Mengajar sesuai SOP', 5),
(14, 2, '2.6', 'Kedisiplinan masuk dan keluar halaqoh', 6),
(15, 2, '2.7', 'Menggunakan bahasa arab pembukaan dan penutupan halaqoh', 7),
(16, 2, '2.8', 'Menyampaikan capaian perkembangan siswa kepada orang tua', 8),
(17, 3, '3.1', 'Melaksanakan tugas yang diberikan oleh Kepala Sekolah', 1),
(18, 3, '3.2', 'Membantu teman dalam ikut memecahkan masalah', 2),
(19, 3, '3.3', 'KBM', 3),
(20, 3, '3.4', 'Menciptakan hubungan yang harmonis dengan orangtua/wali murid', 4),
(21, 3, '3.5', 'Menciptakan hubungan yang harmonis dengan guru dan GTK', 5),
(22, 3, '3.6', 'Amanah dan Aktif berpartisapasi dalam kepanitiaan', 6),
(23, 3, '3.7', 'Piket kedatangan dan kepulangan', 7),
(24, 3, '3.8', 'Kerjasama dengan guru kelas', 8),
(25, 4, '1.1', 'Persentase Kehadiran', 1),
(26, 4, '1.2', 'Datang di sekolah tepat waktu dan kepulangan tepat waktu', 2),
(27, 4, '1.3', 'Berpakaian seragam sesuai ketentuan', 3),
(28, 4, '1.4', 'Ikut serta dalam upacara dan senam di sekolah', 4),
(29, 4, '1.5', 'Ikut serta dalam rapat-rapat di sekolah', 5),
(30, 4, '1.6', 'Ikut serta dalam Inspirasi Pagi', 6),
(31, 4, '1.7', 'Kehadiran pembinaan sabtu', 7),
(32, 4, '1.8', 'Adab minta izin dan menulis dibuku izin', 8),
(33, 5, '2.1', 'Membuat Administrasi pembelajaran (Promes, Modul Ajar, jurnal harian, dll)', 1),
(34, 5, '2.2', 'Melaksanakan pembelajaran P5 dan Life Skill', 2),
(35, 5, '2.3', 'Metode pengajaran', 3),
(36, 5, '2.4', 'Membuat program perbaikan', 4),
(37, 5, '2.5', 'Membimbing Siswa wudhu', 5),
(38, 5, '2.6', 'Kebersihan dan kerapian kelas', 6),
(39, 5, '2.7', 'Membimbing Siswa Sholat, Dzikir dan Doa', 7),
(40, 5, '2.8', 'Memuat Kurikulum keislaman', 8),
(41, 6, '3.1', 'Melaksanakan tugas yang diberikan oleh Kepala Sekolah', 1),
(42, 6, '3.2', 'Membantu teman dalam ikut memecahkan masalah', 2),
(43, 6, '3.3', 'KBM', 3),
(44, 6, '3.4', 'Menciptakan hubungan yang harmonis dengan orangtua/wali murid', 4),
(45, 6, '3.5', 'Menciptakan hubungan yang harmonis dengan guru dan GTK', 5),
(46, 6, '3.6', 'Amanah dan Aktif berpartisapasi dalam kepanitiaan', 6),
(47, 6, '3.7', 'Piket kedatangan dan kepulangan', 7),
(48, 7, '1.1', 'Persentase Kehadiran', 1),
(49, 7, '1.2', 'Datang di sekolah tepat waktu dan kepulangan tepat waktu', 2),
(50, 7, '1.3', 'Berpakaian seragam sesuai ketentuan', 3),
(51, 7, '1.4', 'Ikut serta dalam upacara dan senam di sekolah', 4),
(52, 7, '1.5', 'Ikut serta dalam rapat-rapat di sekolah', 5),
(53, 7, '1.6', 'Ikut serta dalam Inspirasi Pagi', 6),
(54, 7, '1.7', 'Kehadiran pembinaan sabtu', 7),
(55, 7, '1.8', 'Adab minta izin dan menulis dibuku izin', 8),
(56, 8, '2.1', 'Membuat Administrasi pembelajaran (Promes, Modul Ajar, jurnal harian, dll)', 1),
(57, 8, '2.2', 'Metode pengajaran', 2),
(58, 8, '2.4', 'Membuat program perbaikan', 3),
(59, 8, '2.5', 'Membimbing Siswa wudhu', 4),
(60, 8, '2.6', 'Kebersihan dan kerapian kelas', 5),
(61, 8, '2.7', 'Membimbing Siswa Sholat, Dzikir dan Doa', 6),
(62, 8, '2.8', 'Memuat Kurikulum keislaman', 7),
(63, 9, '3.1', 'Melaksanakan tugas yang diberikan oleh Kepala Sekolah', 1),
(64, 9, '3.2', 'Membantu teman dalam ikut memecahkan masalah', 2),
(65, 9, '3.3', 'KBM', 3),
(66, 9, '3.4', 'Menciptakan hubungan yang harmonis dengan orangtua/wali murid', 4),
(67, 9, '3.5', 'Menciptakan hubungan yang harmonis dengan guru dan GTK', 5),
(68, 9, '3.6', 'Amanah dan Aktif berpartisapasi dalam kepanitiaan', 6),
(69, 9, '3.7', 'Piket kedatangan dan kepulangan', 7),
(74, 13, '1.1', 'Persentase Kehadiran', 1),
(75, 13, '1.2', 'Datang di sekolah tepat waktu dan kepulangan tepat waktu', 2),
(76, 13, '1.3', 'Berpakaian seragam sesuai ketentuan', 3),
(77, 13, '1.4', 'Ikut serta dalam upacara dan senam di sekolah', 4),
(78, 13, '1.5', 'Ikut serta dalam rapat-rapat di sekolah', 5),
(79, 13, '1.6', 'Ikut serta dalam Inspirasi Pagi', 6),
(80, 14, '2.1', 'Kerjasama dengan sesama tenaga pendidik dan kependidikan', 1),
(81, 14, '2.2', 'Membantu kelancaran kegiatan sekolah', 2),
(82, 14, '2.3', 'Menjaga hubungan baik dengan wali murid', 3),
(83, 15, '3.1', 'Melaksanakan tugas pokok sesuai jabatan', 1),
(84, 15, '3.2', 'Menyelesaikan pekerjaan tepat waktu', 2),
(85, 15, '3.3', 'Menjaga kebersihan dan kerapian lingkungan kerja', 3),
(86, 15, '3.4', 'Bertanggung jawab atas tugas yang diberikan', 4);

-- --------------------------------------------------------

--
-- Table structure for table `komponen_penilaian`
--

CREATE TABLE `komponen_penilaian` (
  `id` int NOT NULL,
  `tipe_guru` varchar(20) NOT NULL COMMENT 'FK ke tipe_guru.kode',
  `nama_kategori` varchar(100) NOT NULL,
  `urutan` int DEFAULT '0',
  `is_tambahan` tinyint(1) DEFAULT '0' COMMENT '0=standar, 1=ditambahkan saat penilaian'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `komponen_penilaian`
--

INSERT INTO `komponen_penilaian` (`id`, `tipe_guru`, `nama_kategori`, `urutan`, `is_tambahan`) VALUES
(1, 'guru_quran', 'Disiplin', 1, 0),
(2, 'guru_quran', 'Pelaksanaan Pembelajaran', 2, 0),
(3, 'guru_quran', 'Kerjasama', 3, 0),
(4, 'guru_kelas', 'Disiplin', 1, 0),
(5, 'guru_kelas', 'Pelaksanaan Pembelajaran', 2, 0),
(6, 'guru_kelas', 'Kerjasama', 3, 0),
(7, 'mapel', 'Disiplin', 1, 0),
(8, 'mapel', 'Pelaksanaan Pembelajaran', 2, 0),
(9, 'mapel', 'Kerjasama', 3, 0),
(13, 'gtk', 'Disiplin', 1, 0),
(14, 'gtk', 'Kerjasama', 2, 0),
(15, 'gtk', 'Kinerja', 3, 0);

-- --------------------------------------------------------

--
-- Table structure for table `penilaian`
--

CREATE TABLE `penilaian` (
  `id` int NOT NULL,
  `guru_id` int NOT NULL,
  `periode` varchar(100) NOT NULL,
  `periode_awal` date DEFAULT NULL,
  `periode_akhir` date DEFAULT NULL,
  `tanggal_penilaian` date NOT NULL,
  `penilai` varchar(100) DEFAULT NULL,
  `jabatan_penilai` varchar(100) DEFAULT NULL,
  `catatan` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tipe_guru`
--

CREATE TABLE `tipe_guru` (
  `id` int NOT NULL,
  `kode` varchar(20) NOT NULL COMMENT 'Slug unik: guru_quran, guru_kelas, mapel, gtk',
  `label` varchar(100) NOT NULL COMMENT 'Label tampil di UI: Guru Qur''an, Guru Kelas, dst',
  `urutan` int DEFAULT '0' COMMENT 'Urutan tampil di dropdown'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Master tipe/kategori guru';

--
-- Dumping data for table `tipe_guru`
--

INSERT INTO `tipe_guru` (`id`, `kode`, `label`, `urutan`) VALUES
(1, 'guru_quran', 'Guru Qur\'an', 1),
(2, 'guru_kelas', 'Guru Kelas', 2),
(3, 'mapel', 'Guru Mapel', 3),
(4, 'gtk', 'GTK/Staff', 4);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `nama_lengkap` varchar(100) NOT NULL,
  `role` enum('admin','kepala_sekolah') DEFAULT 'admin',
  `must_change_password` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'Wajib ganti password saat login pertama',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `nama_lengkap`, `role`, `must_change_password`, `created_at`) VALUES
(1, 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrator', 'admin', 1, '2026-03-30 02:49:31'),
(2, 'kepala', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Hasyim Ashari, S.T', 'kepala_sekolah', 1, '2026-03-30 02:49:31');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `detail_penilaian`
--
ALTER TABLE `detail_penilaian`
  ADD PRIMARY KEY (`id`),
  ADD KEY `penilaian_id` (`penilaian_id`),
  ADD KEY `item_id` (`item_id`);

--
-- Indexes for table `guru`
--
ALTER TABLE `guru`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_guru_nrg` (`nrg`) COMMENT 'Setiap NRG harus unik per guru',
  ADD KEY `fk_guru_tipe` (`tipe`);

--
-- Indexes for table `guru_history`
--
ALTER TABLE `guru_history`
  ADD PRIMARY KEY (`id`),
  -- SARAN-05: Index untuk filter history per guru dan per aksi
  ADD KEY `idx_history_guru_id` (`guru_id`),
  ADD KEY `idx_history_aksi` (`aksi`),
  ADD KEY `idx_history_waktu` (`waktu`);

--
-- Indexes for table `item`
--
ALTER TABLE `item`
  ADD PRIMARY KEY (`id`),
  ADD KEY `komponen_id` (`komponen_id`);

--
-- Indexes for table `komponen_penilaian`
--
ALTER TABLE `komponen_penilaian`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_komponen_tipe` (`tipe_guru`);

--
-- Indexes for table `penilaian`
--
ALTER TABLE `penilaian`
  ADD PRIMARY KEY (`id`),
  ADD KEY `guru_id` (`guru_id`),
  -- SARAN-05: Index komposit untuk cek duplikat (guru + periode) — dipakai di validasi penilaian.php
  ADD KEY `idx_penilaian_guru_periode` (`guru_id`, `periode_awal`, `periode_akhir`),
  -- SARAN-05: Index untuk filter rekap dan ranking berdasarkan periode
  ADD KEY `idx_penilaian_periode` (`periode`),
  ADD KEY `idx_penilaian_tanggal` (`tanggal_penilaian`);

--
-- Indexes for table `tipe_guru`
--
ALTER TABLE `tipe_guru`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `kode` (`kode`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `detail_penilaian`
--
ALTER TABLE `detail_penilaian`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=247;

--
-- AUTO_INCREMENT for table `guru`
--
ALTER TABLE `guru`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=65;

--
-- AUTO_INCREMENT for table `guru_history`
--
ALTER TABLE `guru_history`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `item`
--
ALTER TABLE `item`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=94;

--
-- AUTO_INCREMENT for table `komponen_penilaian`
--
ALTER TABLE `komponen_penilaian`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `penilaian`
--
ALTER TABLE `penilaian`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `tipe_guru`
--
ALTER TABLE `tipe_guru`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `detail_penilaian`
--
ALTER TABLE `detail_penilaian`
  ADD CONSTRAINT `detail_penilaian_ibfk_1` FOREIGN KEY (`penilaian_id`) REFERENCES `penilaian` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `detail_penilaian_ibfk_2` FOREIGN KEY (`item_id`) REFERENCES `item` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `guru`
--
ALTER TABLE `guru`
  ADD CONSTRAINT `fk_guru_tipe` FOREIGN KEY (`tipe`) REFERENCES `tipe_guru` (`kode`) ON DELETE RESTRICT ON UPDATE CASCADE;

--
-- Constraints for table `item`
--
ALTER TABLE `item`
  ADD CONSTRAINT `item_ibfk_1` FOREIGN KEY (`komponen_id`) REFERENCES `komponen_penilaian` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `komponen_penilaian`
--
ALTER TABLE `komponen_penilaian`
  ADD CONSTRAINT `fk_komponen_tipe` FOREIGN KEY (`tipe_guru`) REFERENCES `tipe_guru` (`kode`) ON DELETE RESTRICT ON UPDATE CASCADE;

--
-- Constraints for table `penilaian`
--
ALTER TABLE `penilaian`
  ADD CONSTRAINT `penilaian_ibfk_1` FOREIGN KEY (`guru_id`) REFERENCES `guru` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
