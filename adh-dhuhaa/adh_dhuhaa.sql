-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Apr 17, 2026 at 09:37 AM
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
-- Table structure for table `guru`
--

CREATE TABLE `guru` (
  `id_guru` int NOT NULL,
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

INSERT INTO `guru` (`id_guru`, `nama`, `nrg`, `tmt_guru`, `jabatan`, `status_kepegawaian`, `tipe`, `created_at`, `updated_at`) VALUES
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
  `id_guru_history` int NOT NULL,
  `aksi` enum('tambah','edit','hapus') NOT NULL,
  `id_guru` int DEFAULT NULL,
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
-- Table structure for table `hasil`
--

CREATE TABLE `hasil` (
  `id_penilaian` int NOT NULL,
  `id_item` int NOT NULL COMMENT 'FK ke item.id_item',
  `nilai` tinyint(1) DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `isi`
--

CREATE TABLE `isi` (
  `id_komponen` int NOT NULL COMMENT 'FK ke komponen.id_komponen',
  `nama_indikator` varchar(100) NOT NULL COMMENT 'Nama indikator dalam custom penilaian',
  `urutan_isi` int DEFAULT '0' COMMENT 'Urutan indikator dalam komponen',
  `id_item` int NOT NULL COMMENT 'FK ke item.id_item',
  `nomor_item` varchar(10) NOT NULL COMMENT 'cth: 1.1, 1.2, 2.1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Item pilihan per custom penilaian';

--
-- Dumping data for table `isi`
--

INSERT INTO `isi` (`id_komponen`, `nama_indikator`, `urutan_isi`, `id_item`, `nomor_item`) VALUES
(18, 'Disiplin', 1, 44, '1.1'),
(18, 'Disiplin', 1, 45, '1.2'),
(18, 'Disiplin', 1, 46, '1.3'),
(18, 'Disiplin', 1, 47, '1.4'),
(18, 'Disiplin', 1, 48, '1.5'),
(18, 'Disiplin', 1, 49, '1.6'),
(18, 'Disiplin', 1, 50, '1.7'),
(18, 'Disiplin', 1, 51, '1.8'),
(18, 'Kerjasama', 3, 60, '3.1'),
(18, 'Kerjasama', 3, 61, '3.2'),
(18, 'Kerjasama', 3, 62, '3.3'),
(18, 'Kerjasama', 3, 63, '3.4'),
(18, 'Kerjasama', 3, 64, '3.5'),
(18, 'Kerjasama', 3, 66, '3.6'),
(18, 'Pelaksanaan Pembelajaran', 2, 52, '2.1'),
(18, 'Pelaksanaan Pembelajaran', 2, 53, '2.2'),
(18, 'Pelaksanaan Pembelajaran', 2, 54, '2.3'),
(18, 'Pelaksanaan Pembelajaran', 2, 55, '2.4'),
(18, 'Pelaksanaan Pembelajaran', 2, 56, '2.5'),
(18, 'Pelaksanaan Pembelajaran', 2, 57, '2.6'),
(18, 'Pelaksanaan Pembelajaran', 2, 58, '2.7'),
(18, 'Pelaksanaan Pembelajaran', 2, 59, '2.8'),
(19, 'Disiplin', 1, 44, '1.1'),
(19, 'Disiplin', 1, 45, '1.2'),
(19, 'Disiplin', 1, 46, '1.3'),
(19, 'Disiplin', 1, 47, '1.4'),
(19, 'Disiplin', 1, 48, '1.5'),
(19, 'Disiplin', 1, 49, '1.6'),
(19, 'Disiplin', 1, 50, '1.7'),
(19, 'Disiplin', 1, 51, '1.8'),
(19, 'Kerjasama', 3, 60, '3.1'),
(19, 'Kerjasama', 3, 61, '3.2'),
(19, 'Kerjasama', 3, 62, '3.3'),
(19, 'Kerjasama', 3, 63, '3.4'),
(19, 'Kerjasama', 3, 64, '3.5'),
(19, 'Kerjasama', 3, 65, '3.6'),
(19, 'Pelaksanaan Pembelajaran', 2, 54, '2.1'),
(19, 'Pelaksanaan Pembelajaran', 2, 68, '2.2'),
(19, 'Pelaksanaan Pembelajaran', 2, 69, '2.3'),
(19, 'Pelaksanaan Pembelajaran', 2, 70, '2.4'),
(19, 'Pelaksanaan Pembelajaran', 2, 71, '2.5'),
(19, 'Pelaksanaan Pembelajaran', 2, 72, '2.6'),
(19, 'Pelaksanaan Pembelajaran', 2, 73, '2.7'),
(19, 'Pelaksanaan Pembelajaran', 2, 74, '2.8'),
(20, 'Disiplin', 1, 44, '1.1'),
(20, 'Disiplin', 1, 45, '1.2'),
(20, 'Disiplin', 1, 46, '1.3'),
(20, 'Disiplin', 1, 47, '1.4'),
(20, 'Disiplin', 1, 48, '1.5'),
(20, 'Disiplin', 1, 49, '1.6'),
(20, 'Disiplin', 1, 50, '1.7'),
(20, 'Disiplin', 1, 51, '1.8'),
(20, 'Kerjasama', 3, 60, '3.1'),
(20, 'Kerjasama', 3, 61, '3.2'),
(20, 'Kerjasama', 3, 62, '3.3'),
(20, 'Kerjasama', 3, 63, '3.4'),
(20, 'Kerjasama', 3, 64, '3.5'),
(20, 'Kerjasama', 3, 65, '3.6'),
(20, 'Pelaksanaan Pembelajaran', 2, 54, '2.1'),
(20, 'Pelaksanaan Pembelajaran', 2, 68, '2.2'),
(20, 'Pelaksanaan Pembelajaran', 2, 70, '2.3'),
(20, 'Pelaksanaan Pembelajaran', 2, 71, '2.4'),
(20, 'Pelaksanaan Pembelajaran', 2, 72, '2.5'),
(20, 'Pelaksanaan Pembelajaran', 2, 73, '2.6'),
(20, 'Pelaksanaan Pembelajaran', 2, 74, '2.7'),
(21, 'Disiplin', 1, 44, '1.1'),
(21, 'Disiplin', 1, 45, '1.2'),
(21, 'Disiplin', 1, 46, '1.3'),
(21, 'Disiplin', 1, 47, '1.4'),
(21, 'Disiplin', 1, 48, '1.5'),
(21, 'Disiplin', 1, 49, '1.6'),
(21, 'Disiplin', 1, 50, '1.7'),
(21, 'Disiplin', 1, 51, '1.8'),
(21, 'Kerjasama', 3, 60, '3.1'),
(21, 'Kerjasama', 3, 61, '3.2'),
(21, 'Kerjasama', 3, 62, '3.3'),
(21, 'Kerjasama', 3, 63, '3.4'),
(21, 'Kerjasama', 3, 64, '3.5'),
(21, 'Kerjasama', 3, 65, '3.6'),
(21, 'Pelaksanaan Pembelajaran', 2, 54, '2.1'),
(21, 'Pelaksanaan Pembelajaran', 2, 68, '2.2'),
(21, 'Pelaksanaan Pembelajaran', 2, 70, '2.3'),
(21, 'Pelaksanaan Pembelajaran', 2, 74, '2.4');

-- --------------------------------------------------------

--
-- Table structure for table `item`
--

CREATE TABLE `item` (
  `id_item` int NOT NULL,
  `nama_item` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Bank soal item penilaian global (rename dari item_master)';

--
-- Dumping data for table `item`
--

INSERT INTO `item` (`id_item`, `nama_item`, `created_at`) VALUES
(44, 'Persentasi Kehadiran', '2026-04-10 07:26:42'),
(45, 'Datang di sekolah tepat waktu dan kepulangan tepat waktu', '2026-04-10 07:36:08'),
(46, 'Berpakaian seragam sesuai ketentuan', '2026-04-10 07:36:31'),
(47, 'Ikut serta dalam upacara dan senam di sekolah', '2026-04-10 07:36:54'),
(48, 'Ikut serta dalam rapat-rapat di sekolah', '2026-04-10 07:37:55'),
(49, 'Ikut serta dalam Inspirasi Pagi', '2026-04-10 07:38:21'),
(50, 'Kehadiran pembinaan sabtu', '2026-04-10 07:38:37'),
(51, 'Adab minta izin dan menulis dibuku izin', '2026-04-10 07:39:45'),
(52, 'Menuliskan hasil evaluasi tahsin, tahfizh dan adab', '2026-04-10 07:40:22'),
(53, 'Menyampaikan materi pembelajaran bahasa arab', '2026-04-10 07:40:52'),
(54, 'Metode pengajaran', '2026-04-10 07:41:08'),
(55, 'Membuat halaqoh yang tertib', '2026-04-10 07:41:26'),
(56, 'Mengajar sesuai SOP', '2026-04-10 07:41:41'),
(57, 'Kedisiplinan masuk dan keluar halaqoh', '2026-04-10 07:41:58'),
(58, 'Menggunakan bahasa arab pembukaan dan penutupan halaqoh', '2026-04-10 07:42:23'),
(59, 'Menyampaikan capaian perkembangan siswa kepada orang tua', '2026-04-10 07:42:44'),
(60, 'Melaksanakan tugas yang diberikan oleh Kepala Sekolah', '2026-04-10 07:43:06'),
(61, 'Membantu teman dalam ikut memecahkan masalah KBM', '2026-04-10 07:43:41'),
(62, 'Menciptakan hubungan yang harmonis dengan orangtua/wali murid', '2026-04-10 07:44:15'),
(63, 'Menciptakan hubungan yang harmonis dengan guru dan GTK', '2026-04-10 07:44:43'),
(64, 'Amanah dan Aktif berpartisipasi dalam kepanitiaan', '2026-04-10 07:45:16'),
(65, 'Piket kedatangan dan kepulangan', '2026-04-10 07:45:36'),
(66, 'Kerjasama dengan guru kelas', '2026-04-10 07:45:53'),
(68, 'Membuat Administrasi Pembelajaran (Promes, Modul Ajar, Jurnal Harian, Dll)', '2026-04-10 08:30:58'),
(69, 'Melaksanakan Pembelajaran P5 dan Life Skill', '2026-04-10 08:31:24'),
(70, 'Membuat Program Perbaikan', '2026-04-10 08:31:50'),
(71, 'Membimbing Siswa Wudhu', '2026-04-10 08:32:04'),
(72, 'Kebersihan dan kerapian kelas', '2026-04-10 08:32:21'),
(73, 'Membimbing Siswa Sholat, Dzikir, dan Doa', '2026-04-10 08:32:52'),
(74, 'Memuat Kurikulum Keislaman', '2026-04-10 08:33:05');

-- --------------------------------------------------------

--
-- Table structure for table `komponen`
--

CREATE TABLE `komponen` (
  `id_komponen` int NOT NULL,
  `ta_komponen` varchar(255) NOT NULL COMMENT 'Nama/deskripsi komponen',
  `type_guru` varchar(20) NOT NULL COMMENT 'FK ke tipe_guru.kode'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Tabel komponen penilaian tambahan';

--
-- Dumping data for table `komponen`
--

INSERT INTO `komponen` (`id_komponen`, `ta_komponen`, `type_guru`) VALUES
(18, '2025/2026', 'guru_quran'),
(19, '2025/2026', 'guru_kelas'),
(20, '2025/2026', 'mapel'),
(21, '2025/2026', 'mapel2');

-- --------------------------------------------------------

--
-- Table structure for table `penilaian`
--

CREATE TABLE `penilaian` (
  `id_penilaian` int NOT NULL,
  `id_guru` int NOT NULL,
  `id_komponen` int DEFAULT NULL COMMENT 'FK ke komponen.id_komponen (custom penilaian yang digunakan)',
  `periode` varchar(100) NOT NULL,
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
  `id_tipe_guru` int NOT NULL,
  `kode` varchar(20) NOT NULL COMMENT 'Slug unik: guru_quran, guru_kelas, mapel, gtk',
  `label` varchar(100) NOT NULL COMMENT 'Label tampil di UI',
  `urutan` int DEFAULT '0' COMMENT 'Urutan tampil di dropdown'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `tipe_guru`
--

INSERT INTO `tipe_guru` (`id_tipe_guru`, `kode`, `label`, `urutan`) VALUES
(1, 'guru_quran', 'Guru Qur\'an', 1),
(2, 'guru_kelas', 'Guru Kelas', 2),
(3, 'mapel', 'Guru Mapel 1', 3),
(4, 'gtk', 'GTK/Staff', 4),
(8, 'mapel2', 'Guru Mapel 2', 5);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id_users` int NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `nama_lengkap` varchar(100) NOT NULL,
  `role` enum('admin','kepala_sekolah') DEFAULT 'admin',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id_users`, `username`, `password`, `nama_lengkap`, `role`, `created_at`) VALUES
(1, 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrator', 'admin', '2026-03-30 02:49:31'),
(2, 'kepala', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Hasyim Ashari, S.T', 'kepala_sekolah', '2026-03-30 02:49:31');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `guru`
--
ALTER TABLE `guru`
  ADD PRIMARY KEY (`id_guru`),
  ADD KEY `fk_guru_tipe` (`tipe`);

--
-- Indexes for table `guru_history`
--
ALTER TABLE `guru_history`
  ADD PRIMARY KEY (`id_guru_history`);

--
-- Indexes for table `hasil`
--
ALTER TABLE `hasil`
  ADD PRIMARY KEY (`id_penilaian`,`id_item`),
  ADD KEY `id_penilaian` (`id_penilaian`),
  ADD KEY `id_item` (`id_item`);

--
-- Indexes for table `isi`
--
ALTER TABLE `isi`
  ADD PRIMARY KEY (`id_komponen`,`nama_indikator`,`id_item`),
  ADD KEY `fk_isi_komponen` (`id_komponen`),
  ADD KEY `fk_isi_item` (`id_item`);

--
-- Indexes for table `item`
--
ALTER TABLE `item`
  ADD PRIMARY KEY (`id_item`),
  ADD UNIQUE KEY `uq_nama_item` (`nama_item`);

--
-- Indexes for table `komponen`
--
ALTER TABLE `komponen`
  ADD PRIMARY KEY (`id_komponen`),
  ADD KEY `fk_komponen_type_guru` (`type_guru`);

--
-- Indexes for table `penilaian`
--
ALTER TABLE `penilaian`
  ADD PRIMARY KEY (`id_penilaian`),
  ADD KEY `id_guru` (`id_guru`),
  ADD KEY `fk_penilaian_komponen` (`id_komponen`);

--
-- Indexes for table `tipe_guru`
--
ALTER TABLE `tipe_guru`
  ADD PRIMARY KEY (`id_tipe_guru`),
  ADD UNIQUE KEY `kode` (`kode`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id_users`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `guru`
--
ALTER TABLE `guru`
  MODIFY `id_guru` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=67;

--
-- AUTO_INCREMENT for table `guru_history`
--
ALTER TABLE `guru_history`
  MODIFY `id_guru_history` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `item`
--
ALTER TABLE `item`
  MODIFY `id_item` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=75;

--
-- AUTO_INCREMENT for table `komponen`
--
ALTER TABLE `komponen`
  MODIFY `id_komponen` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `penilaian`
--
ALTER TABLE `penilaian`
  MODIFY `id_penilaian` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=61;

--
-- AUTO_INCREMENT for table `tipe_guru`
--
ALTER TABLE `tipe_guru`
  MODIFY `id_tipe_guru` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id_users` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `guru`
--
ALTER TABLE `guru`
  ADD CONSTRAINT `fk_guru_tipe` FOREIGN KEY (`tipe`) REFERENCES `tipe_guru` (`kode`) ON DELETE RESTRICT ON UPDATE CASCADE;

--
-- Constraints for table `hasil`
--
ALTER TABLE `hasil`
  ADD CONSTRAINT `hasil_ibfk_1` FOREIGN KEY (`id_penilaian`) REFERENCES `penilaian` (`id_penilaian`) ON DELETE CASCADE,
  ADD CONSTRAINT `hasil_ibfk_2` FOREIGN KEY (`id_item`) REFERENCES `item` (`id_item`) ON DELETE CASCADE;

--
-- Constraints for table `isi`
--
ALTER TABLE `isi`
  ADD CONSTRAINT `fk_isi_item` FOREIGN KEY (`id_item`) REFERENCES `item` (`id_item`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_isi_komponen` FOREIGN KEY (`id_komponen`) REFERENCES `komponen` (`id_komponen`) ON DELETE CASCADE;

--
-- Constraints for table `komponen`
--
ALTER TABLE `komponen`
  ADD CONSTRAINT `fk_komponen_type_guru` FOREIGN KEY (`type_guru`) REFERENCES `tipe_guru` (`kode`) ON DELETE RESTRICT ON UPDATE CASCADE;

--
-- Constraints for table `penilaian`
--
ALTER TABLE `penilaian`
  ADD CONSTRAINT `penilaian_ibfk_1` FOREIGN KEY (`id_guru`) REFERENCES `guru` (`id_guru`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
