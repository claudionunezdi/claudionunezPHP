<?php
/*
Validadores para los datos recibidos en la API
*/

// api/validators.php

//Funcion para validar el UTF-8
function str_len($cadena)
{
    return mb_strlen(trim((string)$cadena), 'UTF-8');
}

//Validaciones
//Espera que el codigo debe ser obligatorio, ademas de no exceder los 15 caracteres y solo letras, numeros y guiones

function validate_codigo($codigo)
{
    if (str_len($codigo) === 0) {
        return 'El código es obligatorio.';
    }
    if (str_len($codigo) > 15) {
        return 'El código no debe exceder los 15 caracteres.';
    }
    if (!preg_match('/^[a-zA-Z0-9\-]+$/u', $codigo)) {
        return 'El código solo puede contener letras, números y guiones.';
    }
    return null;
}

//Valida que el nombre no este vacio y tenga entre 2 y 50 caracteres
//Ademas de validar que el nombre sea una cadena UTF-8
function validate_nombre($nombre)
{
    if ($nombre === null || trim($nombre) === '') return "El nombre del producto no puede estar en blanco.";
    $len = str_len($nombre);

    if ($len < 2 || $len > 50) return "El nombre del producto debe tener entre 2 y 50 caracteres.";
    return null;
}

function validate_ids_obligatorios($bodega_id, $sucursal_id, $moneda_id)
{
    if (!$bodega_id) return "Debe seleccionar una bodega.";
    if (!$sucursal_id) return "Debe seleccionar una sucursal para la bodega seleccionada.";
    if (!$moneda_id) return "Debe seleccionar una moneda para el producto.";
    return null;
}

function validate_precio($precio)
{
    if ($precio === null || trim($precio) === '') return "El precio del producto no puede estar en blanco.";
    if (!preg_match('/^(?:[1-9]\d*)(?:\.\d{1,2})?$/', $precio))
        return "El precio del producto debe ser un número positivo con hasta dos decimales.";
    return null;
}

function validate_materiales($materiales)
{
    if (!is_array($materiales) || count($materiales) < 2)
        return "Debe seleccionar al menos dos materiales para el producto.";
    return null;
}

function validate_descripcion($descripcion)
{
    if ($descripcion === null || trim($descripcion) === '') return "La descripción del producto no puede estar en blanco.";
    $len = str_len($descripcion);
    if ($len < 10 || $len > 1000) return "La descripción del producto debe tener entre 10 y 1000 caracteres.";
    return null;
}
