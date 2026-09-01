<script setup lang="ts">
import type {
  AsignacionDocente,
  Asignatura,
  Curso,
  CursoAsignatura,
  Matricula,
  Profesor,
  RolAsignacionDocente,
} from '~/types/comunidad'

definePageMeta({ layout: 'admin', middleware: 'auth' })

const route = useRoute()
const { api, extractErrorMessage } = useApi()

const cursoId = computed(() => Number(route.params.id))
const tab = ref<'resumen' | 'alumnos' | 'asignaturas' | 'profesores' | 'config'>('resumen')

const curso = ref<Curso | null>(null)
const asignaturasCurso = ref<CursoAsignatura[]>([])
const matriculas = ref<Matricula[]>([])
const profesoresData = ref<{ profesor_jefe?: Profesor | null; profesores: { profesor: Profesor; asignaturas: { asignatura: string; codigo: string; rol: string }[] }[] } | null>(null)
const catalogoAsignaturas = ref<Asignatura[]>([])
const profesores = ref<Profesor[]>([])

const loading = ref(true)
const saving = ref(false)
const error = ref<string | null>(null)
const successMessage = ref<string | null>(null)

const showAddAsignatura = ref(false)
const showGestionar = ref(false)
const gestionarItem = ref<CursoAsignatura | null>(null)

const formAsignatura = reactive({ asignatura_id: '' as number | '', horas_semanales: null as number | null })
const formDocente = reactive({
  profesor_id: '' as number | '',
  rol: 'titular' as RolAsignacionDocente,
})
const formJefe = reactive({ profesor_jefe_id: null as number | null })

async function loadCurso() {
  const res = await api<{ data: Curso }>(`/cursos/${cursoId.value}/detalle`)
  curso.value = res.data
  asignaturasCurso.value = res.data.asignaturas || []
  formJefe.profesor_jefe_id = res.data.profesor_jefe_id ?? null
}

async function loadAlumnos() {
  const res = await api<{ data: Matricula[] }>(`/cursos/${cursoId.value}/alumnos`)
  matriculas.value = res.data
}

async function loadProfesores() {
  const res = await api<{ data: typeof profesoresData.value }>(`/cursos/${cursoId.value}/profesores`)
  profesoresData.value = res.data
}

async function loadCatalogos() {
  const [asig, prof] = await Promise.all([
    api<{ data: Asignatura[] }>('/asignaturas-activas'),
    api<{ data: Profesor[] }>('/profesores'),
  ])
  catalogoAsignaturas.value = asig.data
  profesores.value = prof.data
}

async function loadAll() {
  loading.value = true
  error.value = null
  try {
    await Promise.all([loadCurso(), loadCatalogos()])
  } catch (e) {
    error.value = extractErrorMessage(e)
  } finally {
    loading.value = false
  }
}

async function agregarAsignatura() {
  saving.value = true
  error.value = null
  try {
    await api(`/cursos/${cursoId.value}/asignaturas`, {
      method: 'POST',
      body: { asignatura_id: formAsignatura.asignatura_id, horas_semanales: formAsignatura.horas_semanales },
    })
    successMessage.value = 'Asignatura agregada al curso.'
    showAddAsignatura.value = false
    formAsignatura.asignatura_id = ''
    formAsignatura.horas_semanales = null
    await loadCurso()
  } catch (e) {
    error.value = extractErrorMessage(e)
  } finally {
    saving.value = false
  }
}

async function quitarAsignatura(ca: CursoAsignatura) {
  if (!confirm(`¿Quitar ${ca.asignatura?.nombre} del curso?`)) return
  saving.value = true
  error.value = null
  try {
    await api(`/cursos/${cursoId.value}/asignaturas/${ca.id}`, { method: 'DELETE' })
    successMessage.value = 'Asignatura removida.'
    await loadCurso()
  } catch (e) {
    error.value = extractErrorMessage(e)
  } finally {
    saving.value = false
  }
}

