<template>
  <LteAppContent
    title="Animales Dos (sin Pinia)"
    :breadcrumbs="[
      { label: 'Inicio', href: '/dashboard' },
      { label: 'Animales Dos' },
    ]"
  >
    <div class="text-end mb-3">
      <LteButton
        theme="primary"
        label="Nuevo animal"
        icon="bi-plus-circle"
        @click="openCreate"
      />
    </div>

    <LteCard
      title="Listado de animales"
      icon="bi-list-ul"
      theme="primary"
      variant="outline"
    >
      <p v-if="loading" class="text-muted">
        Cargando animales...
      </p>

      <LteAlert
        v-else-if="error"
        theme="danger"
        title="Error"
      >
        {{ error }}
      </LteAlert>

      <template v-else>
        <p>Total de animales: {{ animales.length }}</p>

        <p v-if="animales.length === 0" class="text-muted">
          No hay animales registrados.
        </p>

        <div v-else class="table-responsive">
          <table class="table table-hover align-middle">
            <thead>
              <tr>
                <th>Código</th>
                <th>Nombre</th>
                <th>Especie</th>
                <th>Raza</th>
                <th>Peso</th>
                <th>Estado</th>
                <th class="text-end">Acciones</th>
              </tr>
            </thead>

            <tbody>
              <tr
                v-for="animal in animales"
                :key="animal.id"
              >
                <td>{{ animal.codigo }}</td>
                <td>{{ animal.nombre }}</td>
                <td>{{ animal.especie }}</td>
                <td>{{ animal.raza ?? 'Sin raza' }}</td>
                <td>
                  {{ animal.peso_kg !== null ? `${animal.peso_kg} kg` : 'Sin registro' }}
                </td>
                <td>{{ animal.estado }}</td>
                <td class="text-end">
                  <div class="d-inline-flex gap-2">
                    <LteButton
                      theme="info"
                      size="sm"
                      outline
                      label="Editar"
                      icon="bi-pencil"
                      @click="openEdit(animal)"
                    />

                    <LteButton
                      theme="danger"
                      size="sm"
                      outline
                      :label="deletingId === animal.id ? 'Eliminando...' : 'Eliminar'"
                      icon="bi-trash"
                      :disabled="deletingId === animal.id"
                      @click="confirmRemove(animal)"
                    />
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </template>
    </LteCard>

    <LteModal
      v-model="showModal"
      :title="editing ? 'Editar animal' : 'Nuevo animal'"
      theme="primary"
      centered
    >
      <form @submit.prevent="save">
        <div class="row">
          <div class="col-md-4">
            <LteInput
              v-model="form.codigo"
              label="Código"
              placeholder="ANI-004"
              required
            />
          </div>

          <div class="col-md-8">
            <LteInput
              v-model="form.nombre"
              label="Nombre"
              placeholder="Nombre del animal"
              required
            />
          </div>

          <div class="col-md-6">
            <LteInput
              v-model="form.especie"
              label="Especie"
              placeholder="Canino"
              required
            />
          </div>

          <div class="col-md-6">
            <LteInput
              v-model="form.raza"
              label="Raza"
              placeholder="Mestiza"
            />
          </div>

          <div class="col-md-6">
            <LteSelect
              v-model="form.sexo"
              label="Sexo"
              :options="sexoOptions"
              required
            />
          </div>

          <div class="col-md-6">
            <LteSelect
              v-model="form.estado"
              label="Estado"
              :options="estadoOptions"
              required
            />
          </div>

          <div class="col-md-6">
            <LteInput
              v-model="form.fecha_nacimiento"
              label="Fecha de nacimiento"
              type="date"
            />
          </div>

          <div class="col-md-6">
            <LteInput
              v-model="form.peso_kg"
              label="Peso (kg)"
              type="number"
              min="0"
              step="0.01"
            />
          </div>

          <div class="col-12 mb-3">
            <label for="observaciones-dos" class="form-label">
              Observaciones
            </label>

            <textarea
              id="observaciones-dos"
              v-model="form.observaciones"
              class="form-control"
              rows="3"
              maxlength="1000"
            />
          </div>
        </div>
      </form>

      <template #footer="{ close }">
        <LteButton
          theme="secondary"
          label="Cancelar"
          outline
          @click="close()"
        />

        <LteButton
          theme="primary"
          :label="saving ? 'Guardando...' : 'Guardar'"
          icon="bi-save"
          :disabled="saving"
          @click="save"
        />
      </template>
    </LteModal>
  </LteAppContent>
</template>

<script setup lang="ts">
import type {
  Animal,
  AnimalPayload,
  EstadoAnimal,
  SexoAnimal,
} from '~/types/animal'

definePageMeta({
  layout: 'admin',
  middleware: 'auth',
})

const config = useRuntimeConfig()

const animales = ref<Animal[]>([])
const loading = ref(false)
const saving = ref(false)
const deletingId = ref<number | null>(null)
const error = ref<string | null>(null)
const editing = ref<Animal | null>(null)
const showModal = ref(false)

