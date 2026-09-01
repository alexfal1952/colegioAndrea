<script setup lang="ts">
import type { Persona, PersonaPayload, TipoDocumento } from '~/types/comunidad'

definePageMeta({ layout: 'admin', middleware: 'auth' })

const { api, extractErrorMessage, extractValidationErrors } = useApi()
const catalogos = useCatalogosStore()

const personas = ref<Persona[]>([])
const loading = ref(false)
const saving = ref(false)
const error = ref<string | null>(null)
const fieldErrors = ref<Record<string, string>>({})
const showModal = ref(false)
const editingId = ref<number | null>(null)
const successMessage = ref<string | null>(null)
const search = ref('')

const tiposDocumento: { value: TipoDocumento; label: string }[] = [
  { value: 'rut', label: 'RUT' },
  { value: 'pasaporte', label: 'Pasaporte' },
  { value: 'dni', label: 'DNI' },
  { value: 'otro', label: 'Otro' },
]

const form = reactive<PersonaPayload>({
  tipo_documento: 'rut',
  numero_documento: '',
  pais_documento: 'CL',
  nombres: '',
  apellido_paterno: '',
  apellido_materno: '',
  email: '',
  telefono: '',
})

const requierePais = computed(() => form.tipo_documento !== 'rut')

async function load() {
  loading.value = true
  error.value = null
  try {
    const q = search.value ? `?q=${encodeURIComponent(search.value)}` : ''
    const res = await api<{ data: Persona[] }>(`/personas${q}`)
    personas.value = res.data
  } catch (e) {
    error.value = extractErrorMessage(e)
  } finally {
    loading.value = false
  }
}

function resetForm() {
  Object.assign(form, {
    tipo_documento: 'rut' as TipoDocumento,
    numero_documento: '',
    pais_documento: 'CL',
    nombres: '',
    apellido_paterno: '',
    apellido_materno: '',
    email: '',
    telefono: '',
  })
  editingId.value = null
  fieldErrors.value = {}
}

function openCreate() {
  resetForm()
  showModal.value = true
}

function openEdit(p: Persona) {
  editingId.value = p.id
  Object.assign(form, {
    tipo_documento: p.tipo_documento,
    numero_documento: p.numero_documento_formateado || p.numero_documento,
    pais_documento: p.pais_documento || 'CL',
    nombres: p.nombres,
    apellido_paterno: p.apellido_paterno,
    apellido_materno: p.apellido_materno || '',
    email: p.email || '',
    telefono: p.telefono || '',
  })
  showModal.value = true
}

async function submit() {
  saving.value = true
  error.value = null
  fieldErrors.value = {}
  try {
    const payload = { ...form, pais_documento: requierePais.value ? form.pais_documento : 'CL' }
    if (editingId.value) {
      await api(`/personas/${editingId.value}`, { method: 'PUT', body: payload })
      successMessage.value = 'Persona actualizada.'
    } else {
      await api('/personas', { method: 'POST', body: payload })
      successMessage.value = 'Persona registrada.'
    }
    showModal.value = false
    await load()
  } catch (e) {
    fieldErrors.value = extractValidationErrors(e)
    error.value = extractErrorMessage(e)
  } finally {
    saving.value = false
  }
}

watch(() => form.tipo_documento, (tipo) => {
  if (tipo === 'rut') form.pais_documento = 'CL'
})

onMounted(async () => {
  await catalogos.load()
  await load()
})
</script>

<template>
  <LteAppContent
    title="Personas"
    :breadcrumbs="[{ label: 'Inicio', href: '/dashboard' }, { label: 'Comunidad' }, { label: 'Personas' }]"
  >
    <LteAlert v-if="successMessage" theme="success" dismissible class="mb-3" @close="successMessage = null">
      {{ successMessage }}
    </LteAlert>
    <LteAlert v-if="error && !showModal" theme="danger" dismissible class="mb-3" @close="error = null">
      {{ error }}
    </LteAlert>

    <LteCard title="Registro de personas" icon="bi-people" theme="primary" variant="outline">
      <div class="row g-3 mb-3">
        <div class="col-md-6">
          <LteInput v-model="search" label="Buscar" placeholder="Nombre o documento" @keyup.enter="load" />
        </div>
        <div class="col-md-6 d-flex align-items-end gap-2">
          <LteButton theme="primary" icon="bi-search" @click="load">Buscar</LteButton>
          <LteButton theme="success" icon="bi-plus-lg" @click="openCreate">Nueva persona</LteButton>
        </div>
      </div>

      <div v-if="loading" class="text-center py-4 text-muted">Cargando...</div>
      <div v-else class="table-responsive">
        <table class="table table-hover align-middle">
          <thead>
            <tr>
              <th>Documento</th>
              <th>Nombre</th>
              <th>País</th>
              <th>Contacto</th>
              <th class="text-end">Acciones</th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="!personas.length">
              <td colspan="5" class="text-center text-muted py-4">Sin registros</td>
            </tr>
            <tr v-for="p in personas" :key="p.id">
              <td>
                <span class="badge text-bg-secondary me-1">{{ p.tipo_documento_label }}</span>
                {{ p.numero_documento_formateado }}
              </td>
              <td>{{ p.nombre_completo }}</td>
              <td>{{ p.pais?.nombre || p.pais_documento || '—' }}</td>
              <td>{{ p.email || '—' }}</td>
              <td class="text-end">
                <LteButton size="sm" theme="warning" icon="bi-pencil" @click="openEdit(p)" />
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </LteCard>

    <LteModal v-model="showModal" :title="editingId ? 'Editar persona' : 'Nueva persona'" size="lg">
      <LteAlert v-if="error" theme="danger" class="mb-3">{{ error }}</LteAlert>
      <div class="row g-3">
        <div class="col-md-4">
          <LteSelect v-model="form.tipo_documento" label="Tipo documento">
            <option v-for="t in tiposDocumento" :key="t.value" :value="t.value">{{ t.label }}</option>
          </LteSelect>
        </div>
        <div class="col-md-4">
          <LteInput v-model="form.numero_documento" label="Número" :invalid="!!fieldErrors.numero_documento" :feedback="fieldErrors.numero_documento" />
        </div>
        <div v-if="requierePais" class="col-md-4">
          <LteSelect v-model="form.pais_documento" label="País emisión" :invalid="!!fieldErrors.pais_documento" :feedback="fieldErrors.pais_documento">
            <option v-for="pais in catalogos.paises" :key="pais.codigo" :value="pais.codigo">{{ pais.nombre }}</option>
          </LteSelect>
        </div>
        <div class="col-md-4"><LteInput v-model="form.nombres" label="Nombres" :invalid="!!fieldErrors.nombres" :feedback="fieldErrors.nombres" /></div>
        <div class="col-md-4"><LteInput v-model="form.apellido_paterno" label="Apellido paterno" /></div>
        <div class="col-md-4"><LteInput v-model="form.apellido_materno" label="Apellido materno" /></div>
        <div class="col-md-6"><LteInput v-model="form.email" label="Email" type="email" /></div>
        <div class="col-md-6"><LteInput v-model="form.telefono" label="Teléfono" /></div>
      </div>
      <template #footer>
        <LteButton theme="secondary" @click="showModal = false">Cancelar</LteButton>
        <LteButton theme="primary" :loading="saving" @click="submit">Guardar</LteButton>
      </template>
    </LteModal>
  </LteAppContent>
</template>
