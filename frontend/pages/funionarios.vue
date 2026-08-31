<template>
  <LteAppContent
    title="Funcionarios"
    :breadcrumbs="[
      { label: 'Inicio', href: '/dashboard' },
      { label: 'Funcionarios' },
    ]"
  >
    <div class="row mb-3">
      <div class="col-md-8">
        <p class="text-muted mb-0">
          Mantenedor de cuentas: lista los funcionarios y asígnales un perfil.
          La contraseña se define por correo electrónico.
        </p>
      </div>
      <div class="col-md-4 text-md-end">
        <LteButton
          theme="primary"
          label="Nuevo funcionario"
          icon="bi-person-plus"
          @click="openCreate"
        />
      </div>
    </div>

    <LteAlert v-if="store.success" theme="success" icon="bi-check-circle" title="Listo" dismissible>
      <p class="mb-2">{{ store.success }}</p>
      <div v-if="store.resetUrl" class="mt-2">
        <p class="mb-1 small">
          Si el correo no llega (spam o filtro corporativo), copia este enlace:
        </p>
        <div class="input-group input-group-sm">
          <input :value="store.resetUrl" class="form-control" readonly>
          <button type="button" class="btn btn-outline-success" @click="copyResetUrl">
            Copiar
          </button>
        </div>
        <a :href="store.resetUrl" class="small d-inline-block mt-2" target="_blank" rel="noopener">
          Abrir enlace
        </a>
      </div>
    </LteAlert>

    <LteAlert v-if="store.error" theme="danger" icon="bi-x-circle" title="Error" dismissible>
      {{ store.error }}
    </LteAlert>

    <LteCard title="Listado de funcionarios" icon="bi-people" theme="primary" variant="outline">
      <div v-if="store.loading" class="text-center py-4 text-muted">
        Cargando funcionarios...
      </div>

      <div v-else-if="store.funcionarios.length === 0" class="text-center py-4 text-muted">
        No hay funcionarios registrados todavía.
      </div>

      <div v-else class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead>
            <tr>
              <th>Nombre</th>
              <th>Email</th>
              <th>Perfil</th>
              <th>Creado</th>
              <th class="text-end">Acciones</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="item in store.funcionarios" :key="item.id">
              <td class="fw-semibold">{{ item.name }}</td>
              <td>{{ item.email }}</td>
              <td>
                <span v-if="item.role" class="badge" :class="roleBadgeClass(item.role.slug)">
                  {{ item.role.name }}
                </span>
                <span v-else class="badge text-bg-secondary">Sin perfil</span>
              </td>
              <td>{{ formatDate(item.created_at) }}</td>
              <td class="text-end">
                <div class="d-inline-flex gap-2 flex-wrap justify-content-end">
                  <LteButton
                    theme="warning"
                    size="sm"
                    outline
                    :label="store.resettingId === item.id ? 'Enviando...' : 'Restablecer contraseña'"
                    icon="bi-envelope-at"
                    :disabled="store.resettingId === item.id"
                    @click="sendReset(item)"
                  />
                  <LteButton
                    theme="info"
                    size="sm"
                    outline
                    label="Editar"
                    icon="bi-pencil"
                    @click="openEdit(item)"
                  />
                  <LteButton
                    theme="danger"
                    size="sm"
                    outline
                    label="Eliminar"
                    icon="bi-trash"
                    :disabled="item.id === auth.user?.id"
                    @click="confirmRemove(item)"
                  />
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </LteCard>

    <LteModal
      v-model="showModal"
      :title="editing ? 'Editar funcionario' : 'Nuevo funcionario'"
      theme="primary"
      centered
    >
      <LteCallout v-if="!editing" theme="info" title="Acceso por correo">
        Al crear la cuenta se enviará un enlace al email del funcionario para que defina su contraseña.
        No se muestra ni se guarda una contraseña visible aquí.
      </LteCallout>

      <form @submit.prevent="save">
        <LteInput v-model="form.name" label="Nombre" placeholder="Nombre completo" required />
        <LteInput
          v-model="form.email"
          label="Email"
          type="email"
          placeholder="correo@ejemplo.com"
          required
        />
        <LteSelect
          v-model="form.role_id"
          label="Perfil"
          :options="roleOptions"
          required
        />
      </form>

      <template #footer="{ close }">
        <LteButton theme="secondary" label="Cancelar" outline @click="close()" />
        <LteButton
          theme="primary"
          :label="store.saving ? 'Guardando...' : 'Guardar'"
          icon="bi-save"
          :disabled="store.saving"
          @click="save"
        />
      </template>
    </LteModal>
  </LteAppContent>
</template>

<script setup lang="ts">
import type { Funcionario } from '~/types/funcionario'

definePageMeta({
  layout: 'admin',
  middleware: 'auth',
})

const auth = useAuthStore()
const store = useFuncionariosStore()

const showModal = ref(false)
const editing = ref<Funcionario | null>(null)

const form = reactive({
  name: '',
  email: '',
  role_id: null as number | null,
})

const roleOptions = computed(() =>
  store.roles.map(role => ({
    value: role.id,
    label: role.name,
  })),
)

onMounted(async () => {
  await Promise.all([store.fetchRoles(), store.fetchAll()])
})

function roleBadgeClass(slug: string) {
  const map: Record<string, string> = {
    admin: 'text-bg-danger',
    supervisor: 'text-bg-warning',
    funcionario: 'text-bg-primary',
    consulta: 'text-bg-secondary',
  }
  return map[slug] || 'text-bg-info'
}

function formatDate(value: string) {
  return new Date(value).toLocaleDateString('es-CL', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
  })
}

function resetForm() {
  form.name = ''
  form.email = ''
  form.role_id = store.roles.find(role => role.slug === 'funcionario')?.id ?? null
}

function openCreate() {
  editing.value = null
  resetForm()
  store.clearMessages()
  showModal.value = true
}

function openEdit(item: Funcionario) {
  editing.value = item
  form.name = item.name
  form.email = item.email
  form.role_id = item.role_id
  store.clearMessages()
  showModal.value = true
}

async function save() {
  if (!form.role_id) {
    store.error = 'Debes asignar un perfil.'
    return
  }

  try {
    const payload = {
      name: form.name,
      email: form.email,
      role_id: Number(form.role_id),
    }

    if (editing.value) {
      await store.update(editing.value.id, payload)
    } else {
      await store.create(payload)
    }

    showModal.value = false
  } catch {
    // error en el store
  }
}

async function sendReset(item: Funcionario) {
  if (!confirm(`¿Enviar enlace de restablecimiento a ${item.email}?`)) {
    return
  }

  try {
    await store.sendPasswordReset(item.id)
  } catch {
    // error en el store
  }
}

async function copyResetUrl() {
  if (!store.resetUrl || !import.meta.client) {
    return
  }

  try {
    await navigator.clipboard.writeText(store.resetUrl)
    store.success = 'Enlace copiado al portapapeles.'
  } catch {
    store.error = 'No se pudo copiar el enlace.'
  }
}

async function confirmRemove(item: Funcionario) {
  if (!confirm(`¿Eliminar a ${item.name}?`)) {
    return
  }

  try {
    await store.remove(item.id)
  } catch {
    // error en el store
  }
}
</script>
