CREATE TABLE serial_codes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    serial CHAR(16) UNIQUE NOT NULL,
    user_email VARCHAR(255) querovideo1@gmail.com,
    product ENUM('mensal', 'anual') anual
);
