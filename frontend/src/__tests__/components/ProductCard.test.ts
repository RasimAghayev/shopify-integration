import { describe, it, expect } from 'vitest'
import { mount } from '@vue/test-utils'
import { defineComponent } from 'vue'
import ProductCard from '~/components/products/ProductCard.vue'
import type { Product } from '~/types/product'

const StatusBadgeStub = defineComponent({
  props: ['status'],
  template: '<span class="status-badge">{{ status }}</span>'
})

const mockProduct: Product = {
  id: 1,
  sku: 'TEST-001',
  title: 'Test Product',
  description: 'Test Description',
  status: 'active',
  price: { amount: 1999, currency: 'USD', formatted: '$19.99' },
  inventory: 10,
  inStock: true,
  shopifyId: '123456',
  variants: [],
  createdAt: '2024-01-01T00:00:00Z',
  updatedAt: '2024-01-01T00:00:00Z'
}

const globalStubs = {
  stubs: {
    StatusBadge: StatusBadgeStub
  }
}

describe('ProductCard', () => {
  it('renders product SKU', () => {
    const wrapper = mount(ProductCard, {
      props: { product: mockProduct },
      global: globalStubs
    })

    expect(wrapper.text()).toContain('TEST-001')
  })

  it('renders product title', () => {
    const wrapper = mount(ProductCard, {
      props: { product: mockProduct },
      global: globalStubs
    })

    expect(wrapper.text()).toContain('Test Product')
  })

  it('renders product description', () => {
    const wrapper = mount(ProductCard, {
      props: { product: mockProduct },
      global: globalStubs
    })

    expect(wrapper.text()).toContain('Test Description')
  })

  it('renders formatted price', () => {
    const wrapper = mount(ProductCard, {
      props: { product: mockProduct },
      global: globalStubs
    })

    expect(wrapper.text()).toContain('$19.99')
  })

  it('renders inventory count', () => {
    const wrapper = mount(ProductCard, {
      props: { product: mockProduct },
      global: globalStubs
    })

    expect(wrapper.text()).toContain('10')
  })

  it('emits view event when view button clicked', async () => {
    const wrapper = mount(ProductCard, {
      props: { product: mockProduct },
      global: globalStubs
    })

    const viewButton = wrapper.find('[title="View Details"]')
    await viewButton.trigger('click')

    expect(wrapper.emitted('view')).toBeTruthy()
    expect(wrapper.emitted('view')![0]).toEqual([mockProduct])
  })

  it('emits sync event when sync button clicked', async () => {
    const wrapper = mount(ProductCard, {
      props: { product: mockProduct },
      global: globalStubs
    })

    const syncButton = wrapper.find('[title="Sync from Shopify"]')
    await syncButton.trigger('click')

    expect(wrapper.emitted('sync')).toBeTruthy()
    expect(wrapper.emitted('sync')![0]).toEqual([mockProduct])
  })

  it('applies out-of-stock class when not in stock', () => {
    const outOfStockProduct = { ...mockProduct, inStock: false, inventory: 0 }
    const wrapper = mount(ProductCard, {
      props: { product: outOfStockProduct },
      global: globalStubs
    })

    expect(wrapper.find('.out-of-stock').exists()).toBe(true)
  })

  it('does not apply out-of-stock class when in stock', () => {
    const wrapper = mount(ProductCard, {
      props: { product: mockProduct },
      global: globalStubs
    })

    expect(wrapper.find('.out-of-stock').exists()).toBe(false)
  })

  it('has correct test id', () => {
    const wrapper = mount(ProductCard, {
      props: { product: mockProduct },
      global: globalStubs
    })

    expect(wrapper.find('[data-testid="product-card"]').exists()).toBe(true)
  })
})
