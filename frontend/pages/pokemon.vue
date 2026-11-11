<template>
    <LteAppContent
        title="Pokemon"
        :breadcrumbs="[
        { label: 'Inicio', href: '/dashboard' },
        { label: 'Pokemon' },
        ]"
    >
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Tipo</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <tr v-for="pokemon in pokemones" :key="pokemon.id">
                <td>{{ pokemon.id }}</td>
                <td>{{ pokemon.nombre }}</td>
                <td>{{ pokemon.tipo }}</td>
                <td>
                    <button @click="editPokemon(pokemon.id)">Editar</button>
                    <button @click="deletePokemon(pokemon.id)">Eliminar</button>
                </td>
            </tr>
        </tbody>
    </table>
    <LteModal
  v-model="showModal"
  title="Editar Pokémon"
  theme="primary"
  centered
>
  <form @submit.prevent="updatePokemon">
    <LteInput
      v-model="form.nombre"
      label="Nombre"
      placeholder="Nombre del Pokémon"
      required
    />

    <LteSelect
      v-model="form.tipo"
      label="Tipo"
      :options="tipoOptions"
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
      label="Guardar cambios"
      icon="bi-save"
      @click="updatePokemon"
    />
  </template>
</LteModal>
    </LteAppContent>
</template>

<script setup lang="ts">
    import type {Pokemon,TipoPokemon} from '~/types/pokemon'
    definePageMeta({
        layout: 'admin',
        middleware: 'auth',
    })
    const showModal = ref(false)
const editingId = ref<number | null>(null)

const form = reactive({
  nombre: '',
  tipo: 'normal' as TipoPokemon,
})

const tipoOptions = [
  { value: 'electrico', label: 'Eléctrico' },
  { value: 'fuego', label: 'Fuego' },
  { value: 'agua', label: 'Agua' },
  { value: 'planta', label: 'Planta' },
  { value: 'normal', label: 'Normal' },
]

    onMounted(fetchAll)
    const pokemones = ref<Pokemon[]>([])

    function editPokemon(id: number) {
        const pokemon = pokemones.value.find(
            item => item.id === id,
        )

        if (!pokemon) {
            alert('Pokémon no encontrado')
            return
        }

        editingId.value = pokemon.id
        form.nombre = pokemon.nombre
        form.tipo = pokemon.tipo
        showModal.value = true
    }

    function updatePokemon() {
        if (editingId.value === null) {
            return
        }

        const pokemon = pokemones.value.find(
            item => item.id === editingId.value,
        )

        if (!pokemon) {
            alert('Pokémon no encontrado')
            return
        }

        if (form.nombre.trim() === '') {
            alert('El nombre es obligatorio')
            return
        }

        pokemon.nombre = form.nombre.trim()
        pokemon.tipo = form.tipo

        showModal.value = false
        editingId.value = null
    }

   function deletePokemon(id: number) {
        const confirmed = confirm(
            `¿Quieres eliminar el Pokémon con ID ${id}?`,
        )

        if (!confirmed) {
            return
        }

        pokemones.value = pokemones.value.filter(
            pokemon => pokemon.id !== id,
        )
    }

    function fetchAll() {
        const data: Pokemon[] = [
            {
                id: 1,
                nombre: 'Pikachu',
                tipo: 'electrico',
            },
            {
                id: 2,
                nombre: 'Charmander',
                tipo: 'fuego',
            },
            {
                id: 3,
                nombre: 'Squirtle',
                tipo: 'agua',
            },
        ]

        pokemones.value = data

        console.log(pokemones.value)
    }
</script>