function abrirGestionar(ca: CursoAsignatura) {
  gestionarItem.value = ca
  formDocente.profesor_id = ''
  formDocente.rol = 'titular'
  showGestionar.value = true
}

async function asignarDocente() {
  if (!gestionarItem.value) return
  saving.value = true
  error.value = null
  try {
    await api(`/curso-asignaturas/${gestionarItem.value.id}/docentes`, {
      method: 'POST',
      body: formDocente,
    })
    successMessage.value = 'Profesor asignado.'
    showGestionar.value = false
    await loadCurso()
  } catch (e) {
    error.value = extractErrorMessage(e)
  } finally {
    saving.value = false
  }
}

async function finalizarAsignacion(doc: AsignacionDocente) {
  saving.value = true
  try {
    await api(`/asignaciones-docente/${doc.id}`, {
      method: 'PUT',
      body: { estado: 'finalizada' },
    })
    successMessage.value = 'Asignación finalizada.'
    await loadCurso()
  } catch (e) {
    error.value = extractErrorMessage(e)
  } finally {
    saving.value = false
  }
}

async function guardarJefe() {
  saving.value = true
  error.value = null
  try {
    await api(`/cursos/${cursoId.value}/profesor-jefe`, {
      method: 'PATCH',
      body: formJefe,
    })
    successMessage.value = 'Profesor jefe actualizado.'
    await loadCurso()
  } catch (e) {
    error.value = extractErrorMessage(e)
  } finally {
    saving.value = false
  }
}

const asignaturasDisponibles = computed(() => {
  const ids = new Set(asignaturasCurso.value.map((ca) => ca.asignatura_id))
  return catalogoAsignaturas.value.filter((a) => !ids.has(a.id))
})

watch(tab, async (t) => {
  if (t === 'alumnos' && !matriculas.value.length) await loadAlumnos()
  if (t === 'profesores' && !profesoresData.value) await loadProfesores()
})

onMounted(loadAll)
</script>

