-- OS::JOB v0.14.1 — schema only; no personal data.
-- Import into an EMPTY database named os_job.
-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `os_job`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `applications`
--

CREATE TABLE `applications` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `job_offer_id` bigint(20) UNSIGNED NOT NULL,
  `status` varchar(40) NOT NULL DEFAULT 'to_review',
  `applied_at` datetime DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `application_platform` varchar(80) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `application_ai_drafts`
--

CREATE TABLE `application_ai_drafts` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `application_id` bigint(20) UNSIGNED NOT NULL,
  `kind` enum('reply','interview') NOT NULL,
  `title` varchar(190) NOT NULL DEFAULT '',
  `content` mediumtext NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `application_contacts`
--

CREATE TABLE `application_contacts` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `application_id` bigint(20) UNSIGNED NOT NULL,
  `full_name` varchar(160) NOT NULL,
  `job_title` varchar(160) DEFAULT NULL,
  `email` varchar(190) DEFAULT NULL,
  `phone` varchar(60) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `application_decisions`
--

CREATE TABLE `application_decisions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `application_id` bigint(20) UNSIGNED NOT NULL,
  `decision` enum('apply','investigate','discard') NOT NULL,
  `reason` text NOT NULL,
  `decided_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `application_documents`
--

CREATE TABLE `application_documents` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `application_id` bigint(20) UNSIGNED NOT NULL,
  `document_type` enum('cv','letter','other') NOT NULL DEFAULT 'other',
  `original_name` varchar(255) NOT NULL,
  `stored_name` varchar(100) NOT NULL,
  `mime_type` varchar(100) NOT NULL,
  `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `application_document_dispatches`
--

CREATE TABLE `application_document_dispatches` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `application_id` bigint(20) UNSIGNED NOT NULL,
  `generated_document_id` bigint(20) UNSIGNED DEFAULT NULL,
  `application_document_id` bigint(20) UNSIGNED DEFAULT NULL,
  `sent_at` datetime NOT NULL,
  `channel` enum('email','platform','post','hand','other') NOT NULL DEFAULT 'email',
  `recipient` varchar(190) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `snapshot_filename` varchar(100) NOT NULL,
  `snapshot_original_name` varchar(255) NOT NULL,
  `snapshot_mime` varchar(100) NOT NULL,
  `snapshot_sha256` char(64) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `application_document_links`
--

CREATE TABLE `application_document_links` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `application_id` bigint(20) UNSIGNED NOT NULL,
  `generated_document_id` bigint(20) UNSIGNED NOT NULL,
  `linked_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `application_events`
--

CREATE TABLE `application_events` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `application_id` bigint(20) UNSIGNED NOT NULL,
  `event_type` varchar(40) NOT NULL,
  `details` text DEFAULT NULL,
  `occurred_at` datetime NOT NULL DEFAULT current_timestamp(),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `application_interviews`
--

