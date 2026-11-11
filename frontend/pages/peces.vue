<template>
  <LteAppContent
    title="Peces con Services"
    :breadcrumbs="[
      { label: 'Inicio', href: '/dashboard' },
      { label: 'Peces' },
    ]"
  >
    <div class="text-end mb-3">
      <LteButton
        theme="primary"
        label="Nuevo pez"
        icon="bi-plus-circle"
        @click="openCreate"
      />
    </div>

    <LteCard
      title="Listado de peces"
      icon="bi-water"
      theme="primary"
      variant="outline"
    >
      <p v-if="store.loading" class="text-muted">
        Cargando peces...
      </p>

      <LteAlert
        v-else-if="store.error"
        theme="danger"
        title="Error"
      >
        {{ store.error }}
      </LteAlert>

      <template v-else>
        <p>Total de peces: {{ store.peces.length }}</p>

        <p v-if="store.peces.length === 0" class="text-muted">
          No hay peces registrados.
        </p>

        <div v-else class="table-responsive">
          <table class="table table-hover align-middle">
            <thead>
              <tr>
                <th>Código</th>
                <th>Nombre</th>
                <th>Especie</th>
                <th>Tipo de agua</th>
                <th>Longitud</th>
                <th>Estado</th>
                <th class="text-end">Acciones</th>
              </tr>
            </thead>

            <tbody>
              <tr v-for="pez in store.peces" :key="pez.id">
                <td>{{ pez.codigo }}</td>
                <td>{{ pez.nombre }}</td>
                <td>{{ pez.especie }}</td>
                <td>{{ pez.tipo_agua }}</td>
                <td>
                  {{ pez.longitud_cm !== null ? `${pez.longitud_cm} cm` : 'Sin registro' }}
                </td>
                <td>{{ pez.estado }}</td>
                <td class="text-end">
                  <div class="d-inline-flex gap-2">
                    <LteButton
                      theme="info"
                      size="sm"
                      outline
                      label="Editar"
                      icon="bi-pencil"
                      @click="openEdit(pez)"
                    />

                    <LteButton
                      theme="danger"
                      size="sm"
                      outline
                      :label="store.deletingId === pez.id ? 'Eliminando...' : 'Eliminar'"
                      icon="bi-trash"
                      :disabled="store.deletingId === pez.id"
                      @click="confirmRemove(pez)"
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
      :title="editing ? 'Editar pez' : 'Nuevo pez'"
      theme="primary"
      centered
    >
      <form @submit.prevent="save">
        <div class="row">
          <div class="col-md-4">
            <LteInput
              v-model="form.codigo"
              label="Código"
              placeholder="PEZ-001"
              required
            />
          </div>

          <div class="col-md-8">
            <LteInput
              v-model="form.nombre"
              label="Nombre"
              placeholder="Nombre del pez"
              required
            />
          </div>

          <div class="col-md-6">
            <LteInput
              v-model="form.especie"
              label="Especie"
              placeholder="Betta splendens"
              required
            />
          </div>

          <div class="col-md-6">
            <LteSelect
              v-model="form.tipo_agua"
              label="Tipo de agua"
              :options="tipoAguaOptions"
              required
            />
          </div>

          <div class="col-md-6">
            <LteInput
              v-model="form.longitud_cm"
              label="Longitud (cm)"
              type="number"
              min="0"
              step="0.01"
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
  EstadoPez,
  Pez,
  PezPayload,
  TipoAgua,
} from '~/types/pez'

definePageMeta({
  layout: 'admin',
  middleware: 'auth',
})

const store = usePecesStore()
const editing = ref<Pez | null>(null)
const showModal = ref(false)

const form = reactive({
  codigo: '',
  nombre: '',
  especie: '',
  tipo_agua: 'dulce' as TipoAgua,
  longitud_cm: '',
  estado: 'activo' as EstadoPez,
})

const tipoAguaOptions = [
  { value: 'dulce', label: 'Agua dulce' },
  { value: 'salada', label: 'Agua salada' },
]

const estadoOptions = [
  { value: 'activo', label: 'Activo' },
  { value: 'inactivo', label: 'Inactivo' },
]

onMounted(() => store.fetchAll())

function openCreate() {
  Object.assign(form, {
    codigo: '',
    nombre: '',
    especie: '',
    tipo_agua: 'dulce',
    longitud_cm: '',
    estado: 'activo',
  })

  editing.value = null
  showModal.value = true
}

function openEdit(pez: Pez) {
  Object.assign(form, {
    codigo: pez.codigo,
    nombre: pez.nombre,
    especie: pez.especie,
    tipo_agua: pez.tipo_agua,
    longitud_cm: pez.longitud_cm?.toString() ?? '',
    estado: pez.estado,
  })

  editing.value = pez
  showModal.value = true
}

async function save() {
  const payload: PezPayload = {
    codigo: form.codigo.trim(),
    nombre: form.nombre.trim(),
    especie: form.especie.trim(),
    tipo_agua: form.tipo_agua,
    longitud_cm: form.longitud_cm === '' ? null : Number(form.longitud_cm),
    estado: form.estado,
  }

  try {
    if (editing.value) {
      await store.update(editing.value.id, payload)
    } else {
      await store.create(payload)
    }

    showModal.value = false
  } catch {
    // El store mantiene el mensaje y el modal queda abierto.
  }
}

async function confirmRemove(pez: Pez) {
  if (!confirm(`¿Eliminar al pez ${pez.nombre}?`)) {
    return
  }

  try {
    await store.remove(pez.id)
  } catch {
    // El store muestra el mensaje de error.
  }
}
</script>
