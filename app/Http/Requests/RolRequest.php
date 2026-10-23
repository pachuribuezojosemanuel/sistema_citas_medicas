<?php

namespace App\Http\Requests;

use App\Models\Rol;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Validación en el servidor para crear y editar roles.
 */
class RolRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;   // ROL_GESTIONAR lo verifica el middleware
    }

    /**
     * Normaliza el nombre: sin espacios dobles ni al inicio/final.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'nombre'      => preg_replace('/\s+/', ' ', trim((string) $this->input('nombre'))),
            'descripcion' => trim((string) $this->input('descripcion')) ?: null,
        ]);
    }

    public function rules(): array
    {
        return [
            'nombre' => [
                'required', 'string', 'max:30',
                // Nombre único sin importar mayúsculas/minúsculas ("medico" = "Medico")
                function ($attribute, $value, $fail) {
                    $existe = Rol::whereRaw('LOWER(nombre) = ?', [mb_strtolower($value)])
                        ->when($this->route('rol'), fn ($q, $rol) => $q->where('id', '!=', $rol->id))
                        ->exists();

                    if ($existe) {
                        $fail('Ya existe un rol con ese nombre.');
                    }
                },
            ],
            'descripcion' => ['nullable', 'string', 'max:150'],
            'permisos'    => ['nullable', 'array'],
            'permisos.*'  => ['integer', 'exists:permisos,id'],
        ];
    }
}