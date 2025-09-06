-- Usar: psql -U postgres -d productos_db -f sql/02_seed.sql
BEGIN;


-- UTF8 encoding

SET client_encoding = 'UTF8';
-- Bodegas
INSERT INTO bodegas (nombre) VALUES
('Bodega 1') ,
('Bodega 2') ,
('Bodega 3') 
ON CONFLICT DO NOTHING;

-- Sucursales (coherentes con las 3 bodegas)
INSERT INTO sucursales (nombre, bodega_id) VALUES
('Sucursal 1', 1),
('Sucursal 2', 1),
('Sucursal 3', 2),
('Sucursal 4', 2),
('Sucursal 5', 3),
('Sucursal 6', 3)
ON CONFLICT DO NOTHING;

-- Monedas Codigo en ISO
INSERT INTO monedas (codigo, nombre) VALUES
('USD', 'Dólar estadounidense'),
('EUR', 'Euro'),
('CLP', 'Peso chileno'),
('PEN', 'Sol peruano'),
('ARS', 'Peso argentino')
ON CONFLICT DO NOTHING;

-- Materiales
INSERT INTO materiales (nombre) VALUES
('Plástico'),
('Metal'),
('Madera'),
('Vidrio'),
('Textil')
ON CONFLICT DO NOTHING;

COMMIT;
