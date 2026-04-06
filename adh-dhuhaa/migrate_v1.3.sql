-- ============================================================
-- MIGRASI: adh_dhuhaa v1.2 → v1.3 (Bugfix & Security Update)
-- Jalankan file ini pada database yang SUDAH ADA (existing install)
-- Untuk instalasi BARU: gunakan adh_dhuhaa.sql (sudah include semua ini)
-- ============================================================

-- WARN-01: Tambah kolom wajib ganti password
-- Semua user existing di-set must_change_password = 0 (tidak dipaksa)
-- Hanya user baru yang dibuat dengan password default = 1
ALTER TABLE `users`
    ADD COLUMN IF NOT EXISTS `must_change_password`
    TINYINT(1) NOT NULL DEFAULT '0'
    COMMENT 'Wajib ganti password saat login: 1=ya, 0=tidak'
    AFTER `role`;

-- Jika ingin paksa SEMUA user ganti password sekarang (opsional):
-- UPDATE `users` SET must_change_password = 1;

-- SARAN-05: Tambah index performa yang belum ada
-- Aman dijalankan berulang (IF NOT EXISTS)
ALTER TABLE `guru_history`
    ADD INDEX IF NOT EXISTS `idx_history_guru_id` (`guru_id`),
    ADD INDEX IF NOT EXISTS `idx_history_aksi`    (`aksi`),
    ADD INDEX IF NOT EXISTS `idx_history_waktu`   (`waktu`);

ALTER TABLE `penilaian`
    ADD INDEX IF NOT EXISTS `idx_penilaian_guru_periode` (`guru_id`, `periode_awal`, `periode_akhir`),
    ADD INDEX IF NOT EXISTS `idx_penilaian_periode`      (`periode`),
    ADD INDEX IF NOT EXISTS `idx_penilaian_tanggal`      (`tanggal_penilaian`);

-- Verifikasi hasil
SELECT 'Migrasi v1.3 selesai' AS status;
SELECT
    COLUMN_NAME,
    COLUMN_TYPE,
    COLUMN_DEFAULT,
    IS_NULLABLE,
    COLUMN_COMMENT
FROM information_schema.COLUMNS
WHERE TABLE_SCHEMA = DATABASE()
  AND TABLE_NAME   = 'users'
  AND COLUMN_NAME  = 'must_change_password';
