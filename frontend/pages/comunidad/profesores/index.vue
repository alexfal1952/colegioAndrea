<script setup lang="ts">
import type { PersonaPayload, Profesor } from '~/types/comunidad'

definePageMeta({ layout: 'admin', middleware: 'auth' })

const { api, extractErrorMessage } = useApi()
const catalogos = useCatalogosStore()

const profesores = ref<Profesor[]>([])
const saving = ref(false)
const error = ref<string | null>(null)
const showModal = ref(false)
const successMessage = ref<string | null>(null)

const form = reactive<PersonaPayload & { codigo_interno: string }>({
  tipo_documento: 'rut',
  numero_documento: '',
  pais_documento: 'CL',
  nombres: '',
  apellido_paterno: '',
  apellido_materno: '',
  codigo_interno: '',
})

async function load() {
  const res = await api<{ data: Profesor[] }>('/profesores')
  profesores.value = res.data
}

async function submit() {
  saving.value = true
  error.value = null
  try {
    await api('/profesores', {
      method: 'POST',
      body: { ...form, pais_documento: form.tipo_documento === 'rut' ? 'CL' : form.pais_documento },
    })
    successMessage.value = 'Profesor registrado.'
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
  await load()
})
</script>

<template>
  <LteAppContent
    title="Profesores"
    :breadcrumbs="[{ label: 'Inicio', href: '/dashboard' }, { label: 'Comunidad' }, { label: 'Profesores' }]"
  >
    <LteAlert v-if="successMessage" theme="success" dismissible class="mb-3" @close="successMessage = null">{{ successMessage }}</LteAlert>

    <LteCard title="Profesores" icon="bi-person-workspace" theme="primary" variant="outline">
      <div class="mb-3"><LteButton theme="success" icon="bi-plus-lg" @click="showModal = true">Nuevo profesor</LteButton></div>
      <table class="table table-hover">
        <thead><tr><th>Documento</th><th>Nombre</th><th>Código</th><th>Estado</th></tr></thead>
        <tbody>
          <tr v-for="p in profesores" :key="p.id">
            <td>{{ p.persona?.numero_documento_formateado }}</td>
            <td>{{ p.persona?.nombre_completo }}</td>
            <td>{{ p.codigo_interno || '—' }}</td>
            <td><span :class="['badge', p.activo ? 'text-bg-success' : 'text-bg-secondary']">{{ p.activo ? 'Activo' : 'Inactivo' }}</span></td>
          </tr>
        </tbody>
      </table>
    </LteCard>

    <LteModal v-model="showModal" title="Registrar profesor" size="lg">
      <LteAlert v-if="error" theme="danger" class="mb-3">{{ error }}</LteAlert>
      <div class="row g-3">
        <div class="col-md-4"><LteSelect v-model="form.tipo_documento" label="Tipo documento"><option value="rut">RUT</option><option value="pasaporte">Pasaporte</option><option value="dni">DNI</option></LteSelect></div>
        <div class="col-md-4"><LteInput v-model="form.numero_documento" label="Número" /></div>
        <div v-if="form.tipo_documento !== 'rut'" class="col-md-4"><LteSelect v-model="form.pais_documento" label="País"><option v-for="pa in catalogos.paises" :key="pa.codigo" :value="pa.codigo">{{ pa.nombre }}</option></LteSelect></div>
        <div class="col-md-4"><LteInput v-model="form.nombres" label="Nombres" /></div>
        <div class="col-md-4"><LteInput v-model="form.apellido_paterno" label="Apellido paterno" /></div>
        <div class="col-md-4"><LteInput v-model="form.apellido_materno" label="Apellido materno" /></div>
        <div class="col-md-6"><LteInput v-model="form.codigo_interno" label="Código interno" /></div>
      </div>
      <template #footer>
        <LteButton theme="secondary" @click="showModal = false">Cancelar</LteButton>
        <LteButton theme="primary" :loading="saving" @click="submit">Registrar</LteButton>
      </template>
    </LteModal>
  </LteAppContent>
</template>
