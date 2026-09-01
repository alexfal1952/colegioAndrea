<script setup lang="ts">
import type { Curso, Profesor } from '~/types/comunidad'

definePageMeta({ layout: 'admin', middleware: 'auth' })

const { api, extractErrorMessage } = useApi()
const catalogos = useCatalogosStore()

const cursos = ref<Curso[]>([])
const profesores = ref<Profesor[]>([])
const periodoId = ref<number | ''>('')
const saving = ref(false)
const error = ref<string | null>(null)
const showModal = ref(false)
const successMessage = ref<string | null>(null)

const form = reactive({
  periodo_escolar_id: '' as number | '',
  nivel_id: '' as number | '',
  paralelo: 'A',
  cupo_maximo: null as number | null,
  profesor_jefe_id: null as number | null,
})

async function loadProfesores() {
  const res = await api<{ data: Profesor[] }>('/profesores')
  profesores.value = res.data
}

async function loadCursos() {
  if (!periodoId.value) { cursos.value = []; return }
  const res = await api<{ data: Curso[] }>(`/cursos?periodo_escolar_id=${periodoId.value}`)
  cursos.value = res.data
}

async function submit() {
  saving.value = true
  error.value = null
  try {
    await api('/cursos', { method: 'POST', body: form })
    successMessage.value = 'Curso creado.'
    showModal.value = false
    periodoId.value = form.periodo_escolar_id as number
    await loadCursos()
  } catch (e) {
    error.value = extractErrorMessage(e)
  } finally {
    saving.value = false
  }
}

function openCreate() {
  form.periodo_escolar_id = periodoId.value || (catalogos.periodos[0]?.id ?? '')
  form.nivel_id = catalogos.niveles[0]?.id ?? ''
  form.paralelo = 'A'
  showModal.value = true
}

onMounted(async () => {
  await catalogos.load()
  await loadProfesores()
  const activo = catalogos.periodos.find((p) => p.estado === 'activo')
  if (activo) periodoId.value = activo.id
  await loadCursos()
})

watch(periodoId, loadCursos)
</script>

<template>
  <LteAppContent
    title="Cursos"
    :breadcrumbs="[{ label: 'Inicio', href: '/dashboard' }, { label: 'Académico' }, { label: 'Cursos' }]"
  >
    <LteAlert v-if="successMessage" theme="success" dismissible class="mb-3" @close="successMessage = null">{{ successMessage }}</LteAlert>

    <LteCard title="Cursos por período" icon="bi-journal-bookmark" theme="primary" variant="outline">
      <div class="row g-3 mb-3">
        <div class="col-md-4">
          <LteSelect v-model="periodoId" label="Período escolar">
            <option value="">Seleccione período</option>
            <option v-for="p in catalogos.periodos" :key="p.id" :value="p.id">{{ p.anio }} — {{ p.nombre }} ({{ p.estado_label }})</option>
          </LteSelect>
        </div>
        <div class="col-md-4 d-flex align-items-end">
          <LteButton theme="success" icon="bi-plus-lg" :disabled="!periodoId" @click="openCreate">Nuevo curso</LteButton>
        </div>
      </div>

      <table class="table table-hover">
        <thead><tr><th>Curso</th><th>Paralelo</th><th>Jornada</th><th>Profesor jefe</th><th>Cupo</th><th></th></tr></thead>
        <tbody>
          <tr v-if="!cursos.length"><td colspan="6" class="text-muted text-center">Seleccione un período o cree cursos</td></tr>
          <tr v-for="c in cursos" :key="c.id">
            <td>{{ c.nombre_display }}</td>
            <td>{{ c.paralelo }}</td>
            <td>{{ c.jornada_label || '—' }}</td>
            <td>{{ c.profesor_jefe?.persona?.nombre_completo || '—' }}</td>
            <td>{{ c.cupo_maximo || '—' }}</td>
            <td class="text-end">
              <NuxtLink :to="`/academico/cursos/${c.id}`" class="btn btn-sm btn-outline-primary">
                <i class="bi bi-eye" /> Ver detalle
              </NuxtLink>
            </td>
          </tr>
        </tbody>
      </table>
    </LteCard>

    <LteModal v-model="showModal" title="Nuevo curso">
      <LteAlert v-if="error" theme="danger" class="mb-3">{{ error }}</LteAlert>
      <div class="row g-3">
        <div class="col-md-6">
          <LteSelect v-model="form.periodo_escolar_id" label="Período">
            <option v-for="p in catalogos.periodos" :key="p.id" :value="p.id">{{ p.anio }} — {{ p.nombre }}</option>
          </LteSelect>
        </div>
        <div class="col-md-6">
          <LteSelect v-model="form.nivel_id" label="Nivel">
            <option v-for="n in catalogos.niveles" :key="n.id" :value="n.id">{{ n.nombre }}</option>
          </LteSelect>
        </div>
        <div class="col-md-4"><LteInput v-model="form.paralelo" label="Paralelo" /></div>
        <div class="col-md-4"><LteInput v-model="form.cupo_maximo" type="number" label="Cupo máximo" /></div>
        <div class="col-md-4">
          <LteSelect v-model="form.profesor_jefe_id" label="Profesor jefe">
            <option :value="null">Sin asignar</option>
            <option v-for="pr in profesores" :key="pr.id" :value="pr.id">{{ pr.persona?.nombre_completo }}</option>
          </LteSelect>
        </div>
      </div>
      <template #footer>
        <LteButton theme="secondary" @click="showModal = false">Cancelar</LteButton>
        <LteButton theme="primary" :loading="saving" @click="submit">Crear</LteButton>
      </template>
    </LteModal>
  </LteAppContent>
</template>
