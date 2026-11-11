<template>
    <LteAppContent
    title="Productos"
    :breadcrumbs="[
        { label: 'Inicio', href: '/dashboard' },
        { label: 'Productos' },
    ]"> 

    <div class="text-end mb-3">
      <LteButton
        theme="primary"
        icon="bi-plus-circle"
        label="Crear producto"
        @click="openCreate"
      />
    </div>

    <LteAlert
      v-if="store.error"
      theme="danger"
      title="Error"
      class="mb-3"
    >
      {{ store.error }}
    </LteAlert>
total = {{ total }}
<table class="table table-hover align-middle">
  <thead>
    <tr>
      <th>ID</th>
      <th>Imagen</th>
      <th>Título</th>
      <th>Categoría</th>
      <th>Marca</th>
      <th>Precio</th>
      <th>Stock</th>
      <th>Rating</th>
      <th class="text-end">Acciones</th>
    </tr>
  </thead>

  <tbody>
    <tr
      v-for="producto in productos"
      :key="producto.id"
    >
      <td>{{ producto.id }}</td>

      <td>
        <img
          v-if="producto.thumbnail"
          :src="producto.thumbnail"
          :alt="producto.title"
          width="60"
          height="60"
          class="object-fit-contain"
        >
        <span v-else>Sin imagen</span>
      </td>

      <td>
        {{ producto.title }}
      </td>

      <td>
        {{ producto.category }}
      </td>

      <td>
        {{ producto.brand ?? 'Sin marca' }}
      </td>

      <td>
        ${{ producto.price }}
      </td>

      <td>
        {{ producto.stock }}
      </td>

      <td>
        {{ producto.rating ?? 'Sin rating' }}
      </td>

      <td class="text-end">
        <LteButton
          theme="info"
          size="sm"
          outline
          label="Editar"
          icon="bi-pencil"
          @click="openEdit(producto)"
        />

        <LteButton
          theme="danger"
          size="sm"
          outline
          icon="bi-trash"
          :label="store.deletingId === producto.id ? 'Eliminando...' : 'Eliminar'"
          :disabled="store.deletingId === producto.id"
          @click="eliminarProducto(producto)"
        />
      </td>
    </tr>
  </tbody>
</table>

    <LteModal
      v-model="showModal"
      :title="editing ? 'Editar producto' : 'Crear producto'"
      theme="primary"
      centered
    >
      <form @submit.prevent="saveProduct">
        <div class="row">
          <div class="col-md-8">
            <LteInput
              v-model="form.title"
              label="Título"
              placeholder="Notebook Lenovo"
              required
            />
          </div>

          <div class="col-md-4">
            <LteInput
              v-model="form.sku"
              label="SKU"
              placeholder="LEN-001"
            />
          </div>

          <div class="col-12 mb-3">
            <label for="description" class="form-label">Descripción</label>
            <textarea
              id="description"
              v-model="form.description"
              class="form-control"
              rows="3"
            />
          </div>

          <div class="col-md-6">
            <LteInput
              v-model="form.category"
              label="Categoría"
              placeholder="laptops"
            />
          </div>

          <div class="col-md-6">
            <LteInput
              v-model="form.brand"
              label="Marca"
              placeholder="Lenovo"
            />
          </div>

          <div class="col-md-4">
            <LteInput
              v-model="form.price"
              label="Precio"
              type="number"
              min="0"
              step="0.01"
            />
          </div>

          <div class="col-md-4">
            <LteInput
              v-model="form.stock"
              label="Stock"
              type="number"
              min="0"
            />
          </div>

          <div class="col-md-4">
            <LteInput
              v-model="form.rating"
              label="Rating"
              type="number"
              min="0"
              max="5"
              step="0.1"
            />
          </div>

          <div class="col-md-6">
            <LteInput
              v-model="form.discountPercentage"
              label="Descuento (%)"
              type="number"
              min="0"
              step="0.01"
            />
          </div>

          <div class="col-md-6">
            <LteInput
              v-model="form.weight"
              label="Peso"
              type="number"
              min="0"
              step="0.01"
            />
          </div>

          <div class="col-md-4">
            <LteInput
              v-model="form.width"
              label="Ancho"
              type="number"
              min="0"
              step="0.01"
            />
          </div>

          <div class="col-md-4">
            <LteInput
              v-model="form.height"
              label="Alto"
              type="number"
              min="0"
              step="0.01"
            />
          </div>

          <div class="col-md-4">
            <LteInput
              v-model="form.depth"
              label="Profundidad"
              type="number"
              min="0"
              step="0.01"
            />
          </div>

          <div class="col-md-6">
            <LteInput
              v-model="form.warrantyInformation"
              label="Garantía"
            />
          </div>

          <div class="col-md-6">
            <LteInput
              v-model="form.shippingInformation"
              label="Información de despacho"
            />
          </div>

          <div class="col-md-6">
            <LteInput
              v-model="form.availabilityStatus"
              label="Disponibilidad"
            />
          </div>

          <div class="col-md-6">
            <LteInput
              v-model="form.returnPolicy"
              label="Política de devolución"
            />
          </div>

          <div class="col-md-6">
            <LteInput
              v-model="form.minimumOrderQuantity"
              label="Cantidad mínima"
              type="number"
              min="1"
            />
          </div>

          <div class="col-md-6">
            <LteInput
              v-model="form.tags"
              label="Etiquetas separadas por coma"
              placeholder="computación, notebook"
            />
          </div>

          <div class="col-12">
            <LteInput
              v-model="form.thumbnail"
              label="URL de miniatura"
              type="url"
            />
          </div>

          <div class="col-12 mb-3">
            <label for="images" class="form-label">
              URLs de imágenes (una por línea)
            </label>
            <textarea
              id="images"
              v-model="form.images"
              class="form-control"
              rows="3"
            />
          </div>
        </div>
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
          icon="bi-save"
          :label="store.saving ? 'Guardando...' : 'Guardar'"
          :disabled="store.saving"
          @click="saveProduct"
        />
      </template>
    </LteModal>
    </LteAppContent>
