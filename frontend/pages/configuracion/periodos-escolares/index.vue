<script setup lang="ts">
import type {
  EstadoPeriodoEscolar,
  PeriodoEscolar,
  PeriodoEscolarPayload,
  SubperiodoInput,
  TipoPeriodoEscolar,
} from '~/types/periodo-escolar'

definePageMeta({
  layout: 'admin',
  middleware: 'auth',
})

const store = usePeriodosEscolaresStore()

const filters = reactive({
  anio: '' as number | '',
  tipo: '' as TipoPeriodoEscolar | '',
  estado: '' as EstadoPeriodoEscolar | '',
})

const showFormModal = ref(false)
const showDetailModal = ref(false)
const editingId = ref<number | null>(null)
const successMessage = ref<string | null>(null)

const form = reactive({
  nombre: '',
  anio: new Date().getFullYear(),
  tipo: 'anual' as TipoPeriodoEscolar,
  fecha_inicio: `${new Date().getFullYear()}-03-01`,
  fecha_termino: `${new Date().getFullYear()}-12-31`,
  estado: 'borrador' as EstadoPeriodoEscolar,
  descripcion: '',
  subperiodos: [] as SubperiodoInput[],
})

const tipoOptions = [
  { value: 'anual', label: 'Anual' },
  { value: 'semestral', label: 'Semestral' },
  { value: 'trimestral', label: 'Trimestral' },
]

const estadoOptions = [
  { value: 'borrador', label: 'Borrador' },
  { value: 'activo', label: 'Activo' },
  { value: 'cerrado', label: 'Cerrado' },
]

function formatDate(value: string) {
  const [y, m, d] = value.split('-')
  return `${d}/${m}/${y}`
}

function estadoBadgeTheme(estado: EstadoPeriodoEscolar) {
  if (estado === 'activo') return 'success'
  if (estado === 'cerrado') return 'danger'
  return 'secondary'
}

function splitDateRange(inicio: string, termino: string, partes: number): Array<{ inicio: string; termino: string }> {
  const start = new Date(`${inicio}T00:00:00`)
  const end = new Date(`${termino}T00:00:00`)
  const totalMs = end.getTime() - start.getTime()
  const chunk = Math.floor(totalMs / partes)
  const ranges: Array<{ inicio: string; termino: string }> = []

  for (let i = 0; i < partes; i++) {
    const chunkStart = new Date(start.getTime() + chunk * i)
    const chunkEnd = i === partes - 1
      ? end
      : new Date(start.getTime() + chunk * (i + 1) - 86400000)

    const toIso = (date: Date) => date.toISOString().slice(0, 10)
    ranges.push({ inicio: toIso(chunkStart), termino: toIso(chunkEnd) })
  }

  return ranges
}

function buildSubperiodos(tipo: TipoPeriodoEscolar, inicio: string, termino: string): SubperiodoInput[] {
  if (tipo === 'anual') return []

  const cantidad = tipo === 'semestral' ? 2 : 3
  const ranges = splitDateRange(inicio, termino, cantidad)
  const labels = tipo === 'semestral'
    ? ['Primer semestre', 'Segundo semestre']
    : ['Primer trimestre', 'Segundo trimestre', 'Tercer trimestre']

  return ranges.map((range, index) => ({
    nombre: labels[index],
    numero: index + 1,
    fecha_inicio: range.inicio,
    fecha_termino: range.termino,
  }))
}

function syncSubperiodos() {
  form.subperiodos = buildSubperiodos(form.tipo, form.fecha_inicio, form.fecha_termino)
}

function resetForm() {
  const year = new Date().getFullYear()
  form.nombre = String(year)
  form.anio = year
  form.tipo = 'anual'
  form.fecha_inicio = `${year}-03-01`
  form.fecha_termino = `${year}-12-31`
  form.estado = 'borrador'
  form.descripcion = ''
  form.subperiodos = []
  editingId.value = null
  store.fieldErrors = {}
  store.error = null
}

function openCreate() {
  resetForm()
  syncSubperiodos()
  showFormModal.value = true
}

