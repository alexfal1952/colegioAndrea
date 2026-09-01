<script setup lang="ts">
import type { Matricula } from '~/types/comunidad'
import type { ResumenAsistencia } from '~/types/asistencia'
import { formatFechaCorta, nombreMes } from '~/utils/asistencia'

definePageMeta({ layout: 'admin', middleware: 'auth' })

const route = useRoute()
const { api, extractErrorMessage } = useApi()
const {
  catalogos,
  periodoId,
  cursoId,
  cursos,
  periodoActivo,
  initContext,
  loadCursos,
  normalizeId,
} = useAsistenciaContext()

const matriculaId = ref<number | ''>('')
const mes = ref(new Date().getMonth() + 1)
const matriculas = ref<Matricula[]>([])
const resumen = ref<ResumenAsistencia | null>(null)
const loading = ref(false)
const error = ref<string | null>(null)

const anio = computed(() => periodoActivo.value?.anio ?? new Date().getFullYear())

async function loadMatriculas(resetMatricula = true) {
  if (resetMatricula) matriculaId.value = ''
  resumen.value = null
  const cid = normalizeId(cursoId.value)
  if (!cid) {
    matriculas.value = []
    return
  }
  const res = await api<{ data: Matricula[] }>(`/cursos/${cid}/alumnos`)
  matriculas.value = res.data
}

async function loadResumen() {
  const mid = normalizeId(matriculaId.value)
  if (!mid) {
    resumen.value = null
    return
  }
  loading.value = true
  error.value = null
  try {
    const res = await api<{ data: ResumenAsistencia }>(
      `/matriculas/${mid}/asistencias/resumen?anio=${anio.value}&mes=${mes.value}`
    )
    resumen.value = res.data
  } catch (e) {
    error.value = extractErrorMessage(e)
    resumen.value = null
  } finally {
    loading.value = false
  }
}

const alumnoSeleccionado = computed(() =>
  matriculas.value.find((m) => m.id === normalizeId(matriculaId.value))?.alumno?.persona?.nombre_completo
)

onMounted(async () => {
  await initContext()
  if (route.query.periodo) periodoId.value = normalizeId(route.query.periodo)
  await loadCursos()
  if (route.query.curso) cursoId.value = normalizeId(route.query.curso)
  await loadMatriculas(false)
  if (route.query.matricula) matriculaId.value = normalizeId(route.query.matricula)
  if (route.query.mes) mes.value = Number(route.query.mes)
  await loadResumen()
})

watch(cursoId, () => loadMatriculas())
watch([matriculaId, mes, periodoId], loadResumen)
</script>

<template>
  <LteAppContent
    title="Resumen por alumno"
    :breadcrumbs="[
      { label: 'Inicio', href: '/dashboard' },
      { label: 'Asistencia', href: '/asistencia' },
      { label: 'Por alumno' },
    ]"
  >
    <LteAlert v-if="error" theme="danger" dismissible class="mb-3" @close="error = null">{{ error }}</LteAlert>

    <LteCard title="Filtros" icon="bi-funnel" theme="primary" variant="outline" class="mb-3">
      <div class="row g-3">
        <div class="col-md-4">
          <LteSelect v-model="periodoId" label="Período">
            <option v-for="p in catalogos.periodos" :key="p.id" :value="p.id">{{ p.anio }} — {{ p.nombre }}</option>
          </LteSelect>
        </div>
        <div class="col-md-4">
          <LteSelect v-model="cursoId" label="Curso">
            <option value="">Seleccione</option>
            <option v-for="c in cursos" :key="c.id" :value="c.id">{{ c.nombre_display }}</option>
          </LteSelect>
        </div>
        <div class="col-md-4">
          <LteSelect v-model="matriculaId" label="Alumno" :disabled="!matriculas.length">
            <option value="">Seleccione alumno</option>
            <option v-for="m in matriculas" :key="m.id" :value="m.id">{{ m.alumno?.persona?.nombre_completo }}</option>
          </LteSelect>
        </div>
        <div class="col-md-3">
          <LteSelect v-model="mes" label="Mes">
            <option v-for="m in 12" :key="m" :value="m">{{ nombreMes(m) }}</option>
          </LteSelect>
        </div>
      </div>
    </LteCard>

    <div v-if="loading" class="text-center py-5 text-muted">Cargando resumen…</div>

    <template v-else-if="resumen && matriculaId">
      <div class="mb-3">
        <h5 class="mb-0">{{ alumnoSeleccionado }}</h5>
        <small class="text-muted">{{ nombreMes(mes) }} {{ anio }}</small>
      </div>

      <AsistenciaResumenKpis
        :dias-lectivos="resumen.dias_lectivos"
        :presentes="resumen.presentes"
        :ausentes="resumen.ausentes"
        :atrasos="resumen.atrasos"
        :porcentaje="resumen.porcentaje_asistencia"
      />

      <LteCard title="Detalle diario" icon="bi-list-ul" theme="primary" variant="outline">
        <table v-if="resumen.detalle?.length" class="table table-hover mb-0">
          <thead class="table-light">
            <tr><th>Fecha</th><th>Estado</th><th>Observación</th></tr>
          </thead>
          <tbody>
            <tr v-for="d in resumen.detalle" :key="d.fecha">
              <td>{{ formatFechaCorta(d.fecha) }}</td>
              <td><span class="badge text-bg-light text-dark border">{{ d.estado }}</span></td>
              <td class="text-muted">{{ d.observacion || '—' }}</td>
            </tr>
          </tbody>
        </table>
        <p v-else class="text-muted mb-0 py-3 text-center">Sin registros de asistencia en este mes.</p>
      </LteCard>
    </template>

    <div v-else-if="cursoId && !matriculaId" class="alert alert-light border text-center py-5">
      Selecciona un alumno para ver su resumen mensual.
    </div>
  </LteAppContent>
</template>
