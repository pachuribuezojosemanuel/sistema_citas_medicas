// Validación del formulario de login en el cliente (Vista).
// La validación definitiva se hace en el servidor (LoginRequest).
document.getElementById('form-login').addEventListener('submit', function (e) {
    const campos = [
        { id: 'login',    mensaje: 'Ingrese su nombre de usuario o correo.' },
        { id: 'password', mensaje: 'Ingrese su contraseña.' },
    ];
    let valido = true;

    campos.forEach(function (campo) {
        const input = document.getElementById(campo.id);
        const error = document.getElementById('error-' + campo.id);

        if (input.value.trim() === '') {
            error.textContent = campo.mensaje;
            error.classList.remove('hidden');
            valido = false;
        } else {
            error.classList.add('hidden');
        }
    });

    if (!valido) e.preventDefault();
});