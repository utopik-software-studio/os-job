-- OS::JOB v0.12.1 : migration non destructive ; à exécuter une seule fois après sauvegarde.
CREATE TABLE IF NOT EXISTS `application_ai_drafts` (
 `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
 `user_id` BIGINT UNSIGNED NOT NULL,
 `application_id` BIGINT UNSIGNED NOT NULL,
 `kind` ENUM('reply','interview') NOT NULL,
 `title` VARCHAR(190) NOT NULL DEFAULT '',
 `content` MEDIUMTEXT NOT NULL,
 `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
 PRIMARY KEY (`id`), KEY `idx_drafts_owner` (`user_id`,`application_id`,`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