</template>
<script setup lang="ts">
import type { CreateProductPayload, Product } from '~/types/product'

definePageMeta({
  layout: 'admin',
  middleware: 'auth',
})

const store = useProductoStore()
const { productos } = storeToRefs(store)
const showModal = ref(false)
const editing = ref<Product | null>(null)
const { total } = useTotalElementos(productos)

function defaultForm() {
  return {
    title: 'Notebook Lenovo',
    description: 'Notebook para desarrollo',
    category: 'laptops',
    price: '899.99',
    discountPercentage: '10',
    rating: '4.5',
    stock: '20',
    tags: 'computación, notebook',
    brand: 'Lenovo',
    sku: 'LEN-001',
    weight: '2.1',
    width: '35',
    height: '2',
    depth: '24',
    warrantyInformation: '12 meses',
    shippingInformation: 'Despacho en 3 días',
    availabilityStatus: 'In Stock',
    returnPolicy: '30 días',
    minimumOrderQuantity: '1',
    thumbnail: 'https://ejemplo.com/notebook.jpg',
    images: [
      'https://ejemplo.com/notebook-1.jpg',
      'https://ejemplo.com/notebook-2.jpg',
    ].join('\n'),
  }
}

const form = reactive(defaultForm())


onMounted(() => {
  store.fetchProducts()
})

function openCreate() {
  Object.assign(form, defaultForm())
  editing.value = null
  showModal.value = true
}

function openEdit(producto: Product) {
  Object.assign(form, {
    title: producto.title,
    description: producto.description,
    category: producto.category,
    price: producto.price.toString(),
    discountPercentage: producto.discountPercentage?.toString() ?? '',
    rating: producto.rating?.toString() ?? '',
    stock: producto.stock.toString(),
    tags: producto.tags?.join(', ') ?? '',
    brand: producto.brand ?? '',
    sku: producto.sku ?? '',
    weight: producto.weight?.toString() ?? '',
    width: producto.dimensions?.width.toString() ?? '',
    height: producto.dimensions?.height.toString() ?? '',
    depth: producto.dimensions?.depth.toString() ?? '',
    warrantyInformation: producto.warrantyInformation ?? '',
    shippingInformation: producto.shippingInformation ?? '',
    availabilityStatus: producto.availabilityStatus ?? '',
    returnPolicy: producto.returnPolicy ?? '',
    minimumOrderQuantity: producto.minimumOrderQuantity?.toString() ?? '',
    thumbnail: producto.thumbnail ?? '',
    images: producto.images?.join('\n') ?? '',
  })

  editing.value = producto
  showModal.value = true
}

async function saveProduct() {
  const payload: CreateProductPayload = {
    title: form.title.trim(),
    description: form.description.trim(),
    category: form.category.trim(),
    price: Number(form.price),
    discountPercentage: Number(form.discountPercentage),
    rating: Number(form.rating),
    stock: Number(form.stock),
    tags: form.tags.split(',').map(tag => tag.trim()).filter(Boolean),
    brand: form.brand.trim(),
    sku: form.sku.trim(),
    weight: Number(form.weight),
    dimensions: {
      width: Number(form.width),
      height: Number(form.height),
      depth: Number(form.depth),
    },
    warrantyInformation: form.warrantyInformation.trim(),
    shippingInformation: form.shippingInformation.trim(),
    availabilityStatus: form.availabilityStatus.trim(),
    returnPolicy: form.returnPolicy.trim(),
    minimumOrderQuantity: Number(form.minimumOrderQuantity),
    thumbnail: form.thumbnail.trim(),
    images: form.images.split(/[\n,]+/).map(image => image.trim()).filter(Boolean),
  }

  try {
    if (editing.value) {
      await store.updateProduct(editing.value.id, payload)
    } else {
      await store.createProduct(payload)
    }

    showModal.value = false
  } catch {
    // El store muestra el mensaje de error.
  }
}

async function eliminarProducto(producto: Product) {
  const confirmed = confirm(
    `¿Eliminar el producto ${producto.title}?`,
  )

  if (!confirmed) {
    return
  }

  try {
    await store.removeProduct(producto.id)
  } catch {
    // El store muestra el mensaje de error.
  }
}
</script>