<template>
  <LteAppContent
    :title="curso?.nombre_display || 'Detalle del curso'"
    :breadcrumbs="[
      { label: 'Inicio', href: '/dashboard' },
      { label: 'Académico' },
      { label: 'Cursos', href: '/academico/cursos' },
      { label: curso?.nombre_display || '…' },
    ]"
  >
    <LteAlert v-if="successMessage" theme="success" dismissible class="mb-3" @close="successMessage = null">{{ successMessage }}</LteAlert>
    <LteAlert v-if="error" theme="danger" dismissible class="mb-3" @close="error = null">{{ error }}</LteAlert>

    <div v-if="loading" class="text-center py-5 text-muted">Cargando…</div>

    <template v-else-if="curso">
      <div class="mb-3">
        <NuxtLink to="/academico/cursos" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left" /> Volver a cursos</NuxtLink>
      </div>

      <ul class="nav nav-tabs mb-3">
        <li class="nav-item"><button class="nav-link" :class="{ active: tab === 'resumen' }" @click="tab = 'resumen'">Resumen</button></li>
        <li class="nav-item"><button class="nav-link" :class="{ active: tab === 'alumnos' }" @click="tab = 'alumnos'">Alumnos</button></li>
        <li class="nav-item"><button class="nav-link" :class="{ active: tab === 'asignaturas' }" @click="tab = 'asignaturas'">Asignaturas</button></li>
        <li class="nav-item"><button class="nav-link" :class="{ active: tab === 'profesores' }" @click="tab = 'profesores'">Profesores</button></li>
        <li class="nav-item"><button class="nav-link" :class="{ active: tab === 'config' }" @click="tab = 'config'">Configuración</button></li>
      </ul>

      <!-- Resumen -->
      <LteCard v-if="tab === 'resumen'" title="Resumen" icon="bi-info-circle" theme="primary" variant="outline">
        <div class="row g-3">
          <div class="col-md-3"><strong>Período:</strong> {{ curso.periodo_escolar?.anio }} — {{ curso.periodo_escolar?.nombre }}</div>
          <div class="col-md-3"><strong>Nivel:</strong> {{ curso.nivel?.nombre }}</div>
          <div class="col-md-2"><strong>Paralelo:</strong> {{ curso.paralelo }}</div>
          <div class="col-md-2"><strong>Jornada:</strong> {{ curso.jornada_label || '—' }}</div>
          <div class="col-md-2"><strong>Estado:</strong> <span class="badge text-bg-primary">{{ curso.estado_label }}</span></div>
          <div class="col-md-4"><strong>Profesor jefe:</strong> {{ curso.profesor_jefe?.persona?.nombre_completo || 'Sin asignar' }}</div>
          <div class="col-md-2"><strong>Cupo:</strong> {{ curso.cupo_maximo || '—' }}</div>
          <div class="col-md-3"><strong>Asignaturas:</strong> {{ asignaturasCurso.length }}</div>
        </div>
      </LteCard>

      <!-- Alumnos -->
      <LteCard v-if="tab === 'alumnos'" title="Alumnos matriculados" icon="bi-mortarboard" theme="primary" variant="outline">
        <table class="table table-hover">
          <thead><tr><th>Documento</th><th>Nombre</th><th>N° matrícula</th><th>Estado</th></tr></thead>
          <tbody>
            <tr v-if="!matriculas.length"><td colspan="4" class="text-muted text-center">Sin alumnos matriculados</td></tr>
            <tr v-for="m in matriculas" :key="m.id">
              <td>{{ m.alumno?.persona?.numero_documento_formateado }}</td>
              <td>{{ m.alumno?.persona?.nombre_completo }}</td>
              <td>{{ m.numero_matricula || '—' }}</td>
              <td><span class="badge text-bg-success">{{ m.estado_label }}</span></td>
            </tr>
          </tbody>
        </table>
      </LteCard>

      <!-- Asignaturas -->
      <LteCard v-if="tab === 'asignaturas'" title="Asignaturas del curso" icon="bi-book" theme="primary" variant="outline">
        <div class="mb-3">
          <LteButton theme="success" icon="bi-plus-lg" :disabled="!asignaturasDisponibles.length" @click="showAddAsignatura = true">Agregar asignatura</LteButton>
        </div>
        <table class="table table-hover">
          <thead><tr><th>Código</th><th>Asignatura</th><th>Horas/sem</th><th>Profesores</th><th></th></tr></thead>
          <tbody>
            <tr v-if="!asignaturasCurso.length"><td colspan="5" class="text-muted text-center">Sin asignaturas en este curso</td></tr>
            <tr v-for="ca in asignaturasCurso" :key="ca.id">
              <td>{{ ca.asignatura?.codigo }}</td>
              <td>{{ ca.asignatura?.nombre }}</td>
              <td>{{ ca.horas_semanales || '—' }}</td>
              <td>{{ ca.profesores_label || 'Sin asignar' }}</td>
              <td class="text-end text-nowrap">
                <LteButton size="sm" theme="outline-primary" class="me-1" @click="abrirGestionar(ca)">Gestionar</LteButton>
                <LteButton size="sm" theme="outline-danger" @click="quitarAsignatura(ca)">Quitar</LteButton>
              </td>
            </tr>
          </tbody>
        </table>
      </LteCard>

      <!-- Profesores -->
      <LteCard v-if="tab === 'profesores'" title="Profesores del curso" icon="bi-person-workspace" theme="primary" variant="outline">
        <div v-if="profesoresData" class="mb-4">
          <h6>Profesor jefe</h6>
          <p>{{ profesoresData.profesor_jefe?.persona?.nombre_completo || 'Sin asignar' }}</p>
        </div>
        <table class="table table-hover">
          <thead><tr><th>Profesor</th><th>Asignaturas</th></tr></thead>
          <tbody>
            <tr v-if="!profesoresData?.profesores.length"><td colspan="2" class="text-muted text-center">Sin profesores asignados a asignaturas</td></tr>
            <tr v-for="(item, i) in profesoresData?.profesores" :key="i">
              <td>{{ item.profesor?.persona?.nombre_completo }}</td>
              <td>
                <span v-for="(a, j) in item.asignaturas" :key="j" class="badge text-bg-light text-dark me-1">
                  {{ a.codigo }} ({{ a.rol }})
                </span>
              </td>
            </tr>
          </tbody>
        </table>
      </LteCard>

      <!-- Configuración -->
      <LteCard v-if="tab === 'config'" title="Configuración" icon="bi-gear" theme="primary" variant="outline">
        <div class="row g-3 align-items-end">
          <div class="col-md-6">
            <LteSelect v-model="formJefe.profesor_jefe_id" label="Profesor jefe">
              <option :value="null">Sin asignar</option>
              <option v-for="p in profesores" :key="p.id" :value="p.id">{{ p.persona?.nombre_completo }}</option>
            </LteSelect>
          </div>
          <div class="col-md-3">
            <LteButton theme="primary" :loading="saving" @click="guardarJefe">Guardar</LteButton>
          </div>
        </div>
      </LteCard>
    </template>

    <!-- Modal agregar asignatura -->
    <LteModal v-model="showAddAsignatura" title="Agregar asignatura al curso">
      <div class="row g-3">
        <div class="col-12">
          <LteSelect v-model="formAsignatura.asignatura_id" label="Asignatura">
            <option value="">Seleccione</option>
            <option v-for="a in asignaturasDisponibles" :key="a.id" :value="a.id">{{ a.codigo }} — {{ a.nombre }}</option>
          </LteSelect>
        </div>
        <div class="col-md-4"><LteInput v-model="formAsignatura.horas_semanales" type="number" label="Horas semanales" /></div>
      </div>
      <template #footer>
        <LteButton theme="secondary" @click="showAddAsignatura = false">Cancelar</LteButton>
        <LteButton theme="primary" :loading="saving" :disabled="!formAsignatura.asignatura_id" @click="agregarAsignatura">Agregar</LteButton>
      </template>
    </LteModal>

    <!-- Modal gestionar docentes -->
    <LteModal v-model="showGestionar" :title="`Gestionar: ${gestionarItem?.asignatura?.nombre}`" size="lg">
      <h6 class="mb-3">Docentes asignados</h6>
      <table class="table table-sm mb-4">
        <thead><tr><th>Profesor</th><th>Rol</th><th>Estado</th><th></th></tr></thead>
        <tbody>
          <tr v-if="!gestionarItem?.docentes?.length"><td colspan="4" class="text-muted">Sin docentes</td></tr>
          <tr v-for="d in gestionarItem?.docentes" :key="d.id">
            <td>{{ d.profesor?.persona?.nombre_completo }}</td>
            <td>{{ d.rol_label }}</td>
            <td>{{ d.estado_label }}</td>
            <td>
              <LteButton v-if="d.estado === 'activa'" size="sm" theme="outline-warning" @click="finalizarAsignacion(d)">Finalizar</LteButton>
            </td>
          </tr>
        </tbody>
      </table>
      <h6>Asignar profesor</h6>
      <div class="row g-3">
        <div class="col-md-6">
          <LteSelect v-model="formDocente.profesor_id" label="Profesor">
            <option value="">Seleccione</option>
            <option v-for="p in profesores" :key="p.id" :value="p.id">{{ p.persona?.nombre_completo }}</option>
          </LteSelect>
        </div>
        <div class="col-md-6">
          <LteSelect v-model="formDocente.rol" label="Rol">
            <option value="titular">Titular</option>
            <option value="co-docente">Co-docente</option>
            <option value="apoyo">Apoyo</option>
            <option value="reemplazante">Reemplazante</option>
          </LteSelect>
        </div>
      </div>
      <template #footer>
        <LteButton theme="secondary" @click="showGestionar = false">Cerrar</LteButton>
        <LteButton theme="primary" :loading="saving" :disabled="!formDocente.profesor_id" @click="asignarDocente">Asignar</LteButton>
      </template>
    </LteModal>
  </LteAppContent>
</template>
