-- OS::JOB v0.11.0 : exécuter UNE FOIS sur la base os_job après sauvegarde.
CREATE TABLE IF NOT EXISTS cv_studio_documents (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 user_id BIGINT UNSIGNED NOT NULL,
 job_offer_id BIGINT UNSIGNED NULL,
 title VARCHAR(220) NOT NULL,
 template_key ENUM('executive','ats') NOT NULL DEFAULT 'executive',
 content_json LONGTEXT NOT NULL,
 include_photo TINYINT(1) NOT NULL DEFAULT 0,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 INDEX idx_cv_studio_owner (user_id,updated_at),
 CONSTRAINT fk_cv_studio_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
 CONSTRAINT fk_cv_studio_offer FOREIGN KEY (job_offer_id) REFERENCES job_offers(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
