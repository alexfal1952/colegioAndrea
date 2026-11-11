<template>
    <LteAppContent
        title="Animales"
        :breadcrumbs="[
        { label: 'Inicio', href: '/dashboard' },
        { label: 'Animales' },
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
            <p v-if="store.loading" class="text-muted">
                Cargando animales...
            </p>

            <LteAlert
                v-else-if="store.error"
                theme="danger"
                title="Error"
            >
                {{ store.error }}
            </LteAlert>

            <template v-else>
                <p>Total de animales: {{ store.animales.length }}</p>

                <p v-if="store.animales.length === 0" class="text-muted">
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
                        v-for="animal in store.animales"
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
                                        :label="store.deletingId === animal.id ? 'Eliminando...' : 'Eliminar'"
                                        icon="bi-trash"
                                        :disabled="store.deletingId === animal.id"
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
                    <label for="observaciones" class="form-label">
                        Observaciones
                    </label>

                    <textarea
                        id="observaciones"
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
                    :label="store.saving ? 'Guardando...' : 'Guardar'"
                    icon="bi-save"
                    :disabled="store.saving"
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

const store = useAnimalesStore()
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

onMounted(async () => {
  await store.fetchAll()
})

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

  try {
    if (editing.value) {
      await store.update(editing.value.id, payload)
    } else {
      await store.create(payload)
    }

    showModal.value = false
  } catch {
    // Si Laravel rechaza los datos, el modal permanece abierto.
  }
}

function openEdit(animal: Animal) {
  editing.value = animal

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
    editing.value = null
    showModal.value = true
}

async function confirmRemove(animal: Animal) {
  const confirmed = confirm(
    `¿Eliminar al animal ${animal.nombre}?`,
  )

  if (!confirmed) {
    return
  }

  try {
    await store.remove(animal.id)
  } catch {
    // El store muestra el mensaje de error.
  }
}

</script>
