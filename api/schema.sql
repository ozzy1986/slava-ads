-- Lead storage schema for slava-ads.ru
-- Run once on the server: mysql -u root -p < schema.sql  (after creating DB/user)

CREATE TABLE IF NOT EXISTS leads (
    id            BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    created_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    name          VARCHAR(120) NOT NULL,
    phone         VARCHAR(120) NOT NULL,
    message       TEXT NULL,
    quiz          VARCHAR(255) NULL,
    source        VARCHAR(80) NULL,
    page          VARCHAR(255) NULL,
    referrer      VARCHAR(255) NULL,
    utm_source    VARCHAR(120) NULL,
    utm_medium    VARCHAR(120) NULL,
    utm_campaign  VARCHAR(160) NULL,
    utm_content   VARCHAR(160) NULL,
    utm_term      VARCHAR(160) NULL,
    ip            VARCHAR(45) NULL,
    user_agent    VARCHAR(255) NULL,
    status        ENUM('new','contacted','sent','rejected') NOT NULL DEFAULT 'new',
    PRIMARY KEY (id),
    KEY idx_created (created_at),
    KEY idx_ip_created (ip, created_at),
    KEY idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
