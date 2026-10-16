CREATE TABLE IF NOT EXISTS customer_document (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    customer_id BIGINT UNSIGNED NOT NULL,
    title VARCHAR(190) NOT NULL,
    document_type VARCHAR(30) NOT NULL DEFAULT 'other',
    file_name VARCHAR(255) NULL,
    file_path VARCHAR(500) NULL,
    expires_at DATE NULL,
    status VARCHAR(30) NOT NULL DEFAULT 'valid',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY customer_document_customer_index (customer_id),
    KEY customer_document_expires_index (expires_at),
    CONSTRAINT customer_document_customer_fk FOREIGN KEY (customer_id) REFERENCES customer(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
