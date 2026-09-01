<script setup lang="ts">
import type { Alumno, Matricula, PersonaPayload, TipoDocumento } from '~/types/comunidad'

definePageMeta({ layout: 'admin', middleware: 'auth' })

const { api, extractErrorMessage, extractValidationErrors } = useApi()
const catalogos = useCatalogosStore()

const alumnos = ref<Alumno[]>([])
const historial = ref<Matricula[]>([])
const loading = ref(false)
const saving = ref(false)
const error = ref<string | null>(null)
const fieldErrors = ref<Record<string, string>>({})
const showModal = ref(false)
const showHistorial = ref(false)
const selectedAlumno = ref<Alumno | null>(null)
const successMessage = ref<string | null>(null)

const form = reactive<PersonaPayload & { codigo_interno: string; observaciones: string }>({
  tipo_documento: 'rut',
  numero_documento: '',
  pais_documento: 'CL',
  nombres: '',
  apellido_paterno: '',
  apellido_materno: '',
  codigo_interno: '',
  observaciones: '',
})

async function load() {
  loading.value = true
  try {
    const res = await api<{ data: Alumno[] }>('/alumnos')
    alumnos.value = res.data
  } catch (e) {
    error.value = extractErrorMessage(e)
  } finally {
    loading.value = false
  }
}

async function submit() {
  saving.value = true
  error.value = null
  fieldErrors.value = {}
  try {
    await api('/alumnos', {
      method: 'POST',
      body: {
        ...form,
        pais_documento: form.tipo_documento === 'rut' ? 'CL' : form.pais_documento,
      },
    })
    successMessage.value = 'Alumno registrado.'
    showModal.value = false
    await load()
  } catch (e) {
    fieldErrors.value = extractValidationErrors(e)
    error.value = extractErrorMessage(e)
  } finally {
    saving.value = false
  }
}

async function verHistorial(alumno: Alumno) {
  selectedAlumno.value = alumno
  const res = await api<{ data: Matricula[] }>(`/alumnos/${alumno.id}/historial`)
  historial.value = res.data
  showHistorial.value = true
}

onMounted(async () => {
  await catalogos.load()
  await load()
})
</script>

<template>
  <LteAppContent
    title="Alumnos"
    :breadcrumbs="[{ label: 'Inicio', href: '/dashboard' }, { label: 'Comunidad' }, { label: 'Alumnos' }]"
  >
    <LteAlert v-if="successMessage" theme="success" dismissible class="mb-3" @close="successMessage = null">{{ successMessage }}</LteAlert>

    <LteCard title="Alumnos registrados" icon="bi-mortarboard" theme="primary" variant="outline">
      <div class="mb-3">
        <LteButton theme="success" icon="bi-plus-lg" @click="showModal = true">Nuevo alumno</LteButton>
      </div>
      <div class="table-responsive">
        <table class="table table-hover">
          <thead><tr><th>Documento</th><th>Nombre</th><th>Código</th><th class="text-end">Acciones</th></tr></thead>
          <tbody>
            <tr v-for="a in alumnos" :key="a.id">
              <td>{{ a.persona?.numero_documento_formateado }}</td>
              <td>{{ a.persona?.nombre_completo }}</td>
              <td>{{ a.codigo_interno || '—' }}</td>
              <td class="text-end">
                <LteButton size="sm" theme="info" icon="bi-clock-history" @click="verHistorial(a)">Historial</LteButton>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </LteCard>

    <LteModal v-model="showModal" title="Registrar alumno" size="lg">
      <LteAlert v-if="error" theme="danger" class="mb-3">{{ error }}</LteAlert>
      <div class="row g-3">
        <div class="col-md-4">
          <LteSelect v-model="form.tipo_documento" label="Tipo documento">
            <option value="rut">RUT</option><option value="pasaporte">Pasaporte</option><option value="dni">DNI</option><option value="otro">Otro</option>
          </LteSelect>
        </div>
        <div class="col-md-4"><LteInput v-model="form.numero_documento" label="Número documento" /></div>
        <div v-if="form.tipo_documento !== 'rut'" class="col-md-4">
          <LteSelect v-model="form.pais_documento" label="País">
            <option v-for="p in catalogos.paises" :key="p.codigo" :value="p.codigo">{{ p.nombre }}</option>
          </LteSelect>
        </div>
        <div class="col-md-4"><LteInput v-model="form.nombres" label="Nombres" /></div>
        <div class="col-md-4"><LteInput v-model="form.apellido_paterno" label="Apellido paterno" /></div>
        <div class="col-md-4"><LteInput v-model="form.apellido_materno" label="Apellido materno" /></div>
        <div class="col-md-6"><LteInput v-model="form.codigo_interno" label="Código interno (opcional)" /></div>
        <div class="col-12"><LteInput v-model="form.observaciones" label="Observaciones" /></div>
      </div>
      <template #footer>
        <LteButton theme="secondary" @click="showModal = false">Cancelar</LteButton>
        <LteButton theme="primary" :loading="saving" @click="submit">Registrar</LteButton>
      </template>
    </LteModal>

    <LteModal v-model="showHistorial" title="Historial académico" size="lg">
      <p v-if="selectedAlumno"><strong>{{ selectedAlumno.persona?.nombre_completo }}</strong></p>
      <ul class="list-group">
        <li v-for="m in historial" :key="m.id" class="list-group-item d-flex justify-content-between align-items-center flex-wrap gap-2">
          <div>
            <div>{{ m.periodo_escolar?.anio }} — {{ m.curso?.nombre_display }}</div>
            <span class="badge text-bg-primary">{{ m.estado_label }}</span>
          </div>
          <NuxtLink
            v-if="m.estado === 'activa'"
            :to="`/asistencia/resumen-mensual?periodo=${m.periodo_escolar_id}&curso=${m.curso_id}&matricula=${m.id}`"
            class="btn btn-sm btn-outline-primary"
            @click="showHistorial = false"
          >
            <i class="bi bi-clipboard-check" /> Ver asistencia
          </NuxtLink>
        </li>
        <li v-if="!historial.length" class="list-group-item text-muted">Sin matrículas</li>
      </ul>
      <template #footer><LteButton theme="secondary" @click="showHistorial = false">Cerrar</LteButton></template>
    </LteModal>
  </LteAppContent>
</template>
