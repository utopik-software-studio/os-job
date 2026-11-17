-- OS::JOB 0.7.0 - appliquer UNE fois après 006, sans supprimer de données.
-- Les anciens documents continuent d'utiliser classic et modern.
CREATE TABLE IF NOT EXISTS document_template_variants (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 user_id BIGINT UNSIGNED NOT NULL,
 document_type ENUM('cv','letter') NOT NULL,
 base_key VARCHAR(32) NOT NULL,
 name VARCHAR(120) NOT NULL,
 settings_json LONGTEXT NOT NULL,
 created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
 INDEX idx_variant_user (user_id,document_type),
 CONSTRAINT fk_variant_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS document_presentation (
 document_id BIGINT UNSIGNED PRIMARY KEY,
 settings_json LONGTEXT NOT NULL,
 CONSTRAINT fk_presentation_document FOREIGN KEY (document_id) REFERENCES generated_documents(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
