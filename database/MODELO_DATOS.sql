-- Modelo inicial para gestor de CV UNAMIS (MySQL 8+)

CREATE TABLE users (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  full_name VARCHAR(180) NOT NULL,
  email VARCHAR(180) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  role ENUM('admin','revisor','usuario') NOT NULL DEFAULT 'usuario',
  active TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL
);

CREATE TABLE cvs (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  user_id BIGINT UNSIGNED NOT NULL,
  status ENUM('draft','in_review','published') NOT NULL DEFAULT 'draft',
  current_version INT NOT NULL DEFAULT 1,
  institutional_address TEXT NOT NULL,
  last_published_at TIMESTAMP NULL,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  CONSTRAINT fk_cvs_user FOREIGN KEY (user_id) REFERENCES users(id)
);

CREATE TABLE cv_localizations (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  cv_id BIGINT UNSIGNED NOT NULL,
  locale ENUM('es','en') NOT NULL,
  title_name VARCHAR(220) NOT NULL,
  office_phone VARCHAR(50) NULL,
  fax_number VARCHAR(50) NULL,
  email VARCHAR(180) NOT NULL,
  position_label VARCHAR(180) NULL,
  completeness_percent DECIMAL(5,2) NOT NULL DEFAULT 0,
  updated_by BIGINT UNSIGNED NULL,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  UNIQUE KEY uk_cv_locale (cv_id, locale),
  CONSTRAINT fk_cvloc_cv FOREIGN KEY (cv_id) REFERENCES cvs(id),
  CONSTRAINT fk_cvloc_user FOREIGN KEY (updated_by) REFERENCES users(id)
);

CREATE TABLE cv_education (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  cv_localization_id BIGINT UNSIGNED NOT NULL,
  institution_id BIGINT UNSIGNED NULL,
  institution_other VARCHAR(220) NULL,
  location_text VARCHAR(220) NULL,
  year_completed SMALLINT NULL,
  degree_id BIGINT UNSIGNED NULL,
  degree_other VARCHAR(220) NULL,
  license_number VARCHAR(120) NULL,
  sort_order INT NOT NULL DEFAULT 1,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  CONSTRAINT fk_edu_cvloc FOREIGN KEY (cv_localization_id) REFERENCES cv_localizations(id)
);

CREATE TABLE cv_professional_experience (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  cv_localization_id BIGINT UNSIGNED NOT NULL,
  institution_id BIGINT UNSIGNED NULL,
  institution_other VARCHAR(220) NULL,
  position VARCHAR(220) NOT NULL,
  start_date DATE NULL,
  end_date DATE NULL,
  is_current TINYINT(1) NOT NULL DEFAULT 0,
  sort_order INT NOT NULL DEFAULT 1,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  CONSTRAINT fk_prof_cvloc FOREIGN KEY (cv_localization_id) REFERENCES cv_localizations(id)
);

CREATE TABLE cv_clinical_research (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  cv_localization_id BIGINT UNSIGNED NOT NULL,
  period_label VARCHAR(80) NOT NULL,
  therapeutic_area_id BIGINT UNSIGNED NULL,
  therapeutic_area_other VARCHAR(220) NULL,
  role_label VARCHAR(150) NOT NULL,
  phase_label VARCHAR(40) NULL,
  sort_order INT NOT NULL DEFAULT 1,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  CONSTRAINT fk_research_cvloc FOREIGN KEY (cv_localization_id) REFERENCES cv_localizations(id)
);

CREATE TABLE cv_trainings (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  cv_localization_id BIGINT UNSIGNED NOT NULL,
  course_name VARCHAR(220) NOT NULL,
  completion_date DATE NULL,
  provider VARCHAR(220) NULL,
  sort_order INT NOT NULL DEFAULT 1,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  CONSTRAINT fk_training_cvloc FOREIGN KEY (cv_localization_id) REFERENCES cv_localizations(id)
);

CREATE TABLE cv_signatures (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  cv_id BIGINT UNSIGNED NOT NULL,
  source_type ENUM('pad','upload') NOT NULL,
  file_path VARCHAR(255) NOT NULL,
  mime_type VARCHAR(80) NOT NULL,
  width INT NULL,
  height INT NULL,
  uploaded_by BIGINT UNSIGNED NOT NULL,
  created_at TIMESTAMP NULL,
  CONSTRAINT fk_sign_cv FOREIGN KEY (cv_id) REFERENCES cvs(id),
  CONSTRAINT fk_sign_user FOREIGN KEY (uploaded_by) REFERENCES users(id)
);

CREATE TABLE cv_documents (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  cv_id BIGINT UNSIGNED NOT NULL,
  locale ENUM('es','en') NOT NULL,
  file_path VARCHAR(255) NOT NULL,
  file_hash_sha256 CHAR(64) NOT NULL,
  folio VARCHAR(50) NOT NULL UNIQUE,
  qr_payload VARCHAR(600) NOT NULL,
  signed_at TIMESTAMP NOT NULL,
  signer_id BIGINT UNSIGNED NULL,
  created_at TIMESTAMP NULL,
  CONSTRAINT fk_doc_cv FOREIGN KEY (cv_id) REFERENCES cvs(id),
  CONSTRAINT fk_doc_signer FOREIGN KEY (signer_id) REFERENCES users(id)
);

CREATE TABLE cv_import_jobs (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  user_id BIGINT UNSIGNED NOT NULL,
  original_file_path VARCHAR(255) NOT NULL,
  detected_locale ENUM('es','en','unknown') NOT NULL DEFAULT 'unknown',
  parse_status ENUM('queued','processing','ready_for_review','failed') NOT NULL DEFAULT 'queued',
  confidence_score DECIMAL(5,2) NULL,
  parsed_payload_json JSON NULL,
  error_message TEXT NULL,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  CONSTRAINT fk_import_user FOREIGN KEY (user_id) REFERENCES users(id)
);

CREATE TABLE catalog_institutions (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  institution_type ENUM('bachillerato','tecnica','universidad','hospital','otro') NOT NULL,
  name VARCHAR(220) NOT NULL,
  state_name VARCHAR(120) NULL,
  country_name VARCHAR(120) NOT NULL DEFAULT 'México',
  active TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL
);

CREATE TABLE catalog_degrees (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  degree_type ENUM('especialidad_medica','carrera_salud','carrera_tecnica','bachillerato','otro') NOT NULL,
  name_es VARCHAR(220) NOT NULL,
  name_en VARCHAR(220) NULL,
  active TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL
);

CREATE TABLE cv_audit_log (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  cv_id BIGINT UNSIGNED NOT NULL,
  user_id BIGINT UNSIGNED NULL,
  event_name VARCHAR(120) NOT NULL,
  event_data JSON NULL,
  created_at TIMESTAMP NOT NULL,
  CONSTRAINT fk_audit_cv FOREIGN KEY (cv_id) REFERENCES cvs(id),
  CONSTRAINT fk_audit_user FOREIGN KEY (user_id) REFERENCES users(id)
);
