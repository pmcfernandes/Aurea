CREATE TABLE IF NOT EXISTS customer_opportunity (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    customer_id BIGINT UNSIGNED NOT NULL,
    title VARCHAR(190) NOT NULL,
    amount DECIMAL(14,2) NOT NULL DEFAULT 0,
    stage VARCHAR(30) NOT NULL DEFAULT 'qualification',
    expected_close_at DATE NULL,
    status VARCHAR(30) NOT NULL DEFAULT 'open',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY customer_opportunity_customer_index (customer_id),
    KEY customer_opportunity_status_index (status),
    CONSTRAINT customer_opportunity_customer_fk FOREIGN KEY (customer_id) REFERENCES customer(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
