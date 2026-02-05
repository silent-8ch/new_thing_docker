<?php
function get_db_pdo() {
    $host = getenv('MYSQL_HOST') ?: 'db';
    $dbname = getenv('MYSQL_DATABASE') ?: 'app';
    $user = getenv('MYSQL_USER') ?: 'app';
    $pass = getenv('MYSQL_PASSWORD') ?: 'password';
    $dsn = "mysql:host={$host};dbname={$dbname};charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ];
    return new PDO($dsn, $user, $pass, $options);
}