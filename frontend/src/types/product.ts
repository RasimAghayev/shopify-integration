export interface Product {
  id: number
  sku: string
  title: string
  description: string | null
  status: ProductStatus
  price: Price
  inventory: number
  inStock: boolean
  shopifyId: string | null
  variants: ProductVariant[]
  createdAt: string
  updatedAt: string
}

export interface ProductVariant {
  id: number
  sku: string
  price: Price
  inventory: number
}

export interface Price {
  amount: number
  formatted: string
  currency: string
}

export type ProductStatus = 'active' | 'draft' | 'archived'

export interface ProductFilters {
  search?: string
  status?: ProductStatus
  inStock?: boolean
}

export interface CreateProductInput {
  sku: string
  title: string
  description?: string
  price: number
  status?: ProductStatus
}

export interface UpdateProductInput {
  title?: string
  description?: string
  price?: number
  status?: ProductStatus
  inventory?: number
}
