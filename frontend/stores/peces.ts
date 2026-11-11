import { defineStore } from 'pinia'
import { pezService } from '~/services/pezService'
import type { Pez, PezPayload } from '~/types/pez'

export const usePecesStore = defineStore('peces', () => {
  const peces = ref<Pez[]>([])
  const loading = ref(false)
  const saving = ref(false)
  const deletingId = ref<number | null>(null)
  const error = ref<string | null>(null)

  function ordenar() {
    peces.value.sort((a, b) => a.nombre.localeCompare(b.nombre, 'es'))
  }

  async function fetchAll() {
    loading.value = true
    error.value = null

    try {
      peces.value = await pezService.listar()
    } catch (caught: unknown) {
      console.error(caught)
      error.value = 'No se pudieron cargar los peces.'
    } finally {
      loading.value = false
    }
  }

  async function create(payload: PezPayload) {
    saving.value = true
    error.value = null

    try {
      const pez = await pezService.crear(payload)
      peces.value.push(pez)
      ordenar()
      return pez
    } catch (caught: unknown) {
      console.error(caught)
      error.value = 'No se pudo crear el pez.'
      throw caught
    } finally {
      saving.value = false
    }
  }

  async function update(id: number, payload: PezPayload) {
    saving.value = true
    error.value = null

    try {
      const pez = await pezService.actualizar(id, payload)
      const index = peces.value.findIndex(item => item.id === id)

      if (index !== -1) {
        peces.value[index] = pez
      }

      ordenar()
      return pez
    } catch (caught: unknown) {
      console.error(caught)
      error.value = 'No se pudo actualizar el pez.'
      throw caught
    } finally {
      saving.value = false
    }
  }

  async function remove(id: number) {
    deletingId.value = id
    error.value = null

    try {
      await pezService.eliminar(id)
      peces.value = peces.value.filter(pez => pez.id !== id)
    } catch (caught: unknown) {
      console.error(caught)
      error.value = 'No se pudo eliminar el pez.'
      throw caught
    } finally {
      deletingId.value = null
    }
  }

  return {
    peces,
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
