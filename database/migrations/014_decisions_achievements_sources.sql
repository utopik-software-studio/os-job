-- OS::JOB v0.10.0. Importer une seule fois après sauvegarde.
CREATE TABLE IF NOT EXISTS application_decisions (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 user_id BIGINT UNSIGNED NOT NULL,
 application_id BIGINT UNSIGNED NOT NULL,
 decision ENUM('apply','investigate','discard') NOT NULL,
 reason TEXT NOT NULL,
 decided_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
 INDEX idx_decisions_app (application_id,decided_at),
 CONSTRAINT fk_decisions_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
 CONSTRAINT fk_decisions_application FOREIGN KEY (application_id) REFERENCES applications(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS career_achievements (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 user_id BIGINT UNSIGNED NOT NULL,
 title VARCHAR(190) NOT NULL,
 organization VARCHAR(190) DEFAULT NULL,
 context_text TEXT DEFAULT NULL,
 actions_text TEXT NOT NULL,
 results_text TEXT DEFAULT NULL,
 evidence_text TEXT DEFAULT NULL,
 created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
 INDEX idx_achievements_user (user_id),
 CONSTRAINT fk_achievements_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
ALTER TABLE job_offers ADD COLUMN discovery_platform VARCHAR(80) NULL DEFAULT NULL;
ALTER TABLE applications ADD COLUMN application_platform VARCHAR(80) NULL DEFAULT NULL;
