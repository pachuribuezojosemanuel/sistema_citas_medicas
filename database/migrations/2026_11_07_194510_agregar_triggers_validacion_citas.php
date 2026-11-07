<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * T4: valida agenda, superposición y paciente (RF07, RN04–RN09).
     * T5: impide salir de un estado final (RF10).
     */
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
CREATE OR REPLACE FUNCTION fn_validar_cita()
RETURNS TRIGGER AS $$
DECLARE
    id_cancelada  BIGINT;
    estado_nuevo  TEXT;
    horario       RECORD;
    cambia_agenda BOOLEAN;
BEGIN
    SELECT id INTO id_cancelada FROM estado_citas WHERE nombre = 'Cancelada';
    SELECT nombre INTO estado_nuevo FROM estado_citas WHERE id = NEW.estado_cita_id;

    cambia_agenda := TG_OP = 'INSERT'
        OR NEW.fecha IS DISTINCT FROM OLD.fecha
        OR NEW.hora_inicio IS DISTINCT FROM OLD.hora_inicio
        OR NEW.medico_id IS DISTINCT FROM OLD.medico_id;

    IF NEW.estado_cita_id = id_cancelada OR NOT cambia_agenda THEN
        RETURN NEW;
    END IF;

    IF TG_OP = 'INSERT' THEN
        IF NOT EXISTS (SELECT 1 FROM pacientes WHERE id = NEW.paciente_id AND estado) THEN
            RAISE EXCEPTION 'El paciente está inactivo y no puede recibir nuevas citas.';
        END IF;
        IF NOT EXISTS (SELECT 1 FROM especialidades WHERE id = NEW.especialidad_id AND estado) THEN
            RAISE EXCEPTION 'La especialidad está inactiva.';
        END IF;
    END IF;

    IF NOT EXISTS (SELECT 1 FROM medicos WHERE id = NEW.medico_id AND estado) THEN
        RAISE EXCEPTION 'El médico está inactivo y no puede recibir nuevas citas (RN04).';
    END IF;
    IF NOT EXISTS (SELECT 1 FROM medico_especialidad
                    WHERE medico_id = NEW.medico_id AND especialidad_id = NEW.especialidad_id) THEN
        RAISE EXCEPTION 'El médico no ejerce la especialidad seleccionada.';
    END IF;

    IF estado_nuevo IN ('Programada', 'Confirmada') THEN
        IF NEW.fecha < CURRENT_DATE THEN
            RAISE EXCEPTION 'No se pueden programar citas en fechas pasadas (RN06).';
        END IF;

        SELECT h.hora_inicio, h.hora_fin, h.duracion_cita INTO horario
          FROM horario_atencions h
         WHERE h.medico_id = NEW.medico_id AND h.estado
           AND h.dia_semana_id = EXTRACT(ISODOW FROM NEW.fecha)
           AND NEW.hora_inicio >= h.hora_inicio AND NEW.hora_inicio < h.hora_fin
         LIMIT 1;
        IF NOT FOUND THEN
            RAISE EXCEPTION 'La hora % del % está fuera del horario de atención del médico (RN05).',
                to_char(NEW.hora_inicio, 'HH24:MI'), to_char(NEW.fecha, 'DD/MM/YYYY');
        END IF;

        IF (EXTRACT(EPOCH FROM (NEW.hora_inicio - horario.hora_inicio))::INT / 60) % horario.duracion_cita <> 0
           OR NEW.hora_fin <> NEW.hora_inicio + make_interval(mins => horario.duracion_cita)
           OR NEW.hora_fin > horario.hora_fin THEN
            RAISE EXCEPTION 'La cita debe respetar los bloques de % minutos del horario (RN08).', horario.duracion_cita;
        END IF;
    END IF;

    IF EXISTS (SELECT 1 FROM citas c
                WHERE c.medico_id = NEW.medico_id AND c.fecha = NEW.fecha
                  AND c.id <> COALESCE(NEW.id, 0) AND c.estado_cita_id <> id_cancelada
                  AND c.hora_inicio < NEW.hora_fin AND c.hora_fin > NEW.hora_inicio) THEN
        RAISE EXCEPTION 'El médico ya tiene una cita en ese horario (RN07).';
    END IF;

    IF EXISTS (SELECT 1 FROM citas c
                WHERE c.paciente_id = NEW.paciente_id AND c.fecha = NEW.fecha
                  AND c.id <> COALESCE(NEW.id, 0) AND c.estado_cita_id <> id_cancelada
                  AND c.hora_inicio < NEW.hora_fin AND c.hora_fin > NEW.hora_inicio) THEN
        RAISE EXCEPTION 'El paciente ya tiene otra cita en ese horario.';
    END IF;

    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

DROP TRIGGER IF EXISTS trg_citas_validar ON citas;
CREATE TRIGGER trg_citas_validar
    BEFORE INSERT OR UPDATE ON citas
    FOR EACH ROW EXECUTE FUNCTION fn_validar_cita();

CREATE OR REPLACE FUNCTION fn_validar_cambio_estado()
RETURNS TRIGGER AS $$
DECLARE
    anterior TEXT;
    nuevo    TEXT;
BEGIN
    SELECT nombre INTO anterior FROM estado_citas WHERE id = OLD.estado_cita_id;
    SELECT nombre INTO nuevo    FROM estado_citas WHERE id = NEW.estado_cita_id;

    IF anterior IN ('Atendida', 'Cancelada', 'Inasistencia') THEN
        RAISE EXCEPTION 'La cita está en estado % y ya no puede cambiar.', anterior;
    END IF;
    IF nuevo = 'Programada' AND anterior = 'Confirmada' THEN
        RAISE EXCEPTION 'Una cita confirmada no puede volver a programada.';
    END IF;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

DROP TRIGGER IF EXISTS trg_citas_cambio_estado ON citas;
CREATE TRIGGER trg_citas_cambio_estado
    BEFORE UPDATE OF estado_cita_id ON citas
    FOR EACH ROW WHEN (OLD.estado_cita_id IS DISTINCT FROM NEW.estado_cita_id)
    EXECUTE FUNCTION fn_validar_cambio_estado();
SQL);
    }

    public function down(): void
    {
        DB::unprepared(<<<'SQL'
DROP TRIGGER IF EXISTS trg_citas_cambio_estado ON citas;
DROP TRIGGER IF EXISTS trg_citas_validar ON citas;
DROP FUNCTION IF EXISTS fn_validar_cambio_estado();
DROP FUNCTION IF EXISTS fn_validar_cita();
SQL);
    }
};