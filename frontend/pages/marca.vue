<template>
  <LteAppContent
    title="Marca"
    :breadcrumbs="[
      { label: 'Inicio', href: '/dashboard' },
      { label: 'Marca' },
    ]"
  >
    <button>
        <LteButton
          theme="primary"
          label="Agregar Marca"
          icon="bi-plus"
          @click="crearModal"
        />
    </button>
    <table class="table table-hover align-middle">
      <thead>
        <tr>
          <th>ID</th>
          <th>Nombre</th>
          <th class="text-end">Acciones</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="marca in marcas" :key="marca.id">
          <td>{{ marca.id }}</td>
          <td>{{ marca.nombre }}</td>
          <td class="text-end">
            <LteButton
              theme="info"
              size="sm"
              outline
              label="Editar"
              icon="bi-pencil"
              @click="editMarca(marca)"
            />
            <LteButton
              theme="danger"
              size="sm"
              outline
              label="Eliminar"
              icon="bi-trash"
              @click="deleteMarca(marca.id)"
            />
          </td>
        </tr>
      </tbody>
    </table>

    <LteModal
      v-model="showModal"
      title="Editar marca"
      theme="primary"
      centered
    >
      <form @submit.prevent="saveMarca">
        <LteInput
          v-model="form.nombre"
          label="Nombre"
          placeholder="Nombre de la marca"
          required
        />
      </form>

      <template #footer="{ close }">
        <LteButton
          theme="secondary"
          label="Cancelar"
          outline
          @click="close()"
        />
        <LteButton
          theme="primary"
          label="Guardar"
          icon="bi-save"
          @click="saveMarca"
        />
      </template>
    </LteModal>
  </LteAppContent>
</template>

<script setup lang="ts">
import type { Marca } from '~/types/marca'

definePageMeta({
  layout: 'admin',
  middleware: 'auth',
})

const marcas = ref<Marca[]>([])
const showModal = ref(false)
const editingId = ref<number | null>(null)
const form = reactive({
    nombre: '',
})

onMounted(fetchAll)

function fetchAll() {
    marcas.value = [
        { id: 1, nombre: 'Marca 1' },
        { id: 2, nombre: 'Marca 2' },
        { id: 3, nombre: 'Marca 3' },
    ]
}

function editMarca(marca: Marca) {
    editingId.value = marca.id
    form.nombre = marca.nombre
    showModal.value = true
}

function crearModal() {
    editingId.value = null
    form.nombre = ''
    showModal.value = true
}

function saveMarca() {
    const nombre = form.nombre.trim()

    if (!nombre || editingId.value === null) {
        if (nombre && editingId.value === null) {
          marcas.value.push({ 
            id: (marcas.value.at(-1)?.id ?? 0) + 1,
            nombre: nombre
          })
        } else {
          return
        }
    }

    const marca = marcas.value.find(item => item.id === editingId.value)

    if (marca) {
        marca.nombre = nombre
    }

    showModal.value = false
}
function deleteMarca(id: number) {
    marcas.value = marcas.value.filter(item => item.id !== id)
}
</script>
