<template>
  <LteAppContent
    title="Marca Dos"
    :breadcrumbs="[
      { label: 'Inicio', href: '/dashboard' },
      { label: 'Marca Dos' },
    ]"
  >
  <p>Total de marcas: {{ total }}</p>
    <LteButton
      theme="primary"
      label="Agregar Marca"
      icon="bi-plus"
      @click="showModal = true; editingId = null; form.nombre = ''"
    />
    <p v-if="store.loading">
      Cargando marcas...
    </p>
   <table class="table table-hover align-middle">
      <thead>
        <tr>
          <th>ID</th>
          <th>Nombre</th>
          <th class="text-end">Acciones</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="marca in store.marcas" :key="marca.id">
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
              @click="deleteMarcaFormulario(marca.id)"
            />
          </td>
        </tr>
      </tbody>
    </table>
        <LteModal
      v-model="showModal"
      :title="editingId == null ? 'Crear marca' : 'Editar marca'"
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

definePageMeta({
  layout: 'admin',
  middleware: 'auth',
})

import type { Marca } from '~/types/marca'
const store = useMarcaStore()
const { marcas } = storeToRefs(store)
const { total } = useTotalElementos(marcas)

const form = reactive({
    nombre: '',
})
const showModal = ref(false)
const editingId = ref<number | null>(null)

onMounted(() => {
  store.fetchMarcas()
  console.log(store.marcas)
})
function editMarca(marca: Marca) {
  showModal.value = true
  form.nombre = marca.nombre
  editingId.value = marca.id
  console.log('Editar marca:', marca)
}

function saveMarca() {
  store.saveMarca(editingId.value, form.nombre)
  showModal.value = true
}

function deleteMarcaFormulario(id: number) {
  alert("hola")
  store.deleteMarca(id)
}
</script>
