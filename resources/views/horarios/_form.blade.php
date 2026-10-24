<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
    <div class="sm:col-span-2">
        <x-input-label for="medico_id" value="Médico *" />
        <x-select-input id="medico_id" name="medico_id" class="block mt-1 w-full" data-reglas="requerido">
            <option value="">— Seleccione un médico —</option>
            @foreach ($medicos as $m)
                <option value="{{ $m->id }}" @selected(old('medico_id', $horario->medico_id) == $m->id)>
                    {{ $m->apellidos }}, {{ $m->nombres }} ({{ $m->codigo }})
                </option>
            @endforeach
        </x-select-input>
        <x-input-error :messages="$errors->get('medico_id')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="dia_semana_id" value="Día *" />
        <x-select-input id="dia_semana_id" name="dia_semana_id" class="block mt-1 w-full" data-reglas="requerido">
            <option value="">— Seleccione —</option>
            @foreach ($dias as $d)
                <option value="{{ $d->id }}" @selected(old('dia_semana_id', $horario->dia_semana_id) == $d->id)>{{ $d->nombre }}</option>
            @endforeach
        </x-select-input>
        <x-input-error :messages="$errors->get('dia_semana_id')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="consultorio_id" value="Consultorio *" />
        <x-select-input id="consultorio_id" name="consultorio_id" class="block mt-1 w-full" data-reglas="requerido">
            <option value="">— Seleccione —</option>
            @foreach ($consultorios as $c)
                <option value="{{ $c->id }}" @selected(old('consultorio_id', $horario->consultorio_id) == $c->id)>
                    {{ $c->numero }} — {{ $c->nombre }}
                </option>
            @endforeach
        </x-select-input>
        <x-input-error :messages="$errors->get('consultorio_id')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="hora_inicio" value="Hora inicial *" />
        <x-text-input id="hora_inicio" name="hora_inicio" type="time" step="900" class="block mt-1 w-full"
                      :value="old('hora_inicio', $horario->hora_inicio ? substr($horario->hora_inicio, 0, 5) : '')"
                      data-reglas="requerido" />
        <x-input-error :messages="$errors->get('hora_inicio')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="hora_fin" value="Hora final *" />
        <x-text-input id="hora_fin" name="hora_fin" type="time" step="900" class="block mt-1 w-full"
                      :value="old('hora_fin', $horario->hora_fin ? substr($horario->hora_fin, 0, 5) : '')"
                      data-reglas="requerido" />
        <x-input-error :messages="$errors->get('hora_fin')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="duracion_cita" value="Duración de cada cita *" />
        <x-select-input id="duracion_cita" name="duracion_cita" class="block mt-1 w-full" data-reglas="requerido">
            @foreach ($duraciones as $min)
                <option value="{{ $min }}" @selected(old('duracion_cita', $horario->duracion_cita ?? 30) == $min)>{{ $min }} minutos</option>
            @endforeach
        </x-select-input>
        <x-input-error :messages="$errors->get('duracion_cita')" class="mt-2" />
    </div>

    <div class="flex items-end">
        <p id="resumen-horario" class="text-sm text-gray-600"></p>
    </div>
</div>

<p class="text-xs text-gray-500 mt-4">
    * Campos obligatorios. El médico y el consultorio no pueden tener horarios superpuestos el mismo día.
</p>

<script src="{{ asset('js/horarios.js') }}"></script>