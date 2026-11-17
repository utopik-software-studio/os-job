-- OS::JOB v0.12.1 lot 4 : migration non destructive.
-- Exécuter une seule fois via phpMyAdmin sur la base os_job.
CREATE TABLE IF NOT EXISTS offer_score_reviews (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id BIGINT UNSIGNED NOT NULL,
  job_offer_id BIGINT UNSIGNED NOT NULL,
  ai_analysis_id BIGINT UNSIGNED NOT NULL,
  baseline_score TINYINT UNSIGNED NOT NULL,
  evidence_score TINYINT UNSIGNED NOT NULL,
  weight_percent TINYINT UNSIGNED NOT NULL,
  proposed_score TINYINT UNSIGNED NOT NULL,
  rationale_json LONGTEXT NOT NULL,
  decision ENUM('proposed','accepted','rejected') NOT NULL DEFAULT 'proposed',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  decided_at DATETIME NULL,
  PRIMARY KEY (id),
  INDEX idx_score_reviews_user_offer (user_id,job_offer_id,id),
  INDEX idx_score_reviews_analysis (ai_analysis_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
