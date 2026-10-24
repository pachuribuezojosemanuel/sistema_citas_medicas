// Médicos: al menos una especialidad seleccionada (RF03)
document.addEventListener('DOMContentLoaded', function () {
    const form = document.querySelector('form[data-validar]');
    if (!form) return;

    form.addEventListener('submit', function (e) {
        const marcadas = form.querySelectorAll('input[name="especialidades[]"]:checked').length;
        const error = document.getElementById('error-especialidades');

        if (marcadas === 0) {
            error.classList.remove('hidden');
            e.preventDefault();
        } else {
            error.classList.add('hidden');
        }
    });
});