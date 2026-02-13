import { useProductStore } from '~/stores/product'
import type { Product, ProductFilters } from '~/types/product'

export function useProducts() {
  const store = useProductStore()

  const products = computed(() => store.filteredProducts)
  const loading = computed(() => store.loading)
  const error = computed(() => store.error)
  const pagination = computed(() => store.pagination)
  const currentProduct = computed(() => store.currentProduct)

  async function fetchProducts(page?: number, perPage?: number) {
    await store.fetchProducts(page, perPage)
  }

  async function fetchProduct(sku: string) {
    await store.fetchProduct(sku)
  }

  async function syncProduct(shopifyId: string): Promise<Product> {
    return store.syncProduct(shopifyId)
  }

  async function deleteProduct(sku: string) {
    await store.deleteProduct(sku)
  }

  function setFilters(filters: ProductFilters) {
    store.setFilters(filters)
  }

  function clearFilters() {
    store.clearFilters()
  }

  function clearError() {
    store.clearError()
  }

  return {
    products,
    loading,
    error,
    pagination,
    currentProduct,
    fetchProducts,
    fetchProduct,
    syncProduct,
    deleteProduct,
    setFilters,
    clearFilters,
    clearError
  }
}
