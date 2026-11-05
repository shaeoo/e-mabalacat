<?php
// src/config/database.php

$host = '127.0.0.1'; // Baguhin sa TiDB cloud host kapag i-de-deploy na
$db   = 'e_mabalacat';
$user = 'root';
$pass = '';
$port = '3306';      // TiDB port ay kadalasang 4000

$dsn = "mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4";

try {
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
} catch (\PDOException $e) {
    die(json_encode([
        "status" => "error",
        "message" => "Database Connection Failed: " . $e->getMessage()
    ]));
}
?>
