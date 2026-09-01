<script setup lang="ts">
import type { EstadoAsistencia } from '~/types/asistencia'

definePageMeta({ layout: 'admin', middleware: 'auth' })

const { api, extractErrorMessage } = useApi()

const estados = ref<EstadoAsistencia[]>([])
const saving = ref(false)
const error = ref<string | null>(null)
const showModal = ref(false)
const successMessage = ref<string | null>(null)

const form = reactive({
  codigo: '',
  nombre: '',
  cuenta_como_presente: false,
  cuenta_como_ausencia: false,
  cuenta_como_atraso: false,
  color: '#6c757d',
})

async function load() {
  const res = await api<{ data: EstadoAsistencia[] }>('/estados-asistencia')
  estados.value = res.data
}

async function submit() {
  saving.value = true
  error.value = null
  try {
    await api('/estados-asistencia', { method: 'POST', body: form })
    successMessage.value = 'Estado creado.'
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
  <LteAppContent title="Estados de asistencia" :breadcrumbs="[{ label: 'Inicio', href: '/dashboard' }, { label: 'Estados asistencia' }]">
    <LteAlert v-if="successMessage" theme="success" dismissible class="mb-3" @close="successMessage = null">{{ successMessage }}</LteAlert>
    <LteCard title="Catálogo de estados" icon="bi-list-check" theme="primary" variant="outline">
      <div class="mb-3"><LteButton theme="success" icon="bi-plus-lg" @click="showModal = true">Nuevo estado</LteButton></div>
      <table class="table table-hover">
        <thead><tr><th>Código</th><th>Nombre</th><th>Presente</th><th>Ausencia</th><th>Atraso</th><th>Sistema</th></tr></thead>
        <tbody>
          <tr v-for="e in estados" :key="e.id">
            <td><code>{{ e.codigo }}</code></td>
            <td><span class="badge" :style="{ backgroundColor: e.color || '#6c757d' }">{{ e.nombre }}</span></td>
            <td>{{ e.cuenta_como_presente ? '✓' : '—' }}</td>
            <td>{{ e.cuenta_como_ausencia ? '✓' : '—' }}</td>
            <td>{{ e.cuenta_como_atraso ? '✓' : '—' }}</td>
            <td>{{ e.es_sistema ? 'Sí' : 'No' }}</td>
          </tr>
        </tbody>
      </table>
    </LteCard>
    <LteModal v-model="showModal" title="Nuevo estado">
      <LteAlert v-if="error" theme="danger" class="mb-3">{{ error }}</LteAlert>
      <div class="row g-3">
        <div class="col-md-4"><LteInput v-model="form.codigo" label="Código" /></div>
        <div class="col-md-8"><LteInput v-model="form.nombre" label="Nombre" /></div>
        <div class="col-md-4 form-check"><input v-model="form.cuenta_como_presente" type="checkbox" class="form-check-input"> Presente</div>
        <div class="col-md-4 form-check"><input v-model="form.cuenta_como_ausencia" type="checkbox" class="form-check-input"> Ausencia</div>
        <div class="col-md-4 form-check"><input v-model="form.cuenta_como_atraso" type="checkbox" class="form-check-input"> Atraso</div>
      </div>
      <template #footer>
        <LteButton theme="secondary" @click="showModal = false">Cancelar</LteButton>
        <LteButton theme="primary" :loading="saving" @click="submit">Crear</LteButton>
      </template>
    </LteModal>
  </LteAppContent>
</template>
