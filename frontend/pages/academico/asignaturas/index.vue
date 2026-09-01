<script setup lang="ts">
import type { Asignatura, EstadoAsignatura } from '~/types/comunidad'

definePageMeta({ layout: 'admin', middleware: 'auth' })

const { api, extractErrorMessage } = useApi()

const asignaturas = ref<Asignatura[]>([])
const saving = ref(false)
const error = ref<string | null>(null)
const showModal = ref(false)
const editing = ref<Asignatura | null>(null)
const successMessage = ref<string | null>(null)

const form = reactive({
  codigo: '',
  nombre: '',
  descripcion: '',
  estado: 'activa' as EstadoAsignatura,
})

async function load() {
  const res = await api<{ data: Asignatura[] }>('/asignaturas?per_page=100')
  asignaturas.value = res.data
}

function openCreate() {
  editing.value = null
  form.codigo = ''
  form.nombre = ''
  form.descripcion = ''
  form.estado = 'activa'
  showModal.value = true
}

function openEdit(a: Asignatura) {
  editing.value = a
  form.codigo = a.codigo
  form.nombre = a.nombre
  form.descripcion = a.descripcion || ''
  form.estado = a.estado
  showModal.value = true
}

async function submit() {
  saving.value = true
  error.value = null
  try {
    if (editing.value) {
      await api(`/asignaturas/${editing.value.id}`, { method: 'PUT', body: form })
      successMessage.value = 'Asignatura actualizada.'
    } else {
      await api('/asignaturas', { method: 'POST', body: form })
      successMessage.value = 'Asignatura creada.'
    }
    showModal.value = false
    await load()
  } catch (e) {
    error.value = extractErrorMessage(e)
  } finally {
    saving.value = false
  }
}

onMounted(load)
</script>

<template>
  <LteAppContent
    title="Asignaturas"
    :breadcrumbs="[{ label: 'Inicio', href: '/dashboard' }, { label: 'Académico' }, { label: 'Asignaturas' }]"
  >
    <LteAlert v-if="successMessage" theme="success" dismissible class="mb-3" @close="successMessage = null">{{ successMessage }}</LteAlert>

    <LteCard title="Catálogo de asignaturas" icon="bi-book" theme="primary" variant="outline">
      <div class="mb-3">
        <LteButton theme="success" icon="bi-plus-lg" @click="openCreate">Nueva asignatura</LteButton>
      </div>
      <table class="table table-hover">
        <thead><tr><th>Código</th><th>Nombre</th><th>Estado</th><th></th></tr></thead>
        <tbody>
          <tr v-if="!asignaturas.length"><td colspan="4" class="text-muted text-center">Sin asignaturas registradas</td></tr>
          <tr v-for="a in asignaturas" :key="a.id">
            <td><strong>{{ a.codigo }}</strong></td>
            <td>{{ a.nombre }}</td>
            <td><span :class="['badge', a.estado === 'activa' ? 'text-bg-success' : 'text-bg-secondary']">{{ a.estado_label }}</span></td>
            <td class="text-end"><LteButton size="sm" theme="outline-primary" icon="bi-pencil" @click="openEdit(a)">Editar</LteButton></td>
          </tr>
        </tbody>
      </table>
    </LteCard>

    <LteModal v-model="showModal" :title="editing ? 'Editar asignatura' : 'Nueva asignatura'">
      <LteAlert v-if="error" theme="danger" class="mb-3">{{ error }}</LteAlert>
      <div class="row g-3">
        <div class="col-md-4"><LteInput v-model="form.codigo" label="Código" :disabled="!!editing" /></div>
        <div class="col-md-8"><LteInput v-model="form.nombre" label="Nombre" /></div>
        <div class="col-12"><LteInput v-model="form.descripcion" label="Descripción" /></div>
        <div v-if="editing" class="col-md-4">
          <LteSelect v-model="form.estado" label="Estado">
            <option value="activa">Activa</option>
            <option value="inactiva">Inactiva</option>
          </LteSelect>
        </div>
      </div>
      <template #footer>
        <LteButton theme="secondary" @click="showModal = false">Cancelar</LteButton>
        <LteButton theme="primary" :loading="saving" @click="submit">{{ editing ? 'Guardar' : 'Crear' }}</LteButton>
      </template>
    </LteModal>
  </LteAppContent>
</template>
