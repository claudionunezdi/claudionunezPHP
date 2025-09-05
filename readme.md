

Requisitos:

PHP 8.2.12 (CLI)
PostgresSQL 17
Extensión PDO_PGSQL 


Datos de usuario base de detaos

$DB_HOST = '127.0.0.1';
$DB_PORT = '5432';
$DB_NAME = 'productos_db';
$DB_USER = 'postgres';
$DB_PASS = 'pgadmin';

Desde la raiz del proyecto:

php -S localhost:8000 router.php


ENDPOINTS

GET /api/bodegas

GET /api/sucursales?bodega_id=ID

GET /api/monedas

GET /api/materiales

GET /api/check_code?codigo=XYZ

POST /api/save_product

