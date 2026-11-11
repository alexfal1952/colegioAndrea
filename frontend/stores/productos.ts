import { defineStore } from 'pinia'
import type {
    CreateProductPayload,
    Product,
    UpdateProductPayload,
} from '~/types/product'
import { useProductsService } from '~/services/productoService'


export const useProductoStore = defineStore('productos', () => {

    const productos = ref<Product[]>([])
    const loading = ref(false)
    const saving = ref(false)
    const deletingId = ref<number | null>(null)
    const error = ref<string | null>(null)
    const productoService = useProductsService()
    



    async function fetchProducts() {
        loading.value = true
        error.value = null

        try {
            const response = await productoService.list()
            productos.value = response.products
        } catch (caught: unknown) {
            console.error(caught)
            error.value = 'No se pudieron cargar los productos.'
        } finally {
            loading.value = false
        }
    }

    async function createProduct(payload: CreateProductPayload) {
        saving.value = true
        error.value = null

        try {
            const producto = await productoService.create(payload)
            productos.value.unshift(producto)
            return producto
        } catch (caught: unknown) {
            console.error(caught)
            error.value = 'No se pudo crear el producto.'
            throw caught
        } finally {
            saving.value = false
        }
    }

    async function updateProduct(id: number, payload: UpdateProductPayload) {
        saving.value = true
        error.value = null

        try {
            const producto = await productoService.update(id, payload)
            const index = productos.value.findIndex(item => item.id === id)

            if (index !== -1) {
                productos.value[index] = producto
            }

            return producto
        } catch (caught: unknown) {
            console.error(caught)
            error.value = 'No se pudo actualizar el producto.'
            throw caught
        } finally {
            saving.value = false
        }
    }

    async function removeProduct(id: number) {
        deletingId.value = id
        error.value = null

        try {
            await productoService.remove(id)
            productos.value = productos.value.filter(producto => producto.id !== id)
        } catch (caught: unknown) {
            console.error(caught)
            error.value = 'No se pudo eliminar el producto.'
            throw caught
        } finally {
            deletingId.value = null
        }
    }

    return {
        loading,
        saving,
        deletingId,
        error,
        productos,
        fetchProducts,
        createProduct,
        updateProduct,
        removeProduct,
    }

})
