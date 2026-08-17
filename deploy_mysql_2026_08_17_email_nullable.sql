-- =====================================================================
-- MANUAL DB DEPLOYMENT for no-CLI / GoDaddy phpMyAdmin
-- Run statements IN ORDER in phpMyAdmin 'SQL' tab.
-- Date: 2026-08-17
-- Feature: Bulk student upload - make students.email optional
-- =====================================================================

-- 1) Allow students to be saved without an email ----------------------
ALTER TABLE students
  MODIFY COLUMN email VARCHAR(255) NULL;

-- =====================================================================
-- OPTIONAL - only if you later get CLI access and want 'php artisan
-- migrate' to NOT re-run the steps above. Inserting the record below
-- marks the migration as already applied.
-- =====================================================================
-- INSERT INTO migrations (migration, batch) VALUES
-- ('2026_08_17_000002_make_students_email_nullable', 999);