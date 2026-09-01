<script setup lang="ts">
import type { Vacacion } from '~/types/asistencia'

definePageMeta({ layout: 'admin', middleware: 'auth' })

const { api, extractErrorMessage } = useApi()
const catalogos = useCatalogosStore()

const vacaciones = ref<Vacacion[]>([])
const periodoId = ref<number | ''>('')
const saving = ref(false)
const error = ref<string | null>(null)
const showModal = ref(false)
const successMessage = ref<string | null>(null)

const form = reactive({ nombre: '', fecha_inicio: '', fecha_fin: '', descripcion: '' })

async function load() {
  if (!periodoId.value) { vacaciones.value = []; return }
  const res = await api<{ data: Vacacion[] }>(`/vacaciones?periodo_escolar_id=${periodoId.value}`)
  vacaciones.value = res.data
}

async function submit() {
  saving.value = true
  error.value = null
  try {
    await api('/vacaciones', { method: 'POST', body: { ...form, periodo_escolar_id: periodoId.value } })
    successMessage.value = 'Vacaciones registradas.'
    showModal.value = false
    await load()
  } catch (e) {
    error.value = extractErrorMessage(e)
  } finally {
    saving.value = false
  }
}

onMounted(async () => {
  await catalogos.load()
  const activo = catalogos.periodos.find((p) => p.estado === 'activo')
  if (activo) periodoId.value = activo.id
  await load()
})
watch(periodoId, load)
</script>

<template>
  <LteAppContent title="Vacaciones" :breadcrumbs="[{ label: 'Inicio', href: '/dashboard' }, { label: 'Calendario' }, { label: 'Vacaciones' }]">
    <LteAlert v-if="successMessage" theme="success" dismissible class="mb-3" @close="successMessage = null">{{ successMessage }}</LteAlert>
    <LteCard title="Períodos de vacaciones" icon="bi-sun" theme="primary" variant="outline">
      <div class="row g-3 mb-3">
        <div class="col-md-4">
          <LteSelect v-model="periodoId" label="Período">
            <option value="">Seleccione</option>
            <option v-for="p in catalogos.periodos" :key="p.id" :value="p.id">{{ p.anio }} — {{ p.nombre }}</option>
          </LteSelect>
        </div>
        <div class="col-md-4 d-flex align-items-end">
          <LteButton theme="success" icon="bi-plus-lg" :disabled="!periodoId" @click="showModal = true">Nueva vacación</LteButton>
        </div>
      </div>
      <table class="table table-hover">
        <thead><tr><th>Nombre</th><th>Desde</th><th>Hasta</th><th>Estado</th></tr></thead>
        <tbody>
          <tr v-if="!vacaciones.length"><td colspan="4" class="text-muted text-center">Sin vacaciones</td></tr>
          <tr v-for="v in vacaciones" :key="v.id">
            <td>{{ v.nombre }}</td>
            <td>{{ v.fecha_inicio }}</td>
            <td>{{ v.fecha_fin }}</td>
            <td><span class="badge text-bg-success">{{ v.estado_label }}</span></td>
          </tr>
        </tbody>
      </table>
    </LteCard>
    <LteModal v-model="showModal" title="Nueva vacación">
      <LteAlert v-if="error" theme="danger" class="mb-3">{{ error }}</LteAlert>
      <div class="row g-3">
        <div class="col-12"><LteInput v-model="form.nombre" label="Nombre" /></div>
        <div class="col-md-6"><LteInput v-model="form.fecha_inicio" type="date" label="Inicio" /></div>
        <div class="col-md-6"><LteInput v-model="form.fecha_fin" type="date" label="Término" /></div>
        <div class="col-12"><LteInput v-model="form.descripcion" label="Descripción" /></div>
      </div>
      <template #footer>
        <LteButton theme="secondary" @click="showModal = false">Cancelar</LteButton>
        <LteButton theme="primary" :loading="saving" @click="submit">Guardar</LteButton>
      </template>
    </LteModal>
  </LteAppContent>
</template>
