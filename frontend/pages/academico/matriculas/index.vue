<script setup lang="ts">
import type { Alumno, Curso, Matricula } from '~/types/comunidad'

definePageMeta({ layout: 'admin', middleware: 'auth' })

const { api, extractErrorMessage } = useApi()
const catalogos = useCatalogosStore()

const matriculas = ref<Matricula[]>([])
const alumnos = ref<Alumno[]>([])
const cursos = ref<Curso[]>([])
const periodoId = ref<number | ''>('')
const cursoId = ref<number | ''>('')
const saving = ref(false)
const error = ref<string | null>(null)
const showModal = ref(false)
const successMessage = ref<string | null>(null)

const form = reactive({
  periodo_escolar_id: '' as number | '',
  curso_id: '' as number | '',
  alumno_id: '' as number | '',
  fecha_matricula: new Date().toISOString().slice(0, 10),
})

async function loadAlumnos() {
  const res = await api<{ data: Alumno[] }>('/alumnos')
  alumnos.value = res.data
}

async function loadCursos() {
  if (!periodoId.value) { cursos.value = []; return }
  const res = await api<{ data: Curso[] }>(`/cursos?periodo_escolar_id=${periodoId.value}`)
  cursos.value = res.data
}

async function loadMatriculas() {
  const params = new URLSearchParams()
  if (periodoId.value) params.set('periodo_escolar_id', String(periodoId.value))
  if (cursoId.value) params.set('curso_id', String(cursoId.value))
  const res = await api<{ data: Matricula[] }>(`/matriculas?${params}`)
  matriculas.value = res.data
}

async function submit() {
  saving.value = true
  error.value = null
  try {
    await api('/matriculas', {
      method: 'POST',
      body: {
        ...form,
        periodo_escolar_id: form.periodo_escolar_id || periodoId.value,
      },
    })
    successMessage.value = 'Matrícula registrada.'
    showModal.value = false
    await loadMatriculas()
  } catch (e) {
    error.value = extractErrorMessage(e)
  } finally {
    saving.value = false
  }
}

function openCreate() {
  form.periodo_escolar_id = periodoId.value as number
  form.curso_id = cursoId.value as number || (cursos.value[0]?.id ?? '')
  form.alumno_id = ''
  showModal.value = true
}

onMounted(async () => {
  await catalogos.load()
  await loadAlumnos()
  const activo = catalogos.periodos.find((p) => p.estado === 'activo')
  if (activo) periodoId.value = activo.id
  await loadCursos()
  await loadMatriculas()
})

watch(periodoId, async () => {
  cursoId.value = ''
  await loadCursos()
  await loadMatriculas()
})
watch(cursoId, loadMatriculas)
</script>

<template>
  <LteAppContent
    title="Matrículas"
    :breadcrumbs="[{ label: 'Inicio', href: '/dashboard' }, { label: 'Académico' }, { label: 'Matrículas' }]"
  >
    <LteAlert v-if="successMessage" theme="success" dismissible class="mb-3" @close="successMessage = null">{{ successMessage }}</LteAlert>

    <LteCard title="Matrículas" icon="bi-card-checklist" theme="primary" variant="outline">
      <div class="row g-3 mb-3">
        <div class="col-md-4">
          <LteSelect v-model="periodoId" label="Período">
            <option value="">Todos</option>
            <option v-for="p in catalogos.periodos" :key="p.id" :value="p.id">{{ p.anio }} — {{ p.nombre }}</option>
          </LteSelect>
        </div>
        <div class="col-md-4">
          <LteSelect v-model="cursoId" label="Curso">
            <option value="">Todos</option>
            <option v-for="c in cursos" :key="c.id" :value="c.id">{{ c.nombre_display }}</option>
          </LteSelect>
        </div>
        <div class="col-md-4 d-flex align-items-end">
          <LteButton theme="success" icon="bi-plus-lg" :disabled="!periodoId" @click="openCreate">Matricular alumno</LteButton>
        </div>
      </div>

      <table class="table table-hover">
        <thead><tr><th>Alumno</th><th>Curso</th><th>Período</th><th>Fecha</th><th>Estado</th></tr></thead>
        <tbody>
          <tr v-for="m in matriculas" :key="m.id">
            <td>{{ m.alumno?.persona?.nombre_completo }}</td>
            <td>{{ m.curso?.nombre_display }}</td>
            <td>{{ m.periodo_escolar?.anio }}</td>
            <td>{{ m.fecha_matricula }}</td>
            <td><span class="badge text-bg-primary">{{ m.estado_label }}</span></td>
          </tr>
        </tbody>
      </table>
    </LteCard>

    <LteModal v-model="showModal" title="Nueva matrícula">
      <LteAlert v-if="error" theme="danger" class="mb-3">{{ error }}</LteAlert>
      <div class="row g-3">
        <div class="col-12">
          <LteSelect v-model="form.alumno_id" label="Alumno">
            <option value="">Seleccione alumno</option>
            <option v-for="a in alumnos" :key="a.id" :value="a.id">{{ a.persona?.nombre_completo }} — {{ a.persona?.numero_documento_formateado }}</option>
          </LteSelect>
        </div>
        <div class="col-12">
          <LteSelect v-model="form.curso_id" label="Curso">
            <option v-for="c in cursos" :key="c.id" :value="c.id">{{ c.nombre_display }}</option>
          </LteSelect>
        </div>
        <div class="col-12">
          <LteInput v-model="form.fecha_matricula" type="date" label="Fecha matrícula" />
        </div>
      </div>
      <template #footer>
        <LteButton theme="secondary" @click="showModal = false">Cancelar</LteButton>
        <LteButton theme="primary" :loading="saving" @click="submit">Matricular</LteButton>
      </template>
    </LteModal>
  </LteAppContent>
</template>