const form = reactive({
  codigo: '',
  nombre: '',
  especie: '',
  raza: '',
  sexo: 'desconocido' as SexoAnimal,
  fecha_nacimiento: '',
  peso_kg: '',
  estado: 'activo' as EstadoAnimal,
  observaciones: '',
})

const sexoOptions = [
  { value: 'desconocido', label: 'Desconocido' },
  { value: 'macho', label: 'Macho' },
  { value: 'hembra', label: 'Hembra' },
]

const estadoOptions = [
  { value: 'activo', label: 'Activo' },
  { value: 'adoptado', label: 'Adoptado' },
  { value: 'en_tratamiento', label: 'En tratamiento' },
  { value: 'inactivo', label: 'Inactivo' },
]

onMounted(fetchAll)

function authHeaders() {
  const token = localStorage.getItem('auth_token')

  return {
    Authorization: `Bearer ${token}`,
    Accept: 'application/json',
  }
}

async function fetchAll() {
  loading.value = true
  error.value = null

  try {
    animales.value = await $fetch<Animal[]>(
      `${config.public.apiBase}/animales`,
      { headers: authHeaders() },
    )
  } catch (caught: unknown) {
    error.value = extractErrorMessage(
      caught,
      'No se pudieron cargar los animales.',
    )
  } finally {
    loading.value = false
  }
}

async function create(payload: AnimalPayload) {
  const animal = await $fetch<Animal>(
    `${config.public.apiBase}/animales`,
    {
      method: 'POST',
      body: payload,
      headers: authHeaders(),
    },
  )

  animales.value.push(animal)
  sortAnimales()
}

async function update(id: number, payload: AnimalPayload) {
  const animal = await $fetch<Animal>(
    `${config.public.apiBase}/animales/${id}`,
    {
      method: 'PUT',
      body: payload,
      headers: authHeaders(),
    },
  )

  const index = animales.value.findIndex(
    item => item.id === id,
  )

  if (index !== -1) {
    animales.value[index] = animal
  }

  sortAnimales()
}

async function remove(id: number) {
  deletingId.value = id
  error.value = null

  try {
    await $fetch(
      `${config.public.apiBase}/animales/${id}`,
      {
        method: 'DELETE',
        headers: authHeaders(),
      },
    )

    animales.value = animales.value.filter(
      animal => animal.id !== id,
    )
  } catch (caught: unknown) {
    error.value = extractErrorMessage(
      caught,
      'No se pudo eliminar el animal.',
    )
    throw caught
  } finally {
    deletingId.value = null
  }
}

async function save() {
  const payload: AnimalPayload = {
    codigo: form.codigo.trim(),
    nombre: form.nombre.trim(),
    especie: form.especie.trim(),
    raza: form.raza.trim() || null,
    sexo: form.sexo,
    fecha_nacimiento: form.fecha_nacimiento || null,
    peso_kg: form.peso_kg === ''
      ? null
      : Number(form.peso_kg),
    estado: form.estado,
    observaciones: form.observaciones.trim() || null,
  }

  saving.value = true
  error.value = null

  try {
    if (editing.value) {
      await update(editing.value.id, payload)
    } else {
      await create(payload)
    }

    showModal.value = false
  } catch (caught: unknown) {
    error.value = extractErrorMessage(
      caught,
      editing.value
        ? 'No se pudo actualizar el animal.'
        : 'No se pudo guardar el animal.',
    )
  } finally {
    saving.value = false
  }
}

function openEdit(animal: Animal) {
  editing.value = animal
  error.value = null

  Object.assign(form, {
    codigo: animal.codigo,
    nombre: animal.nombre,
    especie: animal.especie,
    raza: animal.raza ?? '',
    sexo: animal.sexo,
    fecha_nacimiento: animal.fecha_nacimiento ?? '',
    peso_kg: animal.peso_kg?.toString() ?? '',
    estado: animal.estado,
    observaciones: animal.observaciones ?? '',
  })

  showModal.value = true
}

function openCreate() {
  editing.value = null
  error.value = null

  Object.assign(form, {
    codigo: '',
    nombre: '',
    especie: '',
    raza: '',
    sexo: 'desconocido',
    fecha_nacimiento: '',
    peso_kg: '',
    estado: 'activo',
    observaciones: '',
  })

  showModal.value = true
}

async function confirmRemove(animal: Animal) {
  if (!confirm(`¿Eliminar al animal ${animal.nombre}?`)) {
    return
  }

  try {
    await remove(animal.id)
  } catch {
    // El mensaje de error queda guardado en error.value.
  }
}

function sortAnimales() {
  animales.value.sort((a, b) =>
    a.nombre.localeCompare(b.nombre, 'es'),
  )
}

function extractErrorMessage(
  caught: unknown,
  fallback: string,
): string {
  if (typeof caught === 'object' && caught !== null && 'data' in caught) {
    const data = (caught as {
      data?: {
        message?: string
        errors?: Record<string, string[]>
      }
    }).data

    const firstError = data?.errors
      ? Object.values(data.errors)[0]?.[0]
      : null

    return firstError || data?.message || fallback
  }

  return fallback
}
</script>