CREATE TABLE `application_interviews` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `application_id` bigint(20) UNSIGNED NOT NULL,
  `starts_at` datetime NOT NULL,
  `interview_mode` enum('telephone','video','onsite','other') NOT NULL DEFAULT 'video',
  `location` varchar(255) DEFAULT NULL,
  `notes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `application_reminders`
--

CREATE TABLE `application_reminders` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `application_id` bigint(20) UNSIGNED NOT NULL,
  `due_at` datetime NOT NULL,
  `title` varchar(200) NOT NULL,
  `done` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `career_achievements`
--

CREATE TABLE `career_achievements` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(190) NOT NULL,
  `organization` varchar(190) DEFAULT NULL,
  `context_text` text DEFAULT NULL,
  `actions_text` text NOT NULL,
  `results_text` text DEFAULT NULL,
  `evidence_text` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `career_targets`
--

CREATE TABLE `career_targets` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `profile_id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(190) NOT NULL,
  `level` varchar(80) NOT NULL DEFAULT '',
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cv_studio_documents`
--

CREATE TABLE `cv_studio_documents` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `job_offer_id` bigint(20) UNSIGNED DEFAULT NULL,
  `title` varchar(220) NOT NULL,
  `template_key` enum('executive','ats') NOT NULL DEFAULT 'executive',
  `content_json` longtext NOT NULL,
  `include_photo` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `document_ai_reviews`
--

CREATE TABLE `document_ai_reviews` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `document_id` bigint(20) UNSIGNED NOT NULL,
  `offer_id` bigint(20) UNSIGNED DEFAULT NULL,
  `language_code` varchar(2) NOT NULL DEFAULT 'fr',
  `engine` varchar(40) NOT NULL DEFAULT 'local',
  `report_json` longtext NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `document_backgrounds`
--

CREATE TABLE `document_backgrounds` (
  `document_id` bigint(20) UNSIGNED NOT NULL,
  `original_name` varchar(255) NOT NULL,
  `stored_name` varchar(80) NOT NULL,
  `mime_type` varchar(40) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `document_offer_analyses`
--

CREATE TABLE `document_offer_analyses` (
  `document_id` bigint(20) UNSIGNED NOT NULL,
  `offer_id` bigint(20) UNSIGNED NOT NULL,
  `analysis_json` longtext NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `document_presentation`
--

CREATE TABLE `document_presentation` (
  `document_id` bigint(20) UNSIGNED NOT NULL,
  `settings_json` longtext NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `document_template_variants`
--

CREATE TABLE `document_template_variants` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `document_type` enum('cv','letter') NOT NULL,
  `base_key` varchar(32) NOT NULL,
  `name` varchar(120) NOT NULL,
  `settings_json` longtext NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `experiences`
--

CREATE TABLE `experiences` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `profile_id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(190) NOT NULL,
  `organization` varchar(190) NOT NULL DEFAULT '',
  `location` varchar(190) NOT NULL DEFAULT '',
  `start_date` varchar(7) NOT NULL DEFAULT '',
  `end_date` varchar(7) NOT NULL DEFAULT '',
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `generated_documents`
--

CREATE TABLE `generated_documents` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `job_offer_id` bigint(20) UNSIGNED DEFAULT NULL,
  `document_type` enum('cv','letter') NOT NULL,
  `template_key` varchar(32) NOT NULL DEFAULT 'classic',
  `title` varchar(220) NOT NULL,
  `body` longtext NOT NULL,
  `include_photo` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `geo_locations`
--

