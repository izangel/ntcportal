-- =====================================================================
-- MANUAL DB DEPLOYMENT for no-CLI / GoDaddy phpMyAdmin
-- Run statements IN ORDER in phpMyAdmin 'SQL' tab.
-- Date: 2026-08-17
-- Feature: Bulk student upload extra fields
--   contact_number & student_id_name (all optional / nullable)
-- =====================================================================

-- 1) contact_number (optional) ---------------------------------------
ALTER TABLE students
  ADD COLUMN contact_number VARCHAR(255) NULL AFTER birthday;

-- 2) student_id_name (optional; display label, distinct from student_id)
ALTER TABLE students
  ADD COLUMN student_id_name VARCHAR(255) NULL AFTER student_id;

-- =====================================================================
-- OPTIONAL - only if you later get CLI access and want 'php artisan
-- migrate' to NOT re-run the steps above. Inserting the record below
-- marks the migration as already applied.
-- =====================================================================
-- INSERT INTO migrations (migration, batch) VALUES
-- ('2026_08_17_000001_add_contact_and_student_id_name_to_students_table', 999);