CREATE TABLE IF NOT EXISTS user_group (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    description VARCHAR(255) NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY user_group_name_unique (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS user_group_member (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    group_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY user_group_member_unique (group_id, user_id),
    CONSTRAINT user_group_member_group_fk FOREIGN KEY (group_id) REFERENCES user_group(id) ON DELETE CASCADE,
    CONSTRAINT user_group_member_user_fk FOREIGN KEY (user_id) REFERENCES crm_user(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS user_group_permission (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    group_id BIGINT UNSIGNED NOT NULL,
    controller VARCHAR(100) NOT NULL,
    action VARCHAR(100) NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY user_group_permission_unique (group_id, controller, action),
    CONSTRAINT user_group_permission_group_fk FOREIGN KEY (group_id) REFERENCES user_group(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO user_group (id, name, description) VALUES (1, 'Administradores', 'Acesso total à aplicação');
INSERT IGNORE INTO user_group_member (group_id, user_id) VALUES (1, 1);
INSERT IGNORE INTO user_group_permission (group_id, controller, action) VALUES (1, '*', '*');
