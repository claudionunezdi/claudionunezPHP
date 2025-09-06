

/////////Requisitos://////////

PHP 8.2.12 (CLI)
PostgresSQL 17
Extensión de php nativo -> PDO_PGSQL 


////////Datos de usuario base de datos/////////

$DB_HOST = '127.0.0.1';
$DB_PORT = '5432';
$DB_NAME = 'productos_db';
$DB_USER = 'postgres';
$DB_PASS = 'pgadmin';
-----para cargar la base de datos------
en cmd
///Crear la base de datos desde CMD/////////

psql -U postgres -c "CREATE DATABASE productos_db ENCODING 'UTF8' TEMPLATE template0;"
Contraseña: pgadmin

>Cargar archivos SQL en carpetas sql
cd sql
>luego aplicar los archivos sql
psql -U postgres -d productos_db -f sql/01_schema.sql
>Ingresar contraseña<
psql -U postgres -d productos_db -f sql/02_seed.sql
>Ingresar Contraseña<

>Para cargar la aplicacion Desde la raiz del proyecto:
php -S localhost:8000 router.php -t .


>Una vez cargada y desea eliminar La base de datos

psql -U postgres -c "DROP DATABASE productos_db;"

/////////ENDPOINTS///////////

GET /api/bodegas

GET /api/sucursales?bodega_id=ID

GET /api/monedas

GET /api/materiales

GET /api/check_code?codigo=XYZ

POST /api/save_product

