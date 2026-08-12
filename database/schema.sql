CREATE TABLE IF NOT EXISTS owners (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(190) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS content_documents (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    type ENUM('profile','current_system','project','activity','reflection','resume','contact') NOT NULL,
    slug VARCHAR(190) NOT NULL,
    title VARCHAR(190) NOT NULL,
    draft_data JSON NOT NULL,
    published_data JSON NULL,
    is_published TINYINT(1) NOT NULL DEFAULT 0,
    sort_order INT NOT NULL DEFAULT 0,
    published_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY content_type_slug (type, slug),
    INDEX content_public_order (type, is_published, sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS document_layouts (
    id TINYINT UNSIGNED PRIMARY KEY,
    resume_template ENUM('classic','modern') NOT NULL DEFAULT 'classic',
    reflection_template ENUM('academic','journal') NOT NULL DEFAULT 'academic',
    font_family ENUM('Arial','Georgia','Inter') NOT NULL DEFAULT 'Arial',
    font_size DECIMAL(4,1) NOT NULL DEFAULT 10.5,
    line_height DECIMAL(3,2) NOT NULL DEFAULT 1.45,
    section_spacing SMALLINT UNSIGNED NOT NULL DEFAULT 16,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO document_layouts(id) VALUES(1) ON DUPLICATE KEY UPDATE id=id;

CREATE TABLE IF NOT EXISTS visitor_activity (
    visitor_id CHAR(64) PRIMARY KEY,
    last_seen TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX visitor_last_seen (last_seen)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
