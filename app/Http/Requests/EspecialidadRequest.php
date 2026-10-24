<?php

namespace App\Http\Requests;

use App\Models\Especialidad;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Validación en el servidor de especialidades (RF04).
 */
class EspecialidadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;   // ESPECIALIDAD_GESTIONAR lo verifica el middleware
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'nombre'      => Especialidad::formatear((string) $this->input('nombre')),
            'descripcion' => trim((string) $this->input('descripcion')) ?: null,
        ]);
    }

    public function rules(): array
    {
        return [
            'nombre' => [
                'required', 'string', 'min:3', 'max:60',
                'regex:/^[\pL\s.()-]+$/u',
                // Nombre único normalizado: sin importar mayúsculas, tildes ni espacios
                function ($attribute, $value, $fail) {
                    $actual = $this->route('especialidad');

                    $duplicada = Especialidad::query()
                        ->when($actual, fn ($q) => $q->where('id', '!=', $actual->id))
                        ->pluck('nombre')
                        ->contains(fn ($nombre) => Especialidad::normalizar($nombre) === Especialidad::normalizar($value));

                    if ($duplicada) {
                        $fail('Ya existe una especialidad con ese nombre.');
                    }
                },
            ],
            'descripcion' => ['nullable', 'string', 'max:200'],
        ];
    }

    public function messages(): array
    {
        return [
            'nombre.regex' => 'El nombre solo puede contener letras, espacios, puntos, guiones y paréntesis.',
        ];
    }
}