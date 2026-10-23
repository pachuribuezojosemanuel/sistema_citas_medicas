/**
 * Validación de formularios en el cliente (capa Vista).
 * La validación definitiva siempre se hace en el servidor (Form Requests).
 *
 * Uso:
 *   <form data-validar novalidate> ... <input data-reglas="requerido|email|max:100"> ...
 *   <form data-confirmar="¿Está seguro?">  → pide confirmación antes de enviar
 *
 * Reglas: requerido, email, min:n, max:n, coincide:campo, usuario, clave, numero, telefono, ci
 */
(function () {
    const reglas = {
        requerido: (v) => v.trim() !== '',
        email:     (v) => /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v),
        min:       (v, n) => v.trim().length >= Number(n),
        max:       (v, n) => v.trim().length <= Number(n),
        coincide:  (v, campo, form) => v === (form.elements[campo] ? form.elements[campo].value : ''),
        usuario:   (v) => /^[A-Za-z0-9._-]+$/.test(v),
        clave:     (v) => /[A-Za-z]/.test(v) && /\d/.test(v),
        numero:    (v) => /^\d+$/.test(v),
        telefono:  (v) => /^[0-9+\s-]{7,20}$/.test(v),
        ci:        (v) => /^[0-9A-Za-z-]{5,15}$/.test(v),
    };

    const mensajes = {
        requerido: () => 'Este campo es obligatorio.',
        email:     () => 'Ingrese un correo electrónico válido.',
        min:       (n) => `Debe tener al menos ${n} caracteres.`,
        max:       (n) => `No debe superar los ${n} caracteres.`,
        coincide:  () => 'Los valores no coinciden.',
        usuario:   () => 'Solo letras, números, punto, guion y guion bajo (sin espacios).',
        clave:     () => 'Debe contener letras y números.',
        numero:    () => 'Solo se permiten números.',
        telefono:  () => 'Ingrese un teléfono válido (7 a 20 dígitos).',
        ci:        () => 'Ingrese un CI válido.',
    };

    function validarCampo(input, form) {
        const valor = input.value || '';
        const lista = input.dataset.reglas.split('|');

        // Un campo opcional vacío no se valida (salvo "coincide")
        if (!lista.includes('requerido') && !lista.some(r => r.startsWith('coincide')) && valor.trim() === '') {
            return null;
        }

        for (const regla of lista) {
            const [nombre, parametro] = regla.split(':');
            if (reglas[nombre] && !reglas[nombre](valor, parametro, form)) {
                return mensajes[nombre](parametro);
            }
        }
        return null;
    }

    function mostrarError(input, mensaje) {
        let error = input.parentElement.querySelector('.error-js');
        if (!error) {
            error = document.createElement('p');
            error.className = 'error-js text-sm text-red-600 mt-2';
            input.insertAdjacentElement('afterend', error);
        }
        error.textContent = mensaje || '';
        error.style.display = mensaje ? 'block' : 'none';
        input.style.borderColor = mensaje ? '#dc2626' : '';
    }

    // Validación al enviar
    document.querySelectorAll('form[data-validar]').forEach(function (form) {
        form.addEventListener('submit', function (e) {
            let primerError = null;

            form.querySelectorAll('[data-reglas]').forEach(function (input) {
                const mensaje = validarCampo(input, form);
                mostrarError(input, mensaje);
                if (mensaje && !primerError) primerError = input;
            });

            if (primerError) {
                e.preventDefault();
                primerError.focus();
            }
        });

        // Quitar el error mientras el usuario corrige
        form.querySelectorAll('[data-reglas]').forEach(function (input) {
            input.addEventListener('input', function () {
                if (input.style.borderColor) mostrarError(input, validarCampo(input, form));
            });
        });
    });

    // Confirmación antes de acciones (activar / desactivar)
    document.querySelectorAll('form[data-confirmar]').forEach(function (form) {
        form.addEventListener('submit', function (e) {
            if (!confirm(form.dataset.confirmar)) e.preventDefault();
        });
    });
})();