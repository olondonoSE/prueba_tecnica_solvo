-- Forzar creación de usuario root con acceso desde cualquier host
CREATE USER IF NOT EXISTS 'root'@'%' IDENTIFIED BY 'rootpassword';
GRANT ALL PRIVILEGES ON *.* TO 'root'@'%' WITH GRANT OPTION;
GRANT ALL PRIVILEGES ON *.* TO 'root'@'localhost' WITH GRANT OPTION;
FLUSH PRIVILEGES;

-- Crear usuario de aplicación
CREATE USER IF NOT EXISTS 'symfony'@'%' IDENTIFIED BY 'password';
GRANT ALL PRIVILEGES ON symfony_db.* TO 'symfony'@'%';
FLUSH PRIVILEGES;
