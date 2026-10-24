// Horarios de atención (RF05): resumen en vivo y validación de horas
document.addEventListener('DOMContentLoaded', function () {
    const inicio   = document.getElementById('hora_inicio');
    const fin      = document.getElementById('hora_fin');
    const duracion = document.getElementById('duracion_cita');
    const resumen  = document.getElementById('resumen-horario');
    const form     = inicio ? inicio.closest('form') : null;
    if (!form) return;

    function minutos(valor) {
        const [h, m] = valor.split(':').map(Number);
        return h * 60 + m;
    }

    function calcular() {
        resumen.className = 'text-sm';
        if (!inicio.value || !fin.value) { resumen.textContent = ''; return null; }

        const total = minutos(fin.value) - minutos(inicio.value);
        const dur   = Number(duracion.value);

        if (total <= 0) {
            resumen.textContent = 'La hora final debe ser posterior a la hora inicial.';
            resumen.classList.add('text-red-600');
            return false;
        }
        if (total % dur !== 0) {
            resumen.textContent = `El rango (${total} min) no es múltiplo de ${dur} min.`;
            resumen.classList.add('text-red-600');
            return false;
        }
        resumen.textContent = `Se generarán ${total / dur} citas de ${dur} minutos.`;
        resumen.classList.add('text-green-700');
        return true;
    }

    [inicio, fin, duracion].forEach(el => el.addEventListener('change', calcular));
    calcular();

    form.addEventListener('submit', function (e) {
        if (calcular() === false) e.preventDefault();
    });
});