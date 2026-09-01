<script setup lang="ts">
import { colorPorcentajeAsistencia, nombreMes } from '~/utils/asistencia'

definePageMeta({ layout: 'admin', middleware: 'auth' })

const { api, extractErrorMessage } = useApi()
const {
  catalogos,
  periodoId,
  cursoId,
  cursos,
  cursoActivo,
  periodoActivo,
  initContext,
  normalizeId,
} = useAsistenciaContext()

const mes = ref(new Date().getMonth() + 1)
const loading = ref(false)
const error = ref<string | null>(null)
const resumen = ref<{
  total_alumnos: number
  promedio_asistencia: number
  total_presentes: number
  total_ausentes: number
  total_atrasos: number
  alumnos: {
    alumno?: { persona?: { nombre_completo: string } }
    porcentaje_asistencia: number
    presentes: number
    ausentes: number
    atrasos: number
  }[]
} | null>(null)

const anio = computed(() => periodoActivo.value?.anio ?? new Date().getFullYear())

const alumnosOrdenados = computed(() =>
  [...(resumen.value?.alumnos ?? [])].sort((a, b) => a.porcentaje_asistencia - b.porcentaje_asistencia)
)

const alumnosEnRiesgo = computed(() =>
  alumnosOrdenados.value.filter((a) => a.porcentaje_asistencia < 85)
)

async function loadResumen() {
  const cid = normalizeId(cursoId.value)
  if (!cid) {
    resumen.value = null
    return
  }
  loading.value = true
  error.value = null
  try {
    const res = await api<{ data: typeof resumen.value }>(
      `/cursos/${cid}/asistencias/resumen?anio=${anio.value}&mes=${mes.value}`
    )
    resumen.value = res.data
  } catch (e) {
    error.value = extractErrorMessage(e)
    resumen.value = null
  } finally {
    loading.value = false
  }
}

onMounted(async () => {
  await initContext()
  await loadResumen()
})

watch([cursoId, mes, periodoId], loadResumen)
</script>

<template>
  <LteAppContent
    title="Resumen del curso"
    :breadcrumbs="[
      { label: 'Inicio', href: '/dashboard' },
      { label: 'Asistencia', href: '/asistencia' },
      { label: 'Resumen curso' },
    ]"
  >
    <LteAlert v-if="error" theme="danger" dismissible class="mb-3" @close="error = null">{{ error }}</LteAlert>

    <LteCard title="Filtros" icon="bi-funnel" theme="primary" variant="outline" class="mb-3">
      <div class="row g-3 align-items-end">
        <div class="col-md-4">
          <LteSelect v-model="periodoId" label="Período">
            <option v-for="p in catalogos.periodos" :key="p.id" :value="p.id">{{ p.anio }} — {{ p.nombre }}</option>
          </LteSelect>
        </div>
        <div class="col-md-4">
          <LteSelect v-model="cursoId" label="Curso">
            <option value="">Seleccione curso</option>
            <option v-for="c in cursos" :key="c.id" :value="c.id">{{ c.nombre_display }}</option>
          </LteSelect>
        </div>
        <div class="col-md-2">
          <LteSelect v-model="mes" label="Mes">
            <option v-for="m in 12" :key="m" :value="m">{{ nombreMes(m) }}</option>
          </LteSelect>
        </div>
        <div class="col-md-2">
          <NuxtLink
            v-if="cursoId"
            :to="`/asistencia/registrar?curso=${cursoId}`"
            class="btn btn-outline-primary w-100"
          >
            <i class="bi bi-clipboard-check" /> Registrar hoy
          </NuxtLink>
        </div>
      </div>
    </LteCard>

    <div v-if="loading" class="text-center py-5 text-muted">Calculando resumen…</div>

    <template v-else-if="resumen && cursoActivo">
      <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <div>
          <h5 class="mb-0">{{ cursoActivo.nombre_display }}</h5>
          <small class="text-muted">{{ nombreMes(mes) }} {{ anio }}</small>
        </div>
      </div>

      <AsistenciaResumenKpis
        :total-alumnos="resumen.total_alumnos"
        :promedio="resumen.promedio_asistencia"
        :presentes="resumen.total_presentes"
        :ausentes="resumen.total_ausentes"
        :atrasos="resumen.total_atrasos"
      />

      <LteAlert v-if="alumnosEnRiesgo.length" theme="warning" class="mb-3">
        <strong>{{ alumnosEnRiesgo.length }}</strong> alumno(s) con asistencia menor al 85% este mes.
      </LteAlert>

      <LteCard title="Detalle por alumno" icon="bi-table" theme="primary" variant="outline">
        <p class="small text-muted">Ordenado de menor a mayor asistencia para identificar casos prioritarios.</p>
        <table class="table table-hover mb-0">
          <thead class="table-light">
            <tr>
              <th>Alumno</th>
              <th class="text-center">%</th>
              <th class="text-center" title="Presentes">P</th>
              <th class="text-center" title="Ausentes">A</th>
              <th class="text-center" title="Atrasos">T</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(a, i) in alumnosOrdenados" :key="i">
              <td>{{ a.alumno?.persona?.nombre_completo }}</td>
              <td class="text-center">
                <span :class="`badge text-bg-${colorPorcentajeAsistencia(a.porcentaje_asistencia)}`">
                  {{ a.porcentaje_asistencia }}%
                </span>
              </td>
              <td class="text-center text-success">{{ a.presentes }}</td>
              <td class="text-center text-danger">{{ a.ausentes }}</td>
              <td class="text-center text-warning">{{ a.atrasos }}</td>
            </tr>
          </tbody>
        </table>
      </LteCard>
    </template>

    <div v-else-if="!cursoId" class="alert alert-light border text-center py-5">
      Selecciona un curso para ver el resumen mensual.
    </div>
  </LteAppContent>
</template>
