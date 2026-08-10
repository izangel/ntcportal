-- =====================================================================
-- NTC PORTAL - Manual DB deployment (no-CLI / GoDaddy phpMyAdmin)
-- Run statements IN ORDER in phpMyAdmin "SQL" tab (import the whole file).
-- If a statement errors with "Duplicate column/table/key", that step is
-- ALREADY applied on your server -> skip it and continue.
-- =====================================================================

-- 1) system_updates table ------------------------------------------------
-- Your server's existing table already has all needed columns.
-- Only run the CREATE below if the table is MISSING.
CREATE TABLE IF NOT EXISTS system_updates (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  version_number VARCHAR(255) NULL,
  category VARCHAR(255) NOT NULL DEFAULT 'New Feature',
  title VARCHAR(255) NOT NULL,
  release_date DATE NULL,
  description TEXT NULL,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL
) ENGINE=InnoDB;

-- 2) leave_applications: half-day flag + decimal total ---------------------------------
ALTER TABLE leave_applications ADD COLUMN is_half_day TINYINT(1) NOT NULL DEFAULT 0;
ALTER TABLE leave_applications MODIFY total_days DECIMAL(5,2) NOT NULL DEFAULT 0;

-- 3) leave_credits: change integers to decimals ----------------------------------------
ALTER TABLE leave_credits MODIFY sick_leave DECIMAL(5,2) NOT NULL DEFAULT 15.00;
ALTER TABLE leave_credits MODIFY vacation_leave DECIMAL(5,2) NOT NULL DEFAULT 15.00;
ALTER TABLE leave_credits MODIFY service_incentive_leave DECIMAL(5,2) NOT NULL DEFAULT 15.00;

-- 4) courses: prerequisite column ------------------------------------------------------
ALTER TABLE courses ADD COLUMN prerequisite VARCHAR(255) NULL AFTER units;

-- 5) course_syllabi table --------------------------------------------------------------
CREATE TABLE IF NOT EXISTS course_syllabi (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  course_block_id BIGINT UNSIGNED NOT NULL,
  grading_system TEXT NULL,
  textbooks_references TEXT NULL,
  classroom_policies TEXT NULL,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  UNIQUE KEY course_syllabi_course_block_id_unique (course_block_id),
  CONSTRAINT course_syllabi_course_block_id_foreign FOREIGN KEY (course_block_id) REFERENCES course_blocks(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 6) syllabus_learning_plan_items table ------------------------------------------------
CREATE TABLE IF NOT EXISTS syllabus_learning_plan_items (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  course_syllabus_id BIGINT UNSIGNED NOT NULL,
  learning_outcomes TEXT NULL,
  topics_readings TEXT NULL,
  schedule VARCHAR(255) NULL,
  learning_activities TEXT NULL,
  assessment_tools TEXT NULL,
  sort_order INT NOT NULL DEFAULT 0,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  CONSTRAINT slpi_syllabus_fk FOREIGN KEY (course_syllabus_id) REFERENCES course_syllabi(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 7) course_syllabi: program_id ---------------------------------------------------------
ALTER TABLE course_syllabi ADD COLUMN program_id BIGINT UNSIGNED NULL AFTER course_block_id;

-- Backfill program_id from course -> block (empty on a fresh table, harmless)
UPDATE course_syllabi sy
JOIN course_blocks cb ON cb.id = sy.course_block_id
JOIN courses c ON c.id = cb.course_id
SET sy.program_id = c.program_id
WHERE c.program_id IS NOT NULL AND sy.program_id IS NULL;

ALTER TABLE course_syllabi
  ADD UNIQUE KEY course_syllabi_block_program_unique (course_block_id, program_id),
  ADD CONSTRAINT course_syllabi_program_id_foreign FOREIGN KEY (program_id) REFERENCES programs(id) ON DELETE SET NULL;

-- 8) course_syllabi: submitted_at -------------------------------------------------------
ALTER TABLE course_syllabi ADD COLUMN submitted_at TIMESTAMP NULL AFTER classroom_policies;

-- 9) course_syllabi: course_requirements -------------------------------------------------
ALTER TABLE course_syllabi ADD COLUMN course_requirements TEXT NULL AFTER grading_system;

-- 10) course_syllabi: approval columns --------------------------------------------------
ALTER TABLE course_syllabi
  ADD COLUMN program_head_reviewed_at TIMESTAMP NULL AFTER submitted_at,
  ADD COLUMN program_head_reviewed_by_id BIGINT UNSIGNED NULL AFTER program_head_reviewed_at,
  ADD COLUMN program_head_reviewed_by_name VARCHAR(255) NULL AFTER program_head_reviewed_by_id,
  ADD COLUMN academic_head_approved_at TIMESTAMP NULL AFTER program_head_reviewed_by_name,
  ADD COLUMN academic_head_approved_by_id BIGINT UNSIGNED NULL AFTER academic_head_approved_at,
  ADD COLUMN academic_head_approved_by_name VARCHAR(255) NULL AFTER academic_head_approved_by_id;

