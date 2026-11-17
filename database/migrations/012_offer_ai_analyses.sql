-- OS::JOB v0.9.1 — Migration additive, préserve toutes les données existantes.
CREATE TABLE IF NOT EXISTS offer_ai_analyses (
 id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
 user_id BIGINT UNSIGNED NOT NULL,
 job_offer_id BIGINT UNSIGNED NOT NULL,
 model VARCHAR(80) NOT NULL,
 report_json LONGTEXT NOT NULL,
 created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
 UNIQUE KEY uq_offer_ai_user_offer (user_id, job_offer_id),
 CONSTRAINT fk_offer_ai_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
 CONSTRAINT fk_offer_ai_offer FOREIGN KEY (job_offer_id) REFERENCES job_offers(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
