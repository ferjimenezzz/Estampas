-- Ejecuta esto en tu phpMyAdmin local.
-- Primero, crea una base de datos llamada 'estampas' y selecciónala, luego corre este script.

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS user_stamps (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    stamp_code VARCHAR(10) NOT NULL,
    quantity INT DEFAULT 1,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY user_stamp_unique (user_id, stamp_code),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT IGNORE INTO users (username, password) 
VALUES ('Ferad', 'Faja052603050406');