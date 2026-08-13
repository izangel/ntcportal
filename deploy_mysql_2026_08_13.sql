-- =====================================================================
-- MANUAL DB DEPLOYMENT for no-CLI / GoDaddy phpMyAdmin
-- Run statements IN ORDER in phpMyAdmin 'SQL' tab.
-- Date: 2026-08-13
-- Feature: Relational course prerequisites (course_prerequisite pivot)
-- =====================================================================

-- 1) course_prerequisite pivot (courses that must be taken before another) --
CREATE TABLE IF NOT EXISTS course_prerequisite (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  course_id BIGINT UNSIGNED NOT NULL,
  prerequisite_course_id BIGINT UNSIGNED NOT NULL,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  UNIQUE KEY course_prerequisite_unique (course_id, prerequisite_course_id),
  CONSTRAINT course_prereq_course_fk FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
  CONSTRAINT course_prereq_prereq_fk FOREIGN KEY (prerequisite_course_id) REFERENCES courses(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- =====================================================================
-- OPTIONAL - only if you later get CLI access and want 'php artisan
-- migrate' to NOT re-run the steps above. Inserting the record below
-- marks the migration as already applied.
-- =====================================================================
-- INSERT INTO migrations (migration, batch) VALUES
-- ('2026_08_13_000001_create_course_prerequisite_table', 999);