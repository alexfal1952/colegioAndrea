import type {
  CreateProductPayload,
  DeletedProduct,
  ListProductsParams,
  Product,
  ProductsResponse,
  UpdateMethod,
  UpdateProductPayload,
} from '~/types/product'

const PRODUCTS_URL = 'https://dummyjson.com/products'

export function useProductsService() {
  function list(params: ListProductsParams = {}): Promise<ProductsResponse> {
    return $fetch<ProductsResponse>(PRODUCTS_URL, {
      method: 'GET',
      query: params,
    })
  }

  function getById(id: number): Promise<Product> {
    return $fetch<Product>(`${PRODUCTS_URL}/${id}`, {
      method: 'GET',
    })
  }

  function create(payload: CreateProductPayload): Promise<Product> {
    return $fetch<Product>(`${PRODUCTS_URL}/add`, {
      method: 'POST',
      body: payload,
    })
  }

  function update(
    id: number,
    payload: UpdateProductPayload,
    method: UpdateMethod = 'PATCH',
  ): Promise<Product> {
    return $fetch<Product>(`${PRODUCTS_URL}/${id}`, {
      method,
      body: payload,
    })
  }

  function remove(id: number): Promise<DeletedProduct> {
    return $fetch<DeletedProduct>(`${PRODUCTS_URL}/${id}`, {
      method: 'DELETE',
    })
  }

  return {
    list,
    getById,
    create,
    update,
    remove,
  }
}
