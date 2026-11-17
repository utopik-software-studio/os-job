-- OS::JOB 0.6.0 : appliquer une fois APRES la migration 005. Ne supprime aucune donnée.
CREATE TABLE IF NOT EXISTS generated_documents (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 user_id BIGINT UNSIGNED NOT NULL,
 job_offer_id BIGINT UNSIGNED NULL,
 document_type ENUM('cv','letter') NOT NULL,
 template_key VARCHAR(32) NOT NULL DEFAULT 'classic',
 title VARCHAR(220) NOT NULL,
 body LONGTEXT NOT NULL,
 include_photo TINYINT(1) NOT NULL DEFAULT 1,
 created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
 updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 INDEX idx_generated_owner (user_id,created_at),
 CONSTRAINT fk_generated_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
 CONSTRAINT fk_generated_offer FOREIGN KEY (job_offer_id) REFERENCES job_offers(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
