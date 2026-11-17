-- OS::JOB v0.8.1 — à exécuter UNE SEULE FOIS après 008.
-- Non destructif : aucune suppression ou modification de données existantes.
CREATE TABLE IF NOT EXISTS application_document_links (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 application_id BIGINT UNSIGNED NOT NULL,
 generated_document_id BIGINT UNSIGNED NOT NULL,
 linked_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
 UNIQUE KEY uq_app_generated (application_id, generated_document_id),
 CONSTRAINT fk_adl_app FOREIGN KEY(application_id) REFERENCES applications(id) ON DELETE RESTRICT,
 CONSTRAINT fk_adl_doc FOREIGN KEY(generated_document_id) REFERENCES generated_documents(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS application_document_dispatches (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 application_id BIGINT UNSIGNED NOT NULL,
 generated_document_id BIGINT UNSIGNED NULL,
 application_document_id BIGINT UNSIGNED NULL,
 sent_at DATETIME NOT NULL,
 channel ENUM('email','platform','post','hand','other') NOT NULL DEFAULT 'email',
 recipient VARCHAR(190) NULL,
 notes TEXT NULL,
 snapshot_filename VARCHAR(100) NOT NULL,
 snapshot_original_name VARCHAR(255) NOT NULL,
 snapshot_mime VARCHAR(100) NOT NULL,
 snapshot_sha256 CHAR(64) NOT NULL,
 created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
 INDEX idx_dispatch_app (application_id,sent_at),
 CONSTRAINT fk_add_app FOREIGN KEY(application_id) REFERENCES applications(id) ON DELETE RESTRICT,
 CONSTRAINT fk_add_generated FOREIGN KEY(generated_document_id) REFERENCES generated_documents(id) ON DELETE RESTRICT,
 CONSTRAINT fk_add_uploaded FOREIGN KEY(application_document_id) REFERENCES application_documents(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
