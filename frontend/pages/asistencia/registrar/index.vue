<script setup lang="ts">
import type { AlumnoAsistenciaFila, EstadoAsistencia, ValidacionFecha } from '~/types/asistencia'
import { addDays } from '~/utils/asistencia'

definePageMeta({ layout: 'admin', middleware: 'auth' })

const route = useRoute()
const { api, extractErrorMessage } = useApi()
const {
  cursoId,
  cursoActivo,
  initContext,
  normalizeId,
} = useAsistenciaContext()

const fecha = ref(new Date().toISOString().slice(0, 10))
const estados = ref<EstadoAsistencia[]>([])
const filas = ref<AlumnoAsistenciaFila[]>([])
const validacion = ref<ValidacionFecha | null>(null)
const loading = ref(false)
const saving = ref(false)
const error = ref<string | null>(null)
const successMessage = ref<string | null>(null)
const busqueda = ref('')

const registros = reactive<Record<number, number>>({})
const guardados = reactive<Record<number, boolean>>({})

const filasFiltradas = computed(() => {
  const q = busqueda.value.trim().toLowerCase()
  if (!q) return filas.value
  return filas.value.filter((f) =>
    f.matricula.alumno?.persona?.nombre_completo?.toLowerCase().includes(q)
  )
})

const conteo = computed(() => {
  const counts = { presentes: 0, ausentes: 0, atrasos: 0, otros: 0 }
  for (const f of filas.value) {
    const est = estados.value.find((e) => e.id === registros[f.matricula.id])
    if (!est) continue
    if (est.cuenta_como_ausencia) counts.ausentes++
    else if (est.cuenta_como_atraso) counts.atrasos++
    else if (est.cuenta_como_presente) counts.presentes++
    else counts.otros++
  }
  return counts
})

const puedeGuardar = computed(() =>
  validacion.value?.permite_registrar_asistencia && filas.value.length > 0
)

async function loadEstados() {
  const res = await api<{ data: EstadoAsistencia[] }>('/estados-asistencia?activos=1')
  estados.value = res.data
}

async function loadAsistencia() {
  const cid = normalizeId(cursoId.value)
  if (!cid || !fecha.value) {
    filas.value = []
    validacion.value = null
    return
  }
  loading.value = true
  error.value = null
  try {
    const res = await api<{ data: { validacion: ValidacionFecha; alumnos: AlumnoAsistenciaFila[] } }>(
      `/cursos/${cid}/asistencias?fecha=${fecha.value}`
    )
    validacion.value = res.data.validacion
    filas.value = res.data.alumnos
    const presenteId = estados.value.find((e) => e.codigo === 'presente')?.id
    for (const f of filas.value) {
      const existente = f.asistencia?.estado_asistencia?.id ?? f.estado_asistencia?.id
      registros[f.matricula.id] = existente ?? presenteId ?? estados.value[0]?.id ?? 0
      guardados[f.matricula.id] = !!f.asistencia
    }
  } catch (e) {
    error.value = extractErrorMessage(e)
  } finally {
    loading.value = false
  }
}

function marcarTodosPresentes() {
  const presente = estados.value.find((e) => e.codigo === 'presente')
  if (!presente) return
  for (const f of filas.value) registros[f.matricula.id] = presente.id
}

function cambiarFecha(delta: number) {
  fecha.value = addDays(fecha.value, delta)
}

async function guardar() {
  const cid = normalizeId(cursoId.value)
  if (!puedeGuardar.value || !cid) return
  saving.value = true
  error.value = null
  try {
    await api(`/cursos/${cid}/asistencias`, {
      method: 'POST',
      body: {
        fecha: fecha.value,
        registros: filas.value.map((f) => ({
          matricula_id: f.matricula.id,
          estado_asistencia_id: registros[f.matricula.id],
        })),
      },
    })
    successMessage.value = `Asistencia guardada para ${filas.value.length} alumnos.`
    await loadAsistencia()
  } catch (e) {
    error.value = extractErrorMessage(e)
  } finally {
    saving.value = false
  }
}

onMounted(async () => {
  await initContext()
  if (route.query.curso) cursoId.value = normalizeId(route.query.curso)
  if (route.query.fecha && typeof route.query.fecha === 'string') fecha.value = route.query.fecha
  await loadEstados()
  await loadAsistencia()
})

watch([cursoId, fecha], loadAsistencia)
</script>

