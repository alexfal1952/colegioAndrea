<script setup lang="ts">
import type { Curso } from '~/types/comunidad'
import type { SuspensionClases } from '~/types/asistencia'

definePageMeta({ layout: 'admin', middleware: 'auth' })

const { api, extractErrorMessage } = useApi()
const catalogos = useCatalogosStore()

const suspensiones = ref<SuspensionClases[]>([])
const cursos = ref<Curso[]>([])
const periodoId = ref<number | ''>('')
const saving = ref(false)
const error = ref<string | null>(null)
const showModal = ref(false)
const successMessage = ref<string | null>(null)

const form = reactive({
  fecha: '',
  nombre: '',
  motivo: '',
  curso_id: null as number | null,
})

async function loadCursos() {
  if (!periodoId.value) { cursos.value = []; return }
  const res = await api<{ data: Curso[] }>(`/cursos?periodo_escolar_id=${periodoId.value}`)
  cursos.value = res.data
}

async function load() {
  if (!periodoId.value) { suspensiones.value = []; return }
  const res = await api<{ data: SuspensionClases[] }>(`/suspensiones-clases?periodo_escolar_id=${periodoId.value}`)
  suspensiones.value = res.data
}

async function submit() {
  saving.value = true
  error.value = null
  try {
    await api('/suspensiones-clases', {
      method: 'POST',
      body: { ...form, periodo_escolar_id: periodoId.value, tipo: 'total' },
    })
    successMessage.value = 'Suspensión registrada.'
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
  await loadCursos()
  await load()
})
watch(periodoId, async () => { await loadCursos(); await load() })
</script>

<template>
  <LteAppContent title="Suspensiones de clases" :breadcrumbs="[{ label: 'Inicio', href: '/dashboard' }, { label: 'Calendario' }, { label: 'Suspensiones' }]">
    <LteAlert v-if="successMessage" theme="success" dismissible class="mb-3" @close="successMessage = null">{{ successMessage }}</LteAlert>
    <LteCard title="Suspensiones" icon="bi-slash-circle" theme="primary" variant="outline">
      <div class="row g-3 mb-3">
        <div class="col-md-4">
          <LteSelect v-model="periodoId" label="Período">
            <option value="">Seleccione</option>
            <option v-for="p in catalogos.periodos" :key="p.id" :value="p.id">{{ p.anio }} — {{ p.nombre }}</option>
          </LteSelect>
        </div>
        <div class="col-md-4 d-flex align-items-end">
          <LteButton theme="success" icon="bi-plus-lg" :disabled="!periodoId" @click="showModal = true">Nueva suspensión</LteButton>
        </div>
      </div>
      <table class="table table-hover">
        <thead><tr><th>Fecha</th><th>Nombre</th><th>Alcance</th><th>Motivo</th></tr></thead>
        <tbody>
          <tr v-for="s in suspensiones" :key="s.id">
            <td>{{ s.fecha }}</td>
            <td>{{ s.nombre }}</td>
            <td>{{ s.alcance_label }}</td>
            <td>{{ s.motivo || '—' }}</td>
          </tr>
        </tbody>
      </table>
    </LteCard>
    <LteModal v-model="showModal" title="Nueva suspensión">
      <LteAlert v-if="error" theme="danger" class="mb-3">{{ error }}</LteAlert>
      <div class="row g-3">
        <div class="col-md-6"><LteInput v-model="form.fecha" type="date" label="Fecha" /></div>
        <div class="col-md-6"><LteInput v-model="form.nombre" label="Nombre" /></div>
        <div class="col-12">
          <LteSelect v-model="form.curso_id" label="Alcance">
            <option :value="null">Todo el establecimiento</option>
            <option v-for="c in cursos" :key="c.id" :value="c.id">{{ c.nombre_display }}</option>
          </LteSelect>
        </div>
        <div class="col-12"><LteInput v-model="form.motivo" label="Motivo" /></div>
      </div>
      <template #footer>
        <LteButton theme="secondary" @click="showModal = false">Cancelar</LteButton>
        <LteButton theme="primary" :loading="saving" @click="submit">Guardar</LteButton>
      </template>
    </LteModal>
  </LteAppContent>
</template>
