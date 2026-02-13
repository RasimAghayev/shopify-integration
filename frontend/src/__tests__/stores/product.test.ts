import { describe, it, expect, beforeEach, vi } from 'vitest'
import { setActivePinia, createPinia } from 'pinia'
import { useProductStore } from '~/stores/product'
import type { Product } from '~/types/product'

const mockProducts: Product[] = [
  {
    id: 1,
    sku: 'TEST-001',
    title: 'Test Product 1',
    description: 'Description 1',
    status: 'active',
    price: { amount: 1999, currency: 'USD', formatted: '$19.99' },
    inventory: 10,
    inStock: true,
    shopifyId: '123',
    variants: [],
    createdAt: '2024-01-01T00:00:00Z',
    updatedAt: '2024-01-01T00:00:00Z'
  },
  {
    id: 2,
    sku: 'TEST-002',
    title: 'Test Product 2',
    description: 'Description 2',
    status: 'draft',
    price: { amount: 2999, currency: 'USD', formatted: '$29.99' },
    inventory: 0,
    inStock: false,
    shopifyId: '456',
    variants: [],
    createdAt: '2024-01-01T00:00:00Z',
    updatedAt: '2024-01-01T00:00:00Z'
  }
]

describe('useProductStore', () => {
  beforeEach(() => {
    setActivePinia(createPinia())
  })

  describe('initial state', () => {
    it('has empty products array', () => {
      const store = useProductStore()
      expect(store.products).toEqual([])
    })

    it('has null current product', () => {
      const store = useProductStore()
      expect(store.currentProduct).toBeNull()
    })

    it('has loading false', () => {
      const store = useProductStore()
      expect(store.loading).toBe(false)
    })

    it('has null error', () => {
      const store = useProductStore()
      expect(store.error).toBeNull()
    })
  })

  describe('getters', () => {
    describe('filteredProducts', () => {
      it('returns all products when no filters', () => {
        const store = useProductStore()
        store.products = mockProducts

        expect(store.filteredProducts).toHaveLength(2)
      })

      it('filters by search term in sku', () => {
        const store = useProductStore()
        store.products = mockProducts
        store.filters = { search: 'TEST-001' }

        expect(store.filteredProducts).toHaveLength(1)
        expect(store.filteredProducts[0].sku).toBe('TEST-001')
      })

      it('filters by search term in title', () => {
        const store = useProductStore()
        store.products = mockProducts
        store.filters = { search: 'Product 2' }

        expect(store.filteredProducts).toHaveLength(1)
        expect(store.filteredProducts[0].title).toBe('Test Product 2')
      })

      it('filters by status', () => {
        const store = useProductStore()
        store.products = mockProducts
        store.filters = { status: 'active' }

        expect(store.filteredProducts).toHaveLength(1)
        expect(store.filteredProducts[0].status).toBe('active')
      })

      it('filters by inStock', () => {
        const store = useProductStore()
        store.products = mockProducts
        store.filters = { inStock: true }

        expect(store.filteredProducts).toHaveLength(1)
        expect(store.filteredProducts[0].inStock).toBe(true)
      })

      it('combines multiple filters', () => {
        const store = useProductStore()
        store.products = mockProducts
        store.filters = { search: 'Test', status: 'active' }

        expect(store.filteredProducts).toHaveLength(1)
        expect(store.filteredProducts[0].sku).toBe('TEST-001')
      })
    })

    describe('totalProducts', () => {
      it('returns 0 when no pagination', () => {
        const store = useProductStore()
        expect(store.totalProducts).toBe(0)
      })

      it('returns total from pagination', () => {
        const store = useProductStore()
        store.pagination = { total: 100, page: 1, perPage: 10, lastPage: 10 }
        expect(store.totalProducts).toBe(100)
      })
    })

    describe('hasProducts', () => {
      it('returns false when no products', () => {
        const store = useProductStore()
        expect(store.hasProducts).toBe(false)
      })

      it('returns true when has products', () => {
        const store = useProductStore()
        store.products = mockProducts
        expect(store.hasProducts).toBe(true)
      })
    })
  })

  describe('actions', () => {
    describe('setFilters', () => {
      it('sets filters', () => {
        const store = useProductStore()
        store.setFilters({ search: 'test' })

        expect(store.filters.search).toBe('test')
      })

      it('merges with existing filters', () => {
        const store = useProductStore()
        store.filters = { search: 'test' }
        store.setFilters({ status: 'active' })

        expect(store.filters.search).toBe('test')
        expect(store.filters.status).toBe('active')
      })
    })

    describe('clearFilters', () => {
      it('clears all filters', () => {
        const store = useProductStore()
        store.filters = { search: 'test', status: 'active' }
        store.clearFilters()

        expect(store.filters).toEqual({})
      })
    })

    describe('clearError', () => {
      it('clears error', () => {
        const store = useProductStore()
        store.error = 'Some error'
        store.clearError()

        expect(store.error).toBeNull()
      })
    })
  })
})
