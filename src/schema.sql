-- Create users table
CREATE TABLE users (
                       user_id INT UNSIGNED NOT NULL AUTO_INCREMENT,
                       username VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                       password VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                       email VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                       full_name VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                       group_id INT NOT NULL DEFAULT 0,
                       trust_status INT NOT NULL DEFAULT 0,
                       reg_status INT NOT NULL DEFAULT 0,
                       PRIMARY KEY (user_id)
);
