import { defineStore } from 'pinia'
import type { Product, ProductFilters } from '~/types/product'
import type { PaginationMeta, ApiResponse } from '~/types/api'

interface ProductState {
  products: Product[]
  currentProduct: Product | null
  loading: boolean
  error: string | null
  pagination: PaginationMeta | null
  filters: ProductFilters
}

export const useProductStore = defineStore('product', {
  state: (): ProductState => ({
    products: [],
    currentProduct: null,
    loading: false,
    error: null,
    pagination: null,
    filters: {}
  }),

  getters: {
    filteredProducts: (state): Product[] => {
      let result = state.products

      if (state.filters.search) {
        const search = state.filters.search.toLowerCase()
        result = result.filter(p =>
          p.sku.toLowerCase().includes(search) ||
          p.title.toLowerCase().includes(search)
        )
      }

      if (state.filters.status) {
        result = result.filter(p => p.status === state.filters.status)
      }

      if (state.filters.inStock !== undefined) {
        result = result.filter(p => p.inStock === state.filters.inStock)
      }

      return result
    },

    totalProducts: (state): number => state.pagination?.total ?? 0,

    hasProducts: (state): boolean => state.products.length > 0
  },

  actions: {
    async fetchProducts(page = 1, perPage = 10) {
      this.loading = true
      this.error = null

      try {
        const config = useRuntimeConfig()
        const response = await $fetch<ApiResponse<Product[]>>(
          `${config.public.apiBase}/v1/products`,
          {
            params: { page, per_page: perPage }
          }
        )

        this.products = response.data
        this.pagination = response.meta ?? null
      } catch (e) {
        this.error = 'Failed to fetch products'
        console.error(e)
      } finally {
        this.loading = false
      }
    },

    async fetchProduct(sku: string) {
      this.loading = true
      this.error = null

      try {
        const config = useRuntimeConfig()
        const response = await $fetch<{ data: Product }>(
          `${config.public.apiBase}/v1/products/${sku}`
        )

        this.currentProduct = response.data
      } catch (e) {
        this.error = 'Failed to fetch product'
        console.error(e)
      } finally {
        this.loading = false
      }
    },

    async syncProduct(shopifyId: string): Promise<Product> {
      this.loading = true
      this.error = null

      try {
        const config = useRuntimeConfig()
        const response = await $fetch<Product>(
          `${config.public.apiBase}/v1/sync/product`,
          {
            method: 'POST',
            body: { shopify_id: shopifyId }
          }
        )

        this.products.unshift(response)
        return response
      } catch (e) {
        this.error = 'Failed to sync product'
        throw e
      } finally {
        this.loading = false
      }
    },

    async deleteProduct(sku: string) {
      try {
        const config = useRuntimeConfig()
        await $fetch(`${config.public.apiBase}/v1/products/${sku}`, {
          method: 'DELETE'
        })

        this.products = this.products.filter(p => p.sku !== sku)
      } catch (e) {
        this.error = 'Failed to delete product'
        throw e
      }
    },

    setFilters(filters: ProductFilters) {
      this.filters = { ...this.filters, ...filters }
    },

    clearFilters() {
      this.filters = {}
    },

    clearError() {
      this.error = null
    }
  }
})
