CREATE TABLE IF NOT EXISTS customer_activity (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    customer_id BIGINT UNSIGNED NOT NULL,
    type VARCHAR(30) NOT NULL DEFAULT 'task',
    subject VARCHAR(190) NOT NULL,
    description TEXT NULL,
    scheduled_at DATETIME NULL,
    status VARCHAR(30) NOT NULL DEFAULT 'planned',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY customer_activity_customer_index (customer_id),
    KEY customer_activity_scheduled_index (scheduled_at),
    CONSTRAINT customer_activity_customer_fk FOREIGN KEY (customer_id) REFERENCES customer(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