async function openEdit(periodo: PeriodoEscolar) {
  editingId.value = periodo.id
  form.nombre = periodo.nombre
  form.anio = periodo.anio
  form.tipo = periodo.tipo
  form.fecha_inicio = periodo.fecha_inicio
  form.fecha_termino = periodo.fecha_termino
  form.estado = periodo.estado
  form.descripcion = periodo.descripcion ?? ''

  const detalle = await store.fetchPeriodo(periodo.id)
  form.subperiodos = (detalle.subperiodos ?? []).map((sub) => ({
    nombre: sub.nombre,
    numero: sub.numero_subperiodo ?? 1,
    fecha_inicio: sub.fecha_inicio,
    fecha_termino: sub.fecha_termino,
  }))
  if (form.subperiodos.length === 0 && form.tipo !== 'anual') {
    syncSubperiodos()
  }

  showFormModal.value = true
}

async function openDetail(periodo: PeriodoEscolar) {
  await store.fetchPeriodo(periodo.id)
  showDetailModal.value = true
}

async function loadPeriodos() {
  await store.fetchPeriodos({ ...filters })
}

async function submitForm() {
  const payload: PeriodoEscolarPayload = {
    nombre: form.nombre,
    anio: Number(form.anio),
    tipo: form.tipo,
    fecha_inicio: form.fecha_inicio,
    fecha_termino: form.fecha_termino,
    estado: form.estado,
    descripcion: form.descripcion || null,
    subperiodos: form.tipo === 'anual' ? [] : form.subperiodos,
  }

  try {
    if (editingId.value) {
      await store.updatePeriodo(editingId.value, payload)
      successMessage.value = 'Período actualizado correctamente.'
    } else {
      await store.createPeriodo(payload)
      successMessage.value = 'Período creado correctamente.'
    }
    showFormModal.value = false
    await loadPeriodos()
  } catch {
    // errores en store
  }
}

async function activar(periodo: PeriodoEscolar) {
  if (!confirm(`¿Activar el período ${periodo.nombre}? Se cerrará el período activo del mismo año.`)) return
  await store.activarPeriodo(periodo.id)
  successMessage.value = 'Período activado.'
  await loadPeriodos()
}

async function cerrar(periodo: PeriodoEscolar) {
  if (!confirm(`¿Cerrar el período ${periodo.nombre}? No podrá editarse.`)) return
  await store.cerrarPeriodo(periodo.id)
  successMessage.value = 'Período cerrado.'
  await loadPeriodos()
}

async function eliminar(periodo: PeriodoEscolar) {
  if (!confirm(`¿Eliminar el período ${periodo.nombre}?`)) return
  await store.deletePeriodo(periodo.id)
  successMessage.value = 'Período eliminado.'
  await loadPeriodos()
}

watch(() => form.tipo, () => {
  if (!showFormModal.value) return
  syncSubperiodos()
})

watch([() => form.fecha_inicio, () => form.fecha_termino], () => {
  if (!showFormModal.value || form.tipo === 'anual') return
  syncSubperiodos()
})

watch(() => form.anio, (value) => {
  form.nombre = String(value)
  if (form.fecha_inicio.startsWith(String(value - 1)) || form.fecha_inicio.includes('-03-')) {
    form.fecha_inicio = `${value}-03-01`
    form.fecha_termino = `${value}-12-31`
  }
})

onMounted(loadPeriodos)
</script>

