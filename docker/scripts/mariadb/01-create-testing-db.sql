CREATE DATABASE IF NOT EXISTS backend_testing CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
GRANT ALL PRIVILEGES ON backend_testing.* TO 'laravel'@'%';
FLUSH PRIVILEGES;