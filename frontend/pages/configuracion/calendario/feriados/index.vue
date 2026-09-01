<script setup lang="ts">
import type { Feriado } from '~/types/asistencia'

definePageMeta({ layout: 'admin', middleware: 'auth' })

const { api, extractErrorMessage } = useApi()
const catalogos = useCatalogosStore()

const feriados = ref<Feriado[]>([])
const saving = ref(false)
const syncing = ref(false)
const error = ref<string | null>(null)
const showModal = ref(false)
const editing = ref<Feriado | null>(null)
const successMessage = ref<string | null>(null)
const periodoId = ref<number | ''>('')

const form = reactive({
  nombre: '',
  tipo: 'nacional',
  recurrente: false,
  fecha: '',
  mes: null as number | null,
  dia: null as number | null,
  descripcion: '',
})

async function load() {
  const q = periodoId.value ? `?periodo_escolar_id=${periodoId.value}` : ''
  const res = await api<{ data: Feriado[] }>(`/feriados${q}`)
  feriados.value = res.data
}

function openCreate() {
  editing.value = null
  Object.assign(form, { nombre: '', tipo: 'nacional', recurrente: false, fecha: '', mes: null, dia: null, descripcion: '' })
  showModal.value = true
}

function openEdit(f: Feriado) {
  editing.value = f
  Object.assign(form, {
    nombre: f.nombre,
    tipo: f.tipo,
    recurrente: f.recurrente,
    fecha: f.fecha || '',
    mes: f.mes,
    dia: f.dia,
    descripcion: f.descripcion || '',
  })
  showModal.value = true
}

async function submit() {
  saving.value = true
  error.value = null
  try {
    const body = { ...form, periodo_escolar_id: periodoId.value || null }
    if (editing.value) {
      await api(`/feriados/${editing.value.id}`, { method: 'PUT', body })
      successMessage.value = 'Feriado actualizado.'
    } else {
      await api('/feriados', { method: 'POST', body })
      successMessage.value = 'Feriado creado.'
    }
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

const anioSync = computed(() => {
  const p = catalogos.periodos.find((x) => x.id === periodoId.value)
  return p?.anio ?? new Date().getFullYear()
})

async function sincronizarBoostr() {
  syncing.value = true
  error.value = null
  try {
    const res = await api<{ message: string; data: { creados: number; actualizados: number; total: number } }>(
      '/feriados/sincronizar-boostr',
      {
        method: 'POST',
        body: {
          anio: anioSync.value,
          periodo_escolar_id: periodoId.value || null,
        },
      }
    )
    successMessage.value = res.message
    await load()
  } catch (e) {
    error.value = extractErrorMessage(e)
  } finally {
    syncing.value = false
  }
}
</script>

<template>
  <LteAppContent title="Feriados" :breadcrumbs="[{ label: 'Inicio', href: '/dashboard' }, { label: 'Calendario' }, { label: 'Feriados' }]">
    <LteAlert v-if="successMessage" theme="success" dismissible class="mb-3" @close="successMessage = null">{{ successMessage }}</LteAlert>
    <LteAlert v-if="error" theme="danger" dismissible class="mb-3" @close="error = null">{{ error }}</LteAlert>
    <LteCard title="Mantenedor de feriados" icon="bi-calendar-x" theme="primary" variant="outline">
      <div class="row g-3 mb-3">
        <div class="col-md-4">
          <LteSelect v-model="periodoId" label="Período (filtro)">
            <option value="">Todos</option>
            <option v-for="p in catalogos.periodos" :key="p.id" :value="p.id">{{ p.anio }} — {{ p.nombre }}</option>
          </LteSelect>
        </div>
        <div class="col-md-8 d-flex align-items-end gap-2 flex-wrap">
          <LteButton theme="outline-primary" icon="bi-cloud-download" :loading="syncing" @click="sincronizarBoostr">
            Sincronizar Chile {{ anioSync }}
          </LteButton>
          <LteButton theme="success" icon="bi-plus-lg" @click="openCreate">Nuevo feriado</LteButton>
        </div>
      </div>
      <p class="text-muted small mb-3">
        Importa feriados legales desde <a href="https://boostr.cl" target="_blank" rel="noopener">Boostr.cl</a>
        (API pública). Crea o actualiza feriados del año seleccionado.
      </p>
      <table class="table table-hover">
        <thead><tr><th>Nombre</th><th>Fecha</th><th>Tipo</th><th>Estado</th><th></th></tr></thead>
        <tbody>
          <tr v-for="f in feriados" :key="f.id">
            <td>{{ f.nombre }}</td>
            <td>{{ f.fecha_display }}</td>
            <td>{{ f.tipo_label }}</td>
            <td><span class="badge text-bg-success">{{ f.estado_label }}</span></td>
            <td class="text-end"><LteButton size="sm" theme="outline-primary" @click="openEdit(f)">Editar</LteButton></td>
          </tr>
        </tbody>
      </table>
    </LteCard>
    <LteModal v-model="showModal" :title="editing ? 'Editar feriado' : 'Nuevo feriado'">
      <LteAlert v-if="error" theme="danger" class="mb-3">{{ error }}</LteAlert>
      <div class="row g-3">
        <div class="col-12"><LteInput v-model="form.nombre" label="Nombre" /></div>
        <div class="col-md-6">
          <LteSelect v-model="form.tipo" label="Tipo">
            <option value="nacional">Nacional</option><option value="regional">Regional</option>
            <option value="comunal">Comunal</option><option value="escolar">Escolar</option><option value="otro">Otro</option>
          </LteSelect>
        </div>
        <div class="col-md-6 form-check mt-4">
          <input id="rec" v-model="form.recurrente" type="checkbox" class="form-check-input">
          <label for="rec" class="form-check-label">Recurrente (anual)</label>
        </div>
        <div v-if="form.recurrente" class="col-md-3"><LteInput v-model="form.dia" type="number" label="Día" /></div>
        <div v-if="form.recurrente" class="col-md-3"><LteInput v-model="form.mes" type="number" label="Mes" /></div>
        <div v-else class="col-md-6"><LteInput v-model="form.fecha" type="date" label="Fecha" /></div>
        <div class="col-12"><LteInput v-model="form.descripcion" label="Descripción" /></div>
      </div>
      <template #footer>
        <LteButton theme="secondary" @click="showModal = false">Cancelar</LteButton>
        <LteButton theme="primary" :loading="saving" @click="submit">Guardar</LteButton>
      </template>
    </LteModal>
  </LteAppContent>
</template>
