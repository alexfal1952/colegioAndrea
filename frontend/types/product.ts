export interface ProductDimensions {
  width: number
  height: number
  depth: number
}

export interface Product {
  id: number
  title: string
  description: string
  category: string
  price: number
  stock: number
  discountPercentage?: number
  rating?: number
  brand?: string
  tags?: string[]
  sku?: string
  weight?: number
  dimensions?: ProductDimensions
  warrantyInformation?: string
  shippingInformation?: string
  availabilityStatus?: string
  returnPolicy?: string
  minimumOrderQuantity?: number
  thumbnail?: string
  images?: string[]
}

export interface ProductsResponse {
  products: Product[]
  total: number
  skip: number
  limit: number
}

export interface ListProductsParams {
  limit?: number
  skip?: number
  select?: string
  sortBy?: string
  order?: 'asc' | 'desc'
}

export interface CreateProductPayload {
  title: string
  description?: string
  category?: string
  price?: number
  stock?: number
  discountPercentage?: number
  rating?: number
  tags?: string[]
  brand?: string
  sku?: string
  weight?: number
  dimensions?: ProductDimensions
  warrantyInformation?: string
  shippingInformation?: string
  availabilityStatus?: string
  returnPolicy?: string
  minimumOrderQuantity?: number
  thumbnail?: string
  images?: string[]
}

export type UpdateProductPayload = Partial<CreateProductPayload>
export type UpdateMethod = 'PUT' | 'PATCH'

export interface DeletedProduct extends Product {
  isDeleted: boolean
  deletedOn: string
}
