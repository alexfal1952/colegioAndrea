import { defineStore } from 'pinia'
import type { Animal, AnimalPayload } from '~/types/animal'

export const useAnimalesStore = defineStore('animales', () => {
    const animales = ref<Animal[]>([])
    const loading = ref(false)
    const error = ref<string | null>(null)
    const saving = ref(false)
    const deletingId = ref<number | null>(null)

    async function create(payload: AnimalPayload) {
        const config = useRuntimeConfig()
        const auth = useAuthStore()

        saving.value = true
        error.value = null

        try {
            const animal = await $fetch<Animal>(
            `${config.public.apiBase}/animales`,
            {
                method: 'POST',
                body: payload,
                headers: {
                Authorization: `Bearer ${auth.token}`,
                Accept: 'application/json',
                },
            },
            )

            animales.value.push(animal)

            animales.value.sort((a, b) =>
            a.nombre.localeCompare(b.nombre, 'es'),
            )

            return animal
        } catch (caught: unknown) {
            console.error(caught)
            error.value = 'No se pudo guardar el animal.'
            throw caught
        } finally {
            saving.value = false
        }
    }
    async function update(id: number, payload: AnimalPayload) {
        const config = useRuntimeConfig()
        const auth = useAuthStore()

        saving.value = true
        error.value = null

        try {
            const animal = await $fetch<Animal>(
            `${config.public.apiBase}/animales/${id}`,
            {
                method: 'PUT',
                body: payload,
                headers: {
                Authorization: `Bearer ${auth.token}`,
                Accept: 'application/json',
                },
            },
            )

            const index = animales.value.findIndex(
            item => item.id === id,
            )

            if (index !== -1) {
            animales.value[index] = animal
            }

            animales.value.sort((a, b) =>
            a.nombre.localeCompare(b.nombre, 'es'),
            )

            return animal
        } catch (caught: unknown) {
            console.error(caught)
            error.value = 'No se pudo actualizar el animal.'
            throw caught
        } finally {
            saving.value = false
        }
    }

    async function remove(id: number) {
        const config = useRuntimeConfig()
        const auth = useAuthStore()

        deletingId.value = id
        error.value = null

        try {
            await $fetch(
                `${config.public.apiBase}/animales/${id}`,
                {
                    method: 'DELETE',
                    headers: {
                        Authorization: `Bearer ${auth.token}`,
                        Accept: 'application/json',
                    },
                },
            )

            animales.value = animales.value.filter(
                animal => animal.id !== id,
            )
        } catch (caught: unknown) {
            console.error(caught)
            error.value = 'No se pudo eliminar el animal.'
            throw caught
        } finally {
            deletingId.value = null
        }
    }

    async function fetchAll() {
        const config = useRuntimeConfig()
        const auth = useAuthStore()

        loading.value = true
        error.value = null

        try {
            animales.value = await $fetch<Animal[]>(
            `${config.public.apiBase}/animales`,
            {
                headers: {
                Authorization: `Bearer ${auth.token}`,
                Accept: 'application/json',
                },
            },
            )
        } catch (caught: unknown) {
            console.error(caught)
            error.value = 'No se pudieron cargar los animales.'
        } finally {
            loading.value = false
        }
    }

    return {
        animales,
        loading,
        saving,
        deletingId,
        error,
        fetchAll,
        create,
        update,
        remove,
    }
})