-- 11) course_syllabi: revision columns --------------------------------------------------
ALTER TABLE course_syllabi
  ADD COLUMN revision_requested_at TIMESTAMP NULL AFTER academic_head_approved_by_name,
  ADD COLUMN revision_requested_by_id BIGINT UNSIGNED NULL AFTER revision_requested_at,
  ADD COLUMN revision_requested_by_name VARCHAR(255) NULL AFTER revision_requested_by_id,
  ADD COLUMN revision_remarks TEXT NULL AFTER revision_requested_by_name;

-- 12) syllabus_grading_components table ------------------------------------------------
CREATE TABLE IF NOT EXISTS syllabus_grading_components (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  course_syllabus_id BIGINT UNSIGNED NOT NULL,
  assessment_type VARCHAR(255) NOT NULL,
  percentage DECIMAL(5,2) NOT NULL DEFAULT 0,
  sort_order INT NOT NULL DEFAULT 0,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  CONSTRAINT sgc_syllabus_fk FOREIGN KEY (course_syllabus_id) REFERENCES course_syllabi(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 13) program_heads table ----------------------------------------------------------------
CREATE TABLE IF NOT EXISTS program_heads (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  program_id BIGINT UNSIGNED NOT NULL,
  employee_id BIGINT UNSIGNED NOT NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  INDEX program_heads_is_active_index (is_active),
  CONSTRAINT program_heads_program_id_foreign FOREIGN KEY (program_id) REFERENCES programs(id) ON DELETE CASCADE,
  CONSTRAINT program_heads_employee_id_foreign FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 14) course_block_section pivot (create only if missing) -------------------------------
CREATE TABLE IF NOT EXISTS course_block_section (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  section_id BIGINT UNSIGNED NOT NULL,
  course_block_id BIGINT UNSIGNED NOT NULL,
  academic_year_id BIGINT UNSIGNED NULL,
  semester VARCHAR(255) NULL,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  UNIQUE KEY section_block_term_unique (section_id, course_block_id, academic_year_id, semester),
  CONSTRAINT cbs_section_fk FOREIGN KEY (section_id) REFERENCES sections(id) ON DELETE CASCADE,
  CONSTRAINT cbs_block_fk FOREIGN KEY (course_block_id) REFERENCES course_blocks(id) ON DELETE CASCADE,
  CONSTRAINT cbs_ay_fk FOREIGN KEY (academic_year_id) REFERENCES academic_years(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 15) Backfill pivot from legacy course_blocks.section_id -------------------------------
INSERT IGNORE INTO course_block_section (course_block_id, section_id, academic_year_id, semester, created_at, updated_at)
SELECT id, section_id, academic_year_id, semester, NOW(), NOW()
FROM course_blocks
WHERE section_id IS NOT NULL;

-- 16) Drop legacy section_id from course_blocks ------------------------------------------
ALTER TABLE course_blocks DROP FOREIGN KEY course_blocks_section_id_foreign;
ALTER TABLE course_blocks DROP INDEX course_blocks_section_id_foreign;
ALTER TABLE course_blocks DROP COLUMN section_id;

-- =====================================================================
-- OPTIONAL - only if you later get CLI access and want 'php artisan
-- migrate' to NOT re-run the steps above. Inserting the records below
-- marks them as already migrated.
-- =====================================================================
-- INSERT INTO migrations (migration, batch) VALUES
-- ('2026_08_03_054026_add_half_day_to_leave_applications_table', 999),
-- ('2026_08_04_100001_add_prerequisite_to_courses_table', 999),
-- ('2026_08_04_100002_create_course_syllabi_table', 999),
-- ('2026_08_04_100003_create_syllabus_learning_plan_items_table', 999),
-- ('2026_08_04_100004_add_program_id_to_course_syllabi_table', 999),
-- ('2026_08_05_090000_backfill_course_block_section_from_section_id', 999),
-- ('2026_08_05_090100_drop_section_id_from_course_blocks', 999),
-- ('2026_08_06_100000_create_syllabus_grading_components_table', 999),
-- ('2026_08_07_000001_add_submitted_at_to_course_syllabi_table', 999),
-- ('2026_08_07_074932_add_course_requirements_to_course_syllabi_table', 999),
-- ('2026_08_07_084732_add_approval_columns_to_course_syllabi_table', 999),
-- ('2026_08_07_084732_create_program_heads_table', 999),
-- ('2026_08_07_092225_add_revision_columns_to_course_syllabi_table', 999);
-- -- For the renamed system_updates migration, update the old record:
-- UPDATE migrations SET migration = '2026_08_08_000001_create_system_updates_table'
--  WHERE migration = '2026_03_12_031121_create_system_updates_table';