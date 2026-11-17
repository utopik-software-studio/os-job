-- OS::JOB v0.8.0 - exécuter UNE seule fois, après 007. Aucune suppression ou modification de données historiques.
CREATE TABLE IF NOT EXISTS document_backgrounds (
 document_id BIGINT UNSIGNED NOT NULL PRIMARY KEY,
 original_name VARCHAR(255) NOT NULL,
 stored_name VARCHAR(80) NOT NULL,
 mime_type VARCHAR(40) NOT NULL,
 created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
 CONSTRAINT fk_doc_background FOREIGN KEY (document_id) REFERENCES generated_documents(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS document_offer_analyses (
 document_id BIGINT UNSIGNED NOT NULL PRIMARY KEY,
 offer_id BIGINT UNSIGNED NOT NULL,
 analysis_json LONGTEXT NOT NULL,
 updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 CONSTRAINT fk_doc_analysis FOREIGN KEY (document_id) REFERENCES generated_documents(id) ON DELETE CASCADE,
 CONSTRAINT fk_analysis_offer FOREIGN KEY (offer_id) REFERENCES job_offers(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
