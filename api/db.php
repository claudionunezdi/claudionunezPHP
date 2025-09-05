<?php
// api/db.php
header('Content-Type: application/json; charset=utf-8');
/*

Configuracion de la base de datos usando postgresql

*/

$DB_DRIVER = 'pgsql';
$DB_HOST   = '127.0.0.1';
$DB_PORT   = '5432';
$DB_NAME   = 'productos_db';
$DB_USER   = 'postgres';
$DB_PASS   = 'pgadmin';

try {
    $dsn = "pgsql:host={$DB_HOST};port={$DB_PORT};dbname={$DB_NAME};options='--client_encoding=UTF8'";
    $pdo = new PDO($dsn, $DB_USER, $DB_PASS, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Error de conexión a base de datos']);
    exit;
}
