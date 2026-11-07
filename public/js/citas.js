/**
 * Formulario de citas (capa Vista) — RF06 / RF08
 * 1. Filtra la lista de pacientes mientras se escribe.
 * 2. Al elegir especialidad, carga sus médicos activos.
 * 3. Al elegir médico y fecha, muestra SOLO las horas disponibles.
 * 4. No deja enviar sin una hora seleccionada.
 * La verificación definitiva se hace en el servidor al guardar.
 */
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('form-cita');
    if (!form) return;

    const urlMedicos      = form.dataset.urlMedicos;
    const urlDisponible   = form.dataset.urlDisponibilidad;
    const ignorar         = form.dataset.ignorar || '';
    const especialidad    = document.getElementById('especialidad_id');   // no existe al reprogramar
    const medico          = document.getElementById('medico_id');
    const fecha           = document.getElementById('fecha');
    const horas           = document.getElementById('contenedor-horas');
    const errorHora       = document.getElementById('error-hora');

    // ---- Utilidades ----
    async function pedir(url, parametros) {
        const respuesta = await fetch(url + '?' + new URLSearchParams(parametros), {
            headers: { 'Accept': 'application/json' },
        });
        if (!respuesta.ok) throw new Error('HTTP ' + respuesta.status);
        return respuesta.json();
    }

    function mensajeHoras(texto) {
        horas.innerHTML = '';
        const p = document.createElement('p');
        p.className = 'hora-mensaje';
        p.textContent = texto;
        horas.appendChild(p);
    }

    // ---- 1. Filtro de pacientes ----
    const filtro   = document.getElementById('filtro-paciente');
    const paciente = document.getElementById('paciente_id');
    if (filtro && paciente) {
        filtro.addEventListener('input', function () {
            const texto = filtro.value.trim().toLowerCase();
            Array.from(paciente.options).forEach(function (op, i) {
                if (i === 0) return;                       // "— Seleccione —"
                op.hidden = texto !== '' && !op.text.toLowerCase().includes(texto);
            });
        });
    }

    // ---- 2. Médicos de la especialidad ----
    async function cargarMedicos() {
        const seleccionado = medico.dataset.seleccionado || '';
        medico.innerHTML = '<option value="">— Seleccione un médico —</option>';
        mensajeHoras('Seleccione médico y fecha para ver los horarios disponibles.');
        if (!especialidad.value) return;

        try {
            const lista = await pedir(urlMedicos, { especialidad_id: especialidad.value });
            if (lista.length === 0) {
                medico.innerHTML = '<option value="">No hay médicos activos para esta especialidad</option>';
                return;
            }
            lista.forEach(function (m) {
                const opcion = new Option(m.nombre, m.id);
                opcion.selected = String(m.id) === seleccionado;
                medico.add(opcion);
            });
            medico.dataset.seleccionado = '';
            cargarHoras();
        } catch (e) {
            mensajeHoras('No se pudieron cargar los médicos. Intente nuevamente.');
        }
    }

    // ---- 3. Horas disponibles ----
    async function cargarHoras() {
        const seleccionada = horas.dataset.seleccionada || '';
        if (!medico.value || !fecha.value) {
            mensajeHoras('Seleccione médico y fecha para ver los horarios disponibles.');
            return;
        }

        mensajeHoras('Buscando horarios disponibles…');
        try {
            const datos = await pedir(urlDisponible, { medico_id: medico.value, fecha: fecha.value, ignorar: ignorar });
            if (datos.bloques.length === 0) {
                mensajeHoras('No hay horarios disponibles para el ' + datos.fecha + '.');
                return;
            }

            horas.innerHTML = '';
            datos.bloques.forEach(function (b) {
                const etiqueta = document.createElement('label');
                etiqueta.className = 'hora-opcion';
                etiqueta.title = 'Consultorio ' + b.consultorio;

                const radio = document.createElement('input');
                radio.type = 'radio';
                radio.name = 'hora_inicio';
                radio.value = b.inicio;
                radio.checked = b.inicio === seleccionada;

                etiqueta.appendChild(radio);
                etiqueta.appendChild(document.createTextNode(b.inicio + '–' + b.fin));
                horas.appendChild(etiqueta);
            });
            horas.dataset.seleccionada = '';
        } catch (e) {
            mensajeHoras('No se pudo consultar la disponibilidad. Intente nuevamente.');
        }
    }

    // ---- Eventos ----
    if (especialidad) especialidad.addEventListener('change', cargarMedicos);
    medico.addEventListener('change', cargarHoras);
    fecha.addEventListener('change', cargarHoras);

    // ---- 4. Validar que se haya elegido una hora ----
    form.addEventListener('submit', function (e) {
        const elegida = form.querySelector('input[name="hora_inicio"]:checked');
        errorHora.classList.toggle('hidden', !!elegida);
        if (!elegida) e.preventDefault();
    });

    // Al volver con errores (old) o al reprogramar, cargar lo que ya estaba elegido
    if (especialidad && especialidad.value) {
        cargarMedicos();
    } else if (!especialidad) {
        cargarHoras();
    }
});