CREATE TABLE `geo_locations` (
  `id` int(10) UNSIGNED NOT NULL,
  `parent_id` int(10) UNSIGNED DEFAULT NULL,
  `kind` enum('country','region','department','city') NOT NULL,
  `name` varchar(150) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `job_offers`
--

CREATE TABLE `job_offers` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(200) NOT NULL,
  `company` varchar(190) DEFAULT NULL,
  `city` varchar(160) DEFAULT NULL,
  `salary_min` int(10) UNSIGNED DEFAULT NULL,
  `salary_max` int(10) UNSIGNED DEFAULT NULL,
  `source_url` text DEFAULT NULL,
  `description` longtext DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` varchar(40) NOT NULL DEFAULT 'to_review',
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `discovery_platform` varchar(80) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `offer_ai_analyses`
--

CREATE TABLE `offer_ai_analyses` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `job_offer_id` bigint(20) UNSIGNED NOT NULL,
  `model` varchar(80) NOT NULL,
  `report_json` longtext NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `offer_ai_jobs`
--

CREATE TABLE `offer_ai_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `job_offer_id` bigint(20) UNSIGNED NOT NULL,
  `status` enum('pending','running','completed','failed') NOT NULL DEFAULT 'pending',
  `error_message` varchar(600) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `started_at` datetime DEFAULT NULL,
  `finished_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `offer_scores`
--

CREATE TABLE `offer_scores` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `job_offer_id` bigint(20) UNSIGNED NOT NULL,
  `score` tinyint(3) UNSIGNED DEFAULT NULL,
  `details_json` text DEFAULT NULL,
  `calculated_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `offer_score_reviews`
--

CREATE TABLE `offer_score_reviews` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `job_offer_id` bigint(20) UNSIGNED NOT NULL,
  `ai_analysis_id` bigint(20) UNSIGNED NOT NULL,
  `baseline_score` tinyint(3) UNSIGNED NOT NULL,
  `evidence_score` tinyint(3) UNSIGNED NOT NULL,
  `weight_percent` tinyint(3) UNSIGNED NOT NULL,
  `proposed_score` tinyint(3) UNSIGNED NOT NULL,
  `rationale_json` longtext NOT NULL,
  `decision` enum('proposed','accepted','rejected') NOT NULL DEFAULT 'proposed',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `decided_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `profiles`
--

CREATE TABLE `profiles` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `first_name` varchar(100) NOT NULL DEFAULT '',
  `last_name` varchar(100) NOT NULL DEFAULT '',
  `headline` varchar(200) NOT NULL DEFAULT '',
  `email` varchar(190) NOT NULL DEFAULT '',
  `phone` varchar(70) NOT NULL DEFAULT '',
  `city` varchar(160) NOT NULL DEFAULT '',
  `country` varchar(120) NOT NULL DEFAULT '',
  `linkedin_url` varchar(500) NOT NULL DEFAULT '',
  `website_url` varchar(500) NOT NULL DEFAULT '',
  `summary` text DEFAULT NULL,
  `salary_min` int(10) UNSIGNED NOT NULL DEFAULT 45000,
  `preferred_locations` text DEFAULT NULL,
  `mobility` varchar(180) NOT NULL DEFAULT '',
  `work_mode` varchar(80) NOT NULL DEFAULT '',
  `availability` varchar(180) NOT NULL DEFAULT '',
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `photo_path` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `profile_education`
--

CREATE TABLE `profile_education` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `profile_id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(190) NOT NULL,
  `organization` varchar(190) NOT NULL DEFAULT '',
  `start_date` varchar(7) NOT NULL DEFAULT '',
  `end_date` varchar(7) NOT NULL DEFAULT '',
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `profile_languages`
--

CREATE TABLE `profile_languages` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `profile_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `level` varchar(80) NOT NULL DEFAULT '',
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `profile_locations`
--

CREATE TABLE `profile_locations` (
  `profile_id` bigint(20) UNSIGNED NOT NULL,
  `location_id` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `profile_skills`
--

CREATE TABLE `profile_skills` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `profile_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(150) NOT NULL,
  `level` varchar(80) NOT NULL DEFAULT '',
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `search_offer_links`
--

CREATE TABLE `search_offer_links` (
  `search_space_id` bigint(20) UNSIGNED NOT NULL,
  `job_offer_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `search_spaces`
--

CREATE TABLE `search_spaces` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(160) NOT NULL,
  `location_filter` varchar(255) DEFAULT NULL,
  `salary_min` int(10) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `skill_catalog`
--

CREATE TABLE `skill_catalog` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(150) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `email` varchar(190) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
--

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `applications`
--
ALTER TABLE `applications`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_application_offer_user` (`user_id`,`job_offer_id`),
  ADD KEY `job_offer_id` (`job_offer_id`);

--
-- Indices de la tabla `application_ai_drafts`
--
ALTER TABLE `application_ai_drafts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_drafts_owner` (`user_id`,`application_id`,`created_at`);

--
-- Indices de la tabla `application_contacts`
--
ALTER TABLE `application_contacts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `application_id` (`application_id`);

--
-- Indices de la tabla `application_decisions`
--
ALTER TABLE `application_decisions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_decisions_app` (`application_id`,`decided_at`),
  ADD KEY `fk_decisions_user` (`user_id`);

--
-- Indices de la tabla `application_documents`
--
ALTER TABLE `application_documents`
  ADD PRIMARY KEY (`id`),
  ADD KEY `application_id` (`application_id`);

--
-- Indices de la tabla `application_document_dispatches`
--
ALTER TABLE `application_document_dispatches`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_dispatch_app` (`application_id`,`sent_at`),
  ADD KEY `fk_add_generated` (`generated_document_id`),
  ADD KEY `fk_add_uploaded` (`application_document_id`);

--
-- Indices de la tabla `application_document_links`
--
ALTER TABLE `application_document_links`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_app_generated` (`application_id`,`generated_document_id`),
  ADD KEY `fk_adl_doc` (`generated_document_id`);

--
-- Indices de la tabla `application_events`
--
ALTER TABLE `application_events`
  ADD PRIMARY KEY (`id`),
  ADD KEY `application_id` (`application_id`,`occurred_at`);

--
-- Indices de la tabla `application_interviews`
--
ALTER TABLE `application_interviews`
  ADD PRIMARY KEY (`id`),
  ADD KEY `application_id` (`application_id`),
  ADD KEY `starts_at` (`starts_at`);

--
-- Indices de la tabla `application_reminders`
--
ALTER TABLE `application_reminders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `application_id` (`application_id`),
  ADD KEY `due_at` (`due_at`,`done`);

--
-- Indices de la tabla `career_achievements`
--
ALTER TABLE `career_achievements`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_achievements_user` (`user_id`);

--
-- Indices de la tabla `career_targets`
--
ALTER TABLE `career_targets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `profile_id` (`profile_id`);

--
-- Indices de la tabla `cv_studio_documents`
--
ALTER TABLE `cv_studio_documents`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_cv_studio_owner` (`user_id`,`updated_at`),
  ADD KEY `fk_cv_studio_offer` (`job_offer_id`);

--
-- Indices de la tabla `document_ai_reviews`
--
ALTER TABLE `document_ai_reviews`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_review_document` (`document_id`,`created_at`),
  ADD KEY `fk_review_user` (`user_id`),
  ADD KEY `fk_review_offer` (`offer_id`);

--
-- Indices de la tabla `document_backgrounds`
--
ALTER TABLE `document_backgrounds`
  ADD PRIMARY KEY (`document_id`);

--
-- Indices de la tabla `document_offer_analyses`
--
ALTER TABLE `document_offer_analyses`
  ADD PRIMARY KEY (`document_id`),
  ADD KEY `fk_analysis_offer` (`offer_id`);

--
-- Indices de la tabla `document_presentation`
--
ALTER TABLE `document_presentation`
  ADD PRIMARY KEY (`document_id`);

--
-- Indices de la tabla `document_template_variants`
--
ALTER TABLE `document_template_variants`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_variant_user` (`user_id`,`document_type`);

--
-- Indices de la tabla `experiences`
--
ALTER TABLE `experiences`
  ADD PRIMARY KEY (`id`),
  ADD KEY `profile_id` (`profile_id`);

--
-- Indices de la tabla `generated_documents`
--
ALTER TABLE `generated_documents`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_generated_owner` (`user_id`,`created_at`),
  ADD KEY `fk_generated_offer` (`job_offer_id`);

--
-- Indices de la tabla `geo_locations`
--
ALTER TABLE `geo_locations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `parent_id` (`parent_id`);

--
-- Indices de la tabla `job_offers`
--
ALTER TABLE `job_offers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_offers_user` (`user_id`);

--
-- Indices de la tabla `offer_ai_analyses`
--
ALTER TABLE `offer_ai_analyses`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_offer_ai_user_offer` (`user_id`,`job_offer_id`),
  ADD KEY `fk_offer_ai_offer` (`job_offer_id`);

--
-- Indices de la tabla `offer_ai_jobs`
--
ALTER TABLE `offer_ai_jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_ai_jobs_owner` (`user_id`,`job_offer_id`,`id`),
  ADD KEY `idx_ai_jobs_status` (`status`,`created_at`),
  ADD KEY `fk_ai_jobs_offer` (`job_offer_id`);

--
-- Indices de la tabla `offer_scores`
--
ALTER TABLE `offer_scores`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_score_offer_user` (`user_id`,`job_offer_id`),
  ADD KEY `fk_score_offer` (`job_offer_id`);

--
-- Indices de la tabla `offer_score_reviews`
--
ALTER TABLE `offer_score_reviews`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_score_reviews_user_offer` (`user_id`,`job_offer_id`,`id`),
  ADD KEY `idx_score_reviews_analysis` (`ai_analysis_id`);

--
-- Indices de la tabla `profiles`
--
ALTER TABLE `profiles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`);

--
-- Indices de la tabla `profile_education`
--
ALTER TABLE `profile_education`
  ADD PRIMARY KEY (`id`),
  ADD KEY `profile_id` (`profile_id`);

--
-- Indices de la tabla `profile_languages`
--
ALTER TABLE `profile_languages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `profile_id` (`profile_id`);

--
-- Indices de la tabla `profile_locations`
--
ALTER TABLE `profile_locations`
  ADD PRIMARY KEY (`profile_id`,`location_id`),
  ADD KEY `location_id` (`location_id`);

--
-- Indices de la tabla `profile_skills`
--
ALTER TABLE `profile_skills`
  ADD PRIMARY KEY (`id`),
  ADD KEY `profile_id` (`profile_id`);

--
-- Indices de la tabla `search_offer_links`
--
ALTER TABLE `search_offer_links`
  ADD PRIMARY KEY (`search_space_id`,`job_offer_id`),
  ADD KEY `job_offer_id` (`job_offer_id`);

--
-- Indices de la tabla `search_spaces`
--
ALTER TABLE `search_spaces`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_spaces_user` (`user_id`);

--
-- Indices de la tabla `skill_catalog`
--
ALTER TABLE `skill_catalog`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indices de la tabla `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `applications`
--
ALTER TABLE `applications`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

--
-- AUTO_INCREMENT de la tabla `application_ai_drafts`
--
ALTER TABLE `application_ai_drafts`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

--
-- AUTO_INCREMENT de la tabla `application_contacts`
--
ALTER TABLE `application_contacts`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

--
-- AUTO_INCREMENT de la tabla `application_decisions`
--
ALTER TABLE `application_decisions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

--
-- AUTO_INCREMENT de la tabla `application_documents`
--
ALTER TABLE `application_documents`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

--
-- AUTO_INCREMENT de la tabla `application_document_dispatches`
--
ALTER TABLE `application_document_dispatches`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

--
-- AUTO_INCREMENT de la tabla `application_document_links`
--
ALTER TABLE `application_document_links`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

--
-- AUTO_INCREMENT de la tabla `application_events`
--
ALTER TABLE `application_events`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

--
-- AUTO_INCREMENT de la tabla `application_interviews`
--
ALTER TABLE `application_interviews`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

--
-- AUTO_INCREMENT de la tabla `application_reminders`
--
ALTER TABLE `application_reminders`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

--
-- AUTO_INCREMENT de la tabla `career_achievements`
--
ALTER TABLE `career_achievements`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

--
-- AUTO_INCREMENT de la tabla `career_targets`
--
ALTER TABLE `career_targets`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

--
-- AUTO_INCREMENT de la tabla `cv_studio_documents`
--
ALTER TABLE `cv_studio_documents`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

--
-- AUTO_INCREMENT de la tabla `document_ai_reviews`
--
ALTER TABLE `document_ai_reviews`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

--
-- AUTO_INCREMENT de la tabla `document_template_variants`
--
ALTER TABLE `document_template_variants`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

--
-- AUTO_INCREMENT de la tabla `experiences`
--
ALTER TABLE `experiences`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

--
-- AUTO_INCREMENT de la tabla `generated_documents`
--
ALTER TABLE `generated_documents`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

--
-- AUTO_INCREMENT de la tabla `geo_locations`
--
ALTER TABLE `geo_locations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

--
-- AUTO_INCREMENT de la tabla `job_offers`
--
ALTER TABLE `job_offers`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

--
-- AUTO_INCREMENT de la tabla `offer_ai_analyses`
--
ALTER TABLE `offer_ai_analyses`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

--
-- AUTO_INCREMENT de la tabla `offer_ai_jobs`
--
ALTER TABLE `offer_ai_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

--
-- AUTO_INCREMENT de la tabla `offer_scores`
--
ALTER TABLE `offer_scores`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

--
-- AUTO_INCREMENT de la tabla `offer_score_reviews`
--
ALTER TABLE `offer_score_reviews`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

--
-- AUTO_INCREMENT de la tabla `profiles`
--
ALTER TABLE `profiles`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

--
-- AUTO_INCREMENT de la tabla `profile_education`
--
ALTER TABLE `profile_education`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

--
-- AUTO_INCREMENT de la tabla `profile_languages`
--
ALTER TABLE `profile_languages`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

--
-- AUTO_INCREMENT de la tabla `profile_skills`
--
ALTER TABLE `profile_skills`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

--
-- AUTO_INCREMENT de la tabla `search_spaces`
--
ALTER TABLE `search_spaces`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

--
-- AUTO_INCREMENT de la tabla `skill_catalog`
--
ALTER TABLE `skill_catalog`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

--
-- AUTO_INCREMENT de la tabla `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `applications`
--
ALTER TABLE `applications`
  ADD CONSTRAINT `applications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `applications_ibfk_2` FOREIGN KEY (`job_offer_id`) REFERENCES `job_offers` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `application_contacts`
--
ALTER TABLE `application_contacts`
  ADD CONSTRAINT `application_contacts_ibfk_1` FOREIGN KEY (`application_id`) REFERENCES `applications` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `application_decisions`
--
ALTER TABLE `application_decisions`
  ADD CONSTRAINT `fk_decisions_application` FOREIGN KEY (`application_id`) REFERENCES `applications` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_decisions_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `application_documents`
--
ALTER TABLE `application_documents`
  ADD CONSTRAINT `application_documents_ibfk_1` FOREIGN KEY (`application_id`) REFERENCES `applications` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `application_document_dispatches`
--
ALTER TABLE `application_document_dispatches`
  ADD CONSTRAINT `fk_add_app` FOREIGN KEY (`application_id`) REFERENCES `applications` (`id`),
  ADD CONSTRAINT `fk_add_generated` FOREIGN KEY (`generated_document_id`) REFERENCES `generated_documents` (`id`),
  ADD CONSTRAINT `fk_add_uploaded` FOREIGN KEY (`application_document_id`) REFERENCES `application_documents` (`id`);

--
-- Filtros para la tabla `application_document_links`
--
ALTER TABLE `application_document_links`
  ADD CONSTRAINT `fk_adl_app` FOREIGN KEY (`application_id`) REFERENCES `applications` (`id`),
  ADD CONSTRAINT `fk_adl_doc` FOREIGN KEY (`generated_document_id`) REFERENCES `generated_documents` (`id`);

--
-- Filtros para la tabla `application_events`
--
ALTER TABLE `application_events`
  ADD CONSTRAINT `application_events_ibfk_1` FOREIGN KEY (`application_id`) REFERENCES `applications` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `application_interviews`
--
ALTER TABLE `application_interviews`
  ADD CONSTRAINT `application_interviews_ibfk_1` FOREIGN KEY (`application_id`) REFERENCES `applications` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `application_reminders`
--
ALTER TABLE `application_reminders`
  ADD CONSTRAINT `application_reminders_ibfk_1` FOREIGN KEY (`application_id`) REFERENCES `applications` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `career_achievements`
--
ALTER TABLE `career_achievements`
  ADD CONSTRAINT `fk_achievements_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `career_targets`
--
ALTER TABLE `career_targets`
  ADD CONSTRAINT `career_targets_ibfk_1` FOREIGN KEY (`profile_id`) REFERENCES `profiles` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `cv_studio_documents`
--
ALTER TABLE `cv_studio_documents`
  ADD CONSTRAINT `fk_cv_studio_offer` FOREIGN KEY (`job_offer_id`) REFERENCES `job_offers` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_cv_studio_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `document_ai_reviews`
--
ALTER TABLE `document_ai_reviews`
  ADD CONSTRAINT `fk_review_doc` FOREIGN KEY (`document_id`) REFERENCES `generated_documents` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_review_offer` FOREIGN KEY (`offer_id`) REFERENCES `job_offers` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_review_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `document_backgrounds`
--
ALTER TABLE `document_backgrounds`
  ADD CONSTRAINT `fk_doc_background` FOREIGN KEY (`document_id`) REFERENCES `generated_documents` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `document_offer_analyses`
--
ALTER TABLE `document_offer_analyses`
  ADD CONSTRAINT `fk_analysis_offer` FOREIGN KEY (`offer_id`) REFERENCES `job_offers` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_doc_analysis` FOREIGN KEY (`document_id`) REFERENCES `generated_documents` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `document_presentation`
--
ALTER TABLE `document_presentation`
  ADD CONSTRAINT `fk_presentation_document` FOREIGN KEY (`document_id`) REFERENCES `generated_documents` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `document_template_variants`
--
ALTER TABLE `document_template_variants`
  ADD CONSTRAINT `fk_variant_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `experiences`
--
ALTER TABLE `experiences`
  ADD CONSTRAINT `experiences_ibfk_1` FOREIGN KEY (`profile_id`) REFERENCES `profiles` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `generated_documents`
--
ALTER TABLE `generated_documents`
  ADD CONSTRAINT `fk_generated_offer` FOREIGN KEY (`job_offer_id`) REFERENCES `job_offers` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_generated_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `geo_locations`
--
ALTER TABLE `geo_locations`
  ADD CONSTRAINT `geo_locations_ibfk_1` FOREIGN KEY (`parent_id`) REFERENCES `geo_locations` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `job_offers`
--
ALTER TABLE `job_offers`
  ADD CONSTRAINT `fk_offers_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `offer_ai_analyses`
--
ALTER TABLE `offer_ai_analyses`
  ADD CONSTRAINT `fk_offer_ai_offer` FOREIGN KEY (`job_offer_id`) REFERENCES `job_offers` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_offer_ai_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `offer_ai_jobs`
--
ALTER TABLE `offer_ai_jobs`
  ADD CONSTRAINT `fk_ai_jobs_offer` FOREIGN KEY (`job_offer_id`) REFERENCES `job_offers` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_ai_jobs_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `offer_scores`
--
ALTER TABLE `offer_scores`
  ADD CONSTRAINT `fk_score_offer` FOREIGN KEY (`job_offer_id`) REFERENCES `job_offers` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_score_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `profiles`
--
ALTER TABLE `profiles`
  ADD CONSTRAINT `profiles_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `profile_education`
--
ALTER TABLE `profile_education`
  ADD CONSTRAINT `profile_education_ibfk_1` FOREIGN KEY (`profile_id`) REFERENCES `profiles` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `profile_languages`
--
ALTER TABLE `profile_languages`
  ADD CONSTRAINT `profile_languages_ibfk_1` FOREIGN KEY (`profile_id`) REFERENCES `profiles` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `profile_locations`
--
ALTER TABLE `profile_locations`
  ADD CONSTRAINT `profile_locations_ibfk_1` FOREIGN KEY (`profile_id`) REFERENCES `profiles` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `profile_locations_ibfk_2` FOREIGN KEY (`location_id`) REFERENCES `geo_locations` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `profile_skills`
--
ALTER TABLE `profile_skills`
  ADD CONSTRAINT `profile_skills_ibfk_1` FOREIGN KEY (`profile_id`) REFERENCES `profiles` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `search_offer_links`
--
ALTER TABLE `search_offer_links`
  ADD CONSTRAINT `search_offer_links_ibfk_1` FOREIGN KEY (`search_space_id`) REFERENCES `search_spaces` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `search_offer_links_ibfk_2` FOREIGN KEY (`job_offer_id`) REFERENCES `job_offers` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `search_spaces`
--
ALTER TABLE `search_spaces`
  ADD CONSTRAINT `fk_spaces_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
