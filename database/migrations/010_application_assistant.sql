-- OS::JOB 0.9.0 : appliquer une seule fois APRES 009. Aucun DROP/DELETE/ALTER sur les tables existantes.
CREATE TABLE IF NOT EXISTS document_ai_reviews (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 user_id BIGINT UNSIGNED NOT NULL,
 document_id BIGINT UNSIGNED NOT NULL,
 offer_id BIGINT UNSIGNED NULL,
 language_code VARCHAR(2) NOT NULL DEFAULT 'fr',
 engine VARCHAR(40) NOT NULL DEFAULT 'local',
 report_json LONGTEXT NOT NULL,
 created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
 INDEX idx_review_document (document_id,created_at),
 CONSTRAINT fk_review_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
 CONSTRAINT fk_review_doc FOREIGN KEY (document_id) REFERENCES generated_documents(id) ON DELETE CASCADE,
 CONSTRAINT fk_review_offer FOREIGN KEY (offer_id) REFERENCES job_offers(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
