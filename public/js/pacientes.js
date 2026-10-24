/**
 * Formulario de pacientes (capa Vista):
 * 1. Habilita la observación al marcar una alergia o enfermedad.
 * 2. Agrega y quita contactos de emergencia (máximo 3).
 * 3. Muestra los campos de la cuenta solo si se marca "Crear cuenta".
 */
document.addEventListener('DOMContentLoaded', function () {

    // ---- 1. Alergias y enfermedades ----
    document.querySelectorAll('.js-habilita').forEach(function (check) {
        const campos = document.querySelectorAll('[data-depende="' + check.id + '"]');
        check.addEventListener('change', function () {
            campos.forEach(function (campo) { campo.disabled = !check.checked; });
        });
    });

    // ---- 2. Contactos de emergencia ----
    const lista      = document.getElementById('lista-contactos');
    const plantilla  = document.getElementById('plantilla-contacto');
    const btnAgregar = document.getElementById('agregar-contacto');
    const MAXIMO     = 3;

    if (lista && plantilla && btnAgregar) {
        let indice = Number(lista.dataset.siguiente) || 0;

        const actualizarBoton = function () {
            btnAgregar.disabled = lista.querySelectorAll('.fila-contacto').length >= MAXIMO;
        };

        btnAgregar.addEventListener('click', function () {
            lista.insertAdjacentHTML('beforeend', plantilla.innerHTML.replaceAll('__i__', indice++));
            actualizarBoton();
        });

        lista.addEventListener('click', function (e) {
            const boton = e.target.closest('.quitar-contacto');
            if (boton) {
                boton.closest('.fila-contacto').remove();
                actualizarBoton();
            }
        });

        actualizarBoton();
    }

    // ---- 3. Cuenta de acceso ----
    const crearCuenta = document.getElementById('crear_cuenta');

    if (crearCuenta) {
        const seccion = document.getElementById('seccion-cuenta');
        const campos  = document.querySelectorAll('.campo-cuenta');
        const correo  = document.getElementById('correo');
        const usuario = document.getElementById('name');
        const ci      = document.getElementById('ci');

        const actualizar = function () {
            const activa = crearCuenta.checked;
            seccion.classList.toggle('hidden', !activa);
            campos.forEach(function (c) { c.disabled = !activa; });

            // Con cuenta, el correo pasa a ser obligatorio
            correo.dataset.reglas = activa ? 'requerido|email|max:100' : 'email|max:100';

            // Sugerir el CI como nombre de usuario
            if (activa && usuario.value === '' && ci.value !== '') {
                usuario.value = ci.value.trim().toLowerCase();
            }
        };

        crearCuenta.addEventListener('change', actualizar);
        actualizar();
    }
});