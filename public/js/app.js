///Config API

const API = {
    bodegas: '/api/bodegas',
    sucursales: (bodega_id) => `/api/sucursales?bodega_id=${encodeURIComponent(bodega_id)}`,
    monedas: '/api/monedas',
    materiales: '/api/materiales',
    checkCode: (codigo) => `/api/check_code?codigo=${encodeURIComponent(codigo)}`,
    save: '/api/save_product'
};

//REGEX VALIDATORS

const REGEX = {
    codigo: /^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d]{5,15}$/,
    precio: /^(?:[1-9]\d*)(?:\.\d{1,2})?$/
}

//Helpers
const $ = (selector) => document.querySelector(selector);
const materialesSeleccionados = () =>
    Array.from(document.querySelectorAll('input[name="materiales"]:checked'))
        .map(cb => parseInt(cb.value, 10));


//AJAX
//Cargar Bodegas.
async function loadBodegas() {
    const res = await fetch(API.bodegas);
    const data = await res.json();
    const select = $('#bodega');
    select.innerHTML = '<option value=""></option>';
    data.forEach(bodega => select.insertAdjacentHTML('beforeend', `<option value="${bodega.id}">${bodega.nombre}</option>`));
}

//Cargar sucursales al cambiar la bodega
async function loadSucursales(bodega_id) {
    const select = $('#sucursal');
    select.innerHTML = '<option value=""></option>';
    if (!bodega_id) return;

    const res = await fetch(API.sucursales(bodega_id));
    if (!res.ok) {
        console.error('HTTP error sucursales:', res.status, await res.text());
        alert('No se pudieron cargar las sucursales (HTTP ' + res.status + ').');
        return;
    }

    const data = await res.json();
    if (!Array.isArray(data)) {
        console.error('Respuesta sucursales no es array:', data);
        alert((data && data.error) ? data.error : 'No se pudieron cargar las sucursales.');
        return;
    }

    data.forEach(s => select.insertAdjacentHTML('beforeend', `<option value="${s.id}">${s.nombre}</option>`));
    }

//Cargar monedas
async function loadMonedas() {
    const res = await fetch(API.monedas);
    const data = await res.json();
    const select = $('#moneda');
    select.innerHTML = '<option value=""></option>';
    data.forEach(m => select.insertAdjacentHTML('beforeend', `<option value="${m.id}">${m.nombre}</option>`));
}
//Cargar materiales
async function loadMateriales() {
    const res = await fetch(API.materiales);
    const data = await res.json();
    const cont = $('#materialesContainer');
    cont.innerHTML = '';
    data.forEach(mat => {
    const id = `material-${mat.id}`;
    cont.insertAdjacentHTML(
        'beforeend',
        `<label for="${id}">
            <input type="checkbox" id="${id}" value="${mat.id}" name="materiales"> ${mat.nombre}
        </label>`
    );
    });
}

//Validaciones
//Validar código (formato y unicidad)
async function validateCodigo(v) {
    if (!v) return alert('El código del producto no puede estar vacío.');
    if (v.length < 5 || v.length > 15) return alert('El código del producto debe tener entre 5 y 15 caracteres.');
    if (!REGEX.codigo.test(v))
        return alert('El código del producto debe contener letras y números, sin espacios ni caracteres especiales.');

    //Verificar unicidad
    const res = await fetch(API.checkCode(v));
    const data = await res.json();
    if (data.exists) return alert('El código del producto ya esta registrado.'), false;
    return true;

}


//Validar Nombre (no vacío) 2 a 50 caracteres
function validateNombre(v) {
    if (!v) return alert('El nombre del producto no puede estar en blanco.'), false;
    if (v.length < 2 || v.length > 50) return alert('El nombre del producto debe tener entre 2 y 50 caracteres.'), false;
    return true;
}


//Validar Select (no vacío)
function validateSelect(v, msg) { 
    if (!v) return alert(msg), false;
    return true;
}
//Validar precio (número positivo, hasta 2 decimales)
function validatePrecio(v) {
    if (!v) return alert('El precio del producto no puesde estar en blanco.')
    if (!REGEX.precio.test(v))
        return alert('El precio del producto debe ser un número positivo, con hasta dos decimales.'), false;
    return true;
}
//Validar materiales (al menos 2 seleccionados)
function validateMateriales() {
    const n = materialesSeleccionados().length;
    if (n < 2) return alert('Debe seleccionar al menos dos materiales para el producto.'), false;
    return true;
}

function validateDescripcion(v) {
    if (!v) return alert('La descripción del producto no puede estar en blanco.'), false;
    if (v.length < 10 || v.length > 1000) return alert('La descripción del producto debe tener entre 10 y 1000 caracteres.'), false;
    return true;
}


//Submit
async function onSubmit(e) {
    e.preventDefault();

    const codigo = $('#codigo').value.trim();
    const nombre = $('#nombre').value.trim();
    const bodega_id = $('#bodega').value;
    const sucursal_id = $('#sucursal').value;
    const moneda_id = $('#moneda').value;
    const precio = $('#precio').value.trim();
    const descripcion = $('#descripcion').value.trim();
    const materiales = materialesSeleccionados();


    if (!(await validateCodigo(codigo))) return;
    if (!validateNombre(nombre)) return;
    if (!validateSelect(bodega_id, 'Debe seleccionar una bodega.')) return;
    if (!validateSelect(sucursal_id, 'Debe seleccionar una sucursal.')) return;
    if (!validateSelect(moneda_id, 'Debe seleccionar una moneda.')) return;
    if (!validatePrecio(precio)) return;
    if (!validateMateriales()) return;
    if (!validateDescripcion(descripcion)) return;

    const payload = {
        codigo,
        nombre,
        bodega_id: parseInt(bodega_id, 10),
        sucursal_id: parseInt(sucursal_id, 10),
        moneda_id: parseInt(moneda_id, 10),
        precio, descripcion, materiales

        };

    const res = await fetch(API.save, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
});
const data = await res.json();

    if (data.ok) {
        alert(`Producto guardado con ID ${data.id}`);
        $('#productoForm').reset();

        $('#sucursal').innerHTML = '<option value=""></option>';


    } else {
        alert(data.error || 'Error al guardar el producto.');
    }
    
}

//Init

document.addEventListener('DOMContentLoaded', async () => {
    await Promise.all([loadBodegas(), loadMonedas(), loadMateriales()]);
    $('#bodega').addEventListener('change', (e) => loadSucursales(e.target.value));
    $('#productoForm').addEventListener('submit', onSubmit);
    //Validacion del codigo
    $('#codigo').addEventListener('blur', async () => {
        const v = $('#codigo').value.trim();
        if (v) await validateCodigo(v);
    });
});