<?php

namespace App\Services;

use App\Enums\TipoDocumento;
use App\Models\Establecimiento;
use App\Models\Persona;
use App\Models\User;
use App\Support\DocumentoIdentidad;
use Illuminate\Validation\ValidationException;

class PersonaService
{
    /**
     * @param  array<string, mixed>  $filters
     */
    public function listar(array $filters = [])
    {
        $establecimiento = Establecimiento::default();

        $query = Persona::query()
            ->with(['pais', 'alumno', 'profesor'])
            ->where('establecimiento_id', $establecimiento->id);

        if (! empty($filters['q'])) {
            $q = $filters['q'];
            $query->where(function ($builder) use ($q) {
                $builder->where('nombres', 'like', "%{$q}%")
                    ->orWhere('apellido_paterno', 'like', "%{$q}%")
                    ->orWhere('apellido_materno', 'like', "%{$q}%")
                    ->orWhere('numero_documento', 'like', "%{$q}%");
            });
        }

        if (! empty($filters['tipo_documento'])) {
            $query->where('tipo_documento', $filters['tipo_documento']);
        }

        return $query->orderBy('apellido_paterno')->orderBy('nombres')
            ->paginate((int) ($filters['per_page'] ?? 15));
    }

    public function obtener(int $id): Persona
    {
        return $this->baseQuery()->with(['pais', 'alumno', 'profesor', 'creador', 'editor'])->findOrFail($id);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function crear(array $data, User $user): Persona
    {
        $payload = $this->prepararPayload($data);

        return Persona::query()->create(array_merge($payload, [
            'establecimiento_id' => Establecimiento::default()->id,
            'created_by' => $user->id,
        ]));
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function actualizar(Persona $persona, array $data, User $user): Persona
    {
        $payload = $this->prepararPayload($data, $persona);

        $persona->update(array_merge($payload, ['updated_by' => $user->id]));

        return $persona->fresh(['pais', 'alumno', 'profesor']);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function prepararPayload(array $data, ?Persona $persona = null): array
    {
        $tipo = TipoDocumento::from($data['tipo_documento'] ?? $persona?->tipo_documento->value);
        $numero = DocumentoIdentidad::normalizarNumero($tipo, $data['numero_documento'] ?? $persona?->numero_documento ?? '');
        $pais = DocumentoIdentidad::normalizarPais($tipo, $data['pais_documento'] ?? $persona?->pais_documento);

        if ($tipo->requierePais() && ! $pais) {
            throw ValidationException::withMessages([
                'pais_documento' => 'El país de emisión es obligatorio para este tipo de documento.',
            ]);
        }

        if ($tipo === TipoDocumento::Rut && ! DocumentoIdentidad::validarRut($numero)) {
            throw ValidationException::withMessages([
                'numero_documento' => 'El RUT ingresado no es válido.',
            ]);
        }

        $this->validarUnicidadDocumento($tipo, $numero, $pais, $persona?->id);

        return [
            'tipo_documento' => $tipo,
            'numero_documento' => $numero,
            'pais_documento' => $pais,
            'nombres' => $data['nombres'] ?? $persona?->nombres,
            'apellido_paterno' => $data['apellido_paterno'] ?? $persona?->apellido_paterno,
            'apellido_materno' => $data['apellido_materno'] ?? $persona?->apellido_materno,
            'fecha_nacimiento' => $data['fecha_nacimiento'] ?? $persona?->fecha_nacimiento,
            'email' => $data['email'] ?? $persona?->email,
            'telefono' => $data['telefono'] ?? $persona?->telefono,
            'activo' => $data['activo'] ?? $persona?->activo ?? true,
        ];
    }

    private function validarUnicidadDocumento(TipoDocumento $tipo, string $numero, ?string $pais, ?int $exceptId = null): void
    {
        $query = Persona::query()
            ->where('establecimiento_id', Establecimiento::default()->id)
            ->where('tipo_documento', $tipo)
            ->where('numero_documento', $numero)
            ->where('pais_documento', $pais);

        if ($exceptId) {
            $query->where('id', '!=', $exceptId);
        }

        if ($query->exists()) {
            throw ValidationException::withMessages([
                'numero_documento' => 'Ya existe una persona con este documento.',
            ]);
        }
    }

    private function baseQuery()
    {
        return Persona::query()->where('establecimiento_id', Establecimiento::default()->id);
    }
}
