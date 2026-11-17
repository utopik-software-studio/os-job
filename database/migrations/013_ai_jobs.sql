-- OS::JOB v0.9.6 — file de tâches IA (migration additive)
CREATE TABLE IF NOT EXISTS offer_ai_jobs (
 id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
 user_id BIGINT UNSIGNED NOT NULL,
 job_offer_id BIGINT UNSIGNED NOT NULL,
 status ENUM('pending','running','completed','failed') NOT NULL DEFAULT 'pending',
 error_message VARCHAR(600) DEFAULT NULL,
 created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
 started_at DATETIME DEFAULT NULL,
 finished_at DATETIME DEFAULT NULL,
 INDEX idx_ai_jobs_owner (user_id,job_offer_id,id),
 INDEX idx_ai_jobs_status (status,created_at),
 CONSTRAINT fk_ai_jobs_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
 CONSTRAINT fk_ai_jobs_offer FOREIGN KEY (job_offer_id) REFERENCES job_offers(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
