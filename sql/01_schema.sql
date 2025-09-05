-- Usar: psql -U postgres -d productos_db -f sql/01_schema.sql
BEGIN;

-- UTF8

SET client_encoding = 'UTF8';


-- Bodegas
CREATE TABLE IF NOT EXISTS bodegas (
  id SERIAL PRIMARY KEY,
  nombre VARCHAR(80) NOT NULL
);

-- Sucursales (FK bodegas)
CREATE TABLE IF NOT EXISTS sucursales (
  id SERIAL PRIMARY KEY,
  nombre VARCHAR(80) NOT NULL,
  bodega_id INTEGER NOT NULL REFERENCES bodegas(id) ON DELETE RESTRICT
);
CREATE INDEX IF NOT EXISTS idx_sucursales_bodega ON sucursales(bodega_id);

-- Monedas
CREATE TABLE IF NOT EXISTS monedas (
  id SERIAL PRIMARY KEY,
  codigo VARCHAR(10) NOT NULL UNIQUE,
  nombre VARCHAR(50) NOT NULL
);

-- Materiales
CREATE TABLE IF NOT EXISTS materiales (
  id SERIAL PRIMARY KEY,
  nombre VARCHAR(50) NOT NULL UNIQUE
);

-- Productos 
CREATE TABLE IF NOT EXISTS productos (
  id SERIAL PRIMARY KEY,
  codigo VARCHAR(15) NOT NULL UNIQUE,      -- 5–15 (lo valida la app; aquí ponemos el límite)
  nombre VARCHAR(50) NOT NULL,             -- 2–50 (lo valida la app)
  bodega_id INTEGER NOT NULL REFERENCES bodegas(id) ON DELETE RESTRICT,
  sucursal_id INTEGER NOT NULL REFERENCES sucursales(id) ON DELETE RESTRICT,
  moneda_id INTEGER NOT NULL REFERENCES monedas(id) ON DELETE RESTRICT,
  precio NUMERIC(12,2) NOT NULL CHECK (precio > 0),  -- positivo
  descripcion TEXT NOT NULL,
  created_at TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

-- Índices por FK
CREATE INDEX IF NOT EXISTS idx_productos_bodega  ON productos(bodega_id);
CREATE INDEX IF NOT EXISTS idx_productos_sucursal ON productos(sucursal_id);
CREATE INDEX IF NOT EXISTS idx_productos_moneda  ON productos(moneda_id);

-- Relación N:N con materiales
CREATE TABLE IF NOT EXISTS producto_material (
  producto_id INTEGER NOT NULL REFERENCES productos(id) ON DELETE CASCADE,
  material_id INTEGER NOT NULL REFERENCES materiales(id) ON DELETE RESTRICT,
  PRIMARY KEY (producto_id, material_id)
);

COMMIT;