<template>
  <LteAppContent
    title="Períodos escolares"
    :breadcrumbs="[
      { label: 'Inicio', href: '/dashboard' },
      { label: 'Configuración' },
      { label: 'Períodos escolares' },
    ]"
  >
    <LteAlert
      v-if="successMessage"
      theme="success"
      icon="bi-check-circle"
      dismissible
      class="mb-3"
      @close="successMessage = null"
    >
      {{ successMessage }}
    </LteAlert>

    <LteAlert
      v-if="store.error && !showFormModal"
      theme="danger"
      icon="bi-x-circle"
      dismissible
      class="mb-3"
      @close="store.error = null"
    >
      {{ store.error }}
    </LteAlert>

    <LteCard title="Listado de períodos" icon="bi-calendar-range" theme="primary" variant="outline">
      <div class="row g-3 mb-3">
        <div class="col-md-3">
          <LteInput v-model="filters.anio" type="number" label="Año" placeholder="Todos" />
        </div>
        <div class="col-md-3">
          <LteSelect v-model="filters.tipo" label="Tipo">
            <option value="">Todos</option>
            <option v-for="opt in tipoOptions" :key="opt.value" :value="opt.value">
              {{ opt.label }}
            </option>
          </LteSelect>
        </div>
        <div class="col-md-3">
          <LteSelect v-model="filters.estado" label="Estado">
            <option value="">Todos</option>
            <option v-for="opt in estadoOptions" :key="opt.value" :value="opt.value">
              {{ opt.label }}
            </option>
          </LteSelect>
        </div>
        <div class="col-md-3 d-flex align-items-end gap-2">
          <LteButton theme="primary" icon="bi-search" @click="loadPeriodos">
            Filtrar
          </LteButton>
          <LteButton theme="success" icon="bi-plus-lg" @click="openCreate">
            Nuevo período
          </LteButton>
        </div>
      </div>

      <div v-if="store.loading" class="text-center py-4 text-muted">
        <span class="spinner-border spinner-border-sm me-2" />
        Cargando períodos...
      </div>

      <div v-else class="table-responsive">
        <table class="table table-hover align-middle">
          <thead>
            <tr>
              <th>Año</th>
              <th>Nombre</th>
              <th>Tipo</th>
              <th>Inicio</th>
              <th>Término</th>
              <th>Estado</th>
              <th class="text-end">Acciones</th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="store.periodos.length === 0">
              <td colspan="7" class="text-center text-muted py-4">
                No hay períodos registrados.
              </td>
            </tr>
            <tr v-for="periodo in store.periodos" :key="periodo.id">
              <td>{{ periodo.anio }}</td>
              <td>{{ periodo.nombre }}</td>
              <td>{{ periodo.tipo_label }}</td>
              <td>{{ formatDate(periodo.fecha_inicio) }}</td>
              <td>{{ formatDate(periodo.fecha_termino) }}</td>
              <td>
                <span :class="['badge', `text-bg-${estadoBadgeTheme(periodo.estado)}`]">
                  {{ periodo.estado_label }}
                </span>
              </td>
              <td class="text-end">
                <LteButton size="sm" theme="info" icon="bi-eye" @click="openDetail(periodo)" />
                <LteButton
                  v-if="periodo.estado !== 'cerrado'"
                  size="sm"
                  theme="warning"
                  icon="bi-pencil"
                  class="ms-1"
                  @click="openEdit(periodo)"
                />
                <LteButton
                  v-if="periodo.estado === 'borrador'"
                  size="sm"
                  theme="success"
                  icon="bi-check2-circle"
                  class="ms-1"
                  @click="activar(periodo)"
                />
                <LteButton
                  v-if="periodo.estado === 'activo'"
                  size="sm"
                  theme="secondary"
                  icon="bi-lock"
                  class="ms-1"
                  @click="cerrar(periodo)"
                />
                <LteButton
                  v-if="periodo.estado === 'borrador'"
                  size="sm"
                  theme="danger"
                  icon="bi-trash"
                  class="ms-1"
                  @click="eliminar(periodo)"
                />
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </LteCard>

    <LteModal v-model="showFormModal" :title="editingId ? 'Editar período escolar' : 'Nuevo período escolar'" size="lg">
      <LteAlert v-if="store.error" theme="danger" icon="bi-x-circle" class="mb-3">
        {{ store.error }}
      </LteAlert>

      <div class="row g-3">
        <div class="col-md-4">
          <LteInput
            v-model="form.anio"
            type="number"
            label="Año"
            :invalid="!!store.fieldErrors.anio"
            :feedback="store.fieldErrors.anio"
          />
        </div>
        <div class="col-md-8">
          <LteInput
            v-model="form.nombre"
            label="Nombre del período"
            :invalid="!!store.fieldErrors.nombre"
            :feedback="store.fieldErrors.nombre"
          />
        </div>
        <div class="col-md-4">
          <LteSelect
            v-model="form.tipo"
            label="Tipo de período"
            :invalid="!!store.fieldErrors.tipo"
            :feedback="store.fieldErrors.tipo"
          >
            <option v-for="opt in tipoOptions" :key="opt.value" :value="opt.value">
              {{ opt.label }}
            </option>
          </LteSelect>
        </div>
        <div class="col-md-4">
          <LteInput
            v-model="form.fecha_inicio"
            type="date"
            label="Fecha inicio"
            :invalid="!!store.fieldErrors.fecha_inicio"
            :feedback="store.fieldErrors.fecha_inicio"
          />
        </div>
        <div class="col-md-4">
          <LteInput
            v-model="form.fecha_termino"
            type="date"
            label="Fecha término"
            :invalid="!!store.fieldErrors.fecha_termino"
            :feedback="store.fieldErrors.fecha_termino"
          />
        </div>
        <div class="col-md-4">
          <LteSelect v-model="form.estado" label="Estado">
            <option v-for="opt in estadoOptions.filter(o => o.value !== 'cerrado')" :key="opt.value" :value="opt.value">
              {{ opt.label }}
            </option>
          </LteSelect>
        </div>
        <div class="col-12">
          <LteInput v-model="form.descripcion" label="Descripción (opcional)" />
        </div>
      </div>

      <div v-if="form.tipo !== 'anual'" class="mt-4">
        <h6 class="mb-3">
          <i class="bi bi-diagram-3 me-1" />
          Subperíodos
        </h6>
        <div v-for="(sub, index) in form.subperiodos" :key="sub.numero" class="border rounded p-3 mb-3">
          <div class="row g-3">
            <div class="col-md-4">
              <LteInput v-model="sub.nombre" :label="`Subperíodo ${sub.numero}`" />
            </div>
            <div class="col-md-4">
              <LteInput
                v-model="sub.fecha_inicio"
                type="date"
                label="Inicio"
                :invalid="!!store.fieldErrors[`subperiodos.${index}.fecha_inicio`]"
                :feedback="store.fieldErrors[`subperiodos.${index}.fecha_inicio`]"
              />
            </div>
            <div class="col-md-4">
              <LteInput
                v-model="sub.fecha_termino"
                type="date"
                label="Término"
                :invalid="!!store.fieldErrors[`subperiodos.${index}.fecha_termino`]"
                :feedback="store.fieldErrors[`subperiodos.${index}.fecha_termino`]"
              />
            </div>
          </div>
        </div>
        <p v-if="store.fieldErrors.subperiodos" class="text-danger small mb-0">
          {{ store.fieldErrors.subperiodos }}
        </p>
      </div>

      <template #footer>
        <LteButton theme="secondary" @click="showFormModal = false">
          Cancelar
        </LteButton>
        <LteButton theme="primary" :loading="store.saving" @click="submitForm">
          {{ editingId ? 'Guardar cambios' : 'Crear período' }}
        </LteButton>
      </template>
    </LteModal>

    <LteModal v-model="showDetailModal" title="Detalle del período escolar" size="lg">
      <template v-if="store.periodoActual">
        <div class="row g-3 mb-3">
          <div class="col-md-6">
            <strong>Nombre:</strong> {{ store.periodoActual.nombre }}
          </div>
          <div class="col-md-3">
            <strong>Año:</strong> {{ store.periodoActual.anio }}
          </div>
          <div class="col-md-3">
            <strong>Tipo:</strong> {{ store.periodoActual.tipo_label }}
          </div>
          <div class="col-md-4">
            <strong>Inicio:</strong> {{ formatDate(store.periodoActual.fecha_inicio) }}
          </div>
          <div class="col-md-4">
            <strong>Término:</strong> {{ formatDate(store.periodoActual.fecha_termino) }}
          </div>
          <div class="col-md-4">
            <strong>Estado:</strong> {{ store.periodoActual.estado_label }}
          </div>
          <div v-if="store.periodoActual.descripcion" class="col-12">
            <strong>Descripción:</strong> {{ store.periodoActual.descripcion }}
          </div>
        </div>

        <div v-if="store.periodoActual.subperiodos?.length" class="mb-3">
          <h6>Estructura jerárquica</h6>
          <ul class="list-group">
            <li
              v-for="sub in store.periodoActual.subperiodos"
              :key="sub.id"
              class="list-group-item d-flex justify-content-between"
            >
              <span>{{ sub.nombre }}</span>
              <span class="text-muted">
                {{ formatDate(sub.fecha_inicio) }} → {{ formatDate(sub.fecha_termino) }}
              </span>
            </li>
          </ul>
        </div>

        <LteCard title="Auditoría" icon="bi-clock-history" variant="outline">
          <p class="mb-1">
            <strong>Creado por:</strong>
            {{ store.periodoActual.auditoria?.created_by?.name ?? '—' }}
          </p>
          <p class="mb-1">
            <strong>Modificado por:</strong>
            {{ store.periodoActual.auditoria?.updated_by?.name ?? '—' }}
          </p>
          <p class="mb-1">
            <strong>Creación:</strong>
            {{ store.periodoActual.auditoria?.created_at ?? '—' }}
          </p>
          <p class="mb-0">
            <strong>Cierre:</strong>
            {{ store.periodoActual.auditoria?.closed_at ?? '—' }}
          </p>
        </LteCard>
      </template>

      <template #footer>
        <LteButton theme="secondary" @click="showDetailModal = false">
          Cerrar
        </LteButton>
      </template>
    </LteModal>
  </LteAppContent>
</template>