<template>
  <LteAppContent
    title="Registrar asistencia"
    :breadcrumbs="[
      { label: 'Inicio', href: '/dashboard' },
      { label: 'Asistencia', href: '/asistencia' },
      { label: 'Registrar' },
    ]"
  >
    <LteAlert v-if="successMessage" theme="success" dismissible class="mb-3" @close="successMessage = null">
      {{ successMessage }}
    </LteAlert>
    <LteAlert v-if="error" theme="danger" dismissible class="mb-3" @close="error = null">{{ error }}</LteAlert>

    <LteCard title="1. Selecciona curso y fecha" icon="bi-funnel" theme="primary" variant="outline" class="mb-3">
      <p class="text-muted small mb-3">
        Elige el <strong>curso</strong> (ej. 5° Básico A) y la <strong>fecha</strong> del día a registrar.
      </p>
      <AsistenciaContextBar v-model:fecha="fecha" show-fecha show-curso />
      <div class="d-flex flex-wrap gap-2 mt-3 align-items-center">
        <LteButton size="sm" theme="outline-secondary" icon="bi-chevron-left" @click="cambiarFecha(-1)">Día anterior</LteButton>
        <LteButton size="sm" theme="outline-secondary" icon="bi-chevron-right" @click="cambiarFecha(1)">Día siguiente</LteButton>
        <NuxtLink to="/configuracion/calendario" class="btn btn-sm btn-outline-info">
          <i class="bi bi-calendar3" /> Ver calendario
        </NuxtLink>
      </div>
      <AsistenciaDiaBanner :validacion="validacion" :fecha="fecha" class="mt-3" />
    </LteCard>

    <LteCard v-if="cursoId" title="2. Marca asistencia" icon="bi-people" theme="primary" variant="outline" class="mb-3">
      <div class="row g-3 mb-3 align-items-end">
        <div class="col-md-5">
          <LteInput v-model="busqueda" label="Buscar alumno" placeholder="Nombre del alumno…" />
        </div>
        <div class="col-md-7 d-flex flex-wrap gap-2 justify-content-md-end">
          <LteButton theme="outline-success" size="sm" :disabled="!filas.length || !puedeGuardar" @click="marcarTodosPresentes">
            <i class="bi bi-check-all" /> Todos presentes
          </LteButton>
        </div>
      </div>

      <div v-if="filas.length" class="d-flex flex-wrap gap-3 mb-3 small">
        <span class="badge text-bg-success">{{ conteo.presentes }} presentes</span>
        <span class="badge text-bg-danger">{{ conteo.ausentes }} ausentes</span>
        <span class="badge text-bg-warning text-dark">{{ conteo.atrasos }} atrasos</span>
        <span v-if="conteo.otros" class="badge text-bg-secondary">{{ conteo.otros }} otros</span>
      </div>

      <div v-if="loading" class="text-center py-5 text-muted">
        <div class="spinner-border spinner-border-sm me-2" /> Cargando alumnos…
      </div>

      <div v-else-if="!filas.length" class="text-center py-5 text-muted">
        <i class="bi bi-inbox fs-1 d-block mb-2" />
        No hay alumnos matriculados activos en este curso.
      </div>

      <div v-else class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th style="width: 3rem">N°</th>
              <th>Alumno</th>
              <th>Estado</th>
              <th style="width: 6rem" class="text-center">Guardado</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(f, i) in filasFiltradas" :key="f.matricula.id">
              <td class="text-muted">{{ i + 1 }}</td>
              <td>
                <div class="fw-semibold">{{ f.matricula.alumno?.persona?.nombre_completo }}</div>
              </td>
              <td>
                <AsistenciaEstadoChips
                  :estados="estados"
                  :model-value="registros[f.matricula.id]"
                  @update:model-value="registros[f.matricula.id] = $event"
                />
              </td>
              <td class="text-center">
                <span v-if="guardados[f.matricula.id]" class="badge text-bg-success" title="Ya registrado">
                  <i class="bi bi-check-lg" />
                </span>
                <span v-else class="badge text-bg-light text-muted">Nuevo</span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </LteCard>

    <div v-else class="alert alert-light border text-center py-5">
      <i class="bi bi-arrow-up-circle fs-2 d-block mb-2 text-primary" />
      Elige un <strong>curso</strong> en el selector de arriba (por ejemplo <strong>5° Básico A</strong>).
    </div>

    <div v-if="puedeGuardar && filas.length" class="asistencia-sticky-bar">
      <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
        <span class="small text-muted">
          {{ cursoActivo?.nombre_display }} · {{ filas.length }} alumnos
        </span>
        <LteButton theme="primary" size="lg" icon="bi-save" :loading="saving" @click="guardar">
          Guardar asistencia
        </LteButton>
      </div>
    </div>
  </LteAppContent>
</template>

<style scoped>
.asistencia-sticky-bar {
  position: sticky;
  bottom: 0;
  z-index: 100;
  background: var(--bs-body-bg);
  border-top: 1px solid var(--bs-border-color);
  padding: 1rem;
  margin: 0 -0.5rem;
  box-shadow: 0 -4px 12px rgb(0 0 0 / 8%);
}
</style>
