-- OS::JOB 0.5.0 - Exécuter UNE SEULE FOIS après la migration 004, après sauvegarde.
USE os_job;
CREATE TABLE IF NOT EXISTS offer_scores (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 user_id BIGINT UNSIGNED NOT NULL,
 job_offer_id BIGINT UNSIGNED NOT NULL,
 score TINYINT UNSIGNED NULL,
 details_json TEXT NULL,
 calculated_at DATETIME NOT NULL,
 UNIQUE KEY uq_score_offer_user (user_id,job_offer_id),
 CONSTRAINT fk_score_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
 CONSTRAINT fk_score_offer FOREIGN KEY (job_offer_id) REFERENCES job_offers(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
