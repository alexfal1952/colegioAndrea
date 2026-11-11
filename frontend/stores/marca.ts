import { defineStore } from 'pinia'
import type { marca } from '~/types/marca'

export const useMarcaStore = defineStore('marcas', () => {

    const marcas = ref<marca[]>([])
    const loading = ref(false)

    function saveMarca(id: number | null, nombre: string) {
        if (id) {
            const index = marcas.value.findIndex(m => m.id === id)
            if (index !== -1) {
                marcas.value[index].nombre = nombre
            }
        } else {
            const newMarca = { id: (marcas.value.at(-1)?.id ?? 0) + 1, nombre }
            marcas.value.push(newMarca)
        }
    }

    function fetchMarcas() {
        loading.value = true
        setTimeout(() => {
            marcas.value = [
                { id: 1, nombre: 'Marca 1' },
                { id: 2, nombre: 'Marca 2' },
                { id: 3, nombre: 'Marca 3' },
            ]
            loading.value = false
        }, 5000)
    }

    function deleteMarca(id: number) {
        marcas.value = marcas.value.filter(m => m.id !== id)
    }

    return {
        loading,
        saveMarca,
        marcas,
        fetchMarcas,
        deleteMarca
    }

})