<script setup lang="ts">
import type { Product } from '~/types/product'
import { formatPrice } from '~/utils/formatters'
import StatusBadge from "../ui/StatusBadge.vue";

const { products, loading, error } = useProducts()

const emit = defineEmits<{
  view: [product: Product]
  sync: [product: Product]
}>()
</script>

<template>
  <div class="product-list">
    <div v-if="error" class="error-banner">
      {{ error }}
    </div>

    <div v-if="loading" class="loading">
      Loading products...
    </div>

    <table v-else-if="products.length > 0" class="table" data-testid="product-table">
      <thead>
        <tr>
          <th>SKU</th>
          <th>Title</th>
          <th>Status</th>
          <th>Price</th>
          <th>Stock</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <tr
          v-for="product in products"
          :key="product.id"
          class="table-row"
          data-testid="product-row"
        >
          <td class="font-mono">{{ product.sku }}</td>
          <td>{{ product.title }}</td>
          <td>
            <StatusBadge :status="product.status" />
          </td>
          <td>{{ formatPrice(product.price) }}</td>
          <td :class="{ 'text-red': !product.inStock }">
            {{ product.inventory }}
          </td>
          <td class="actions">
            <button
              class="icon-btn"
              title="View Details"
              @click="emit('view', product)"
            >
              <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                <circle cx="12" cy="12" r="3"/>
              </svg>
            </button>
            <button
              class="icon-btn"
              title="Sync from Shopify"
              @click="emit('sync', product)"
            >
              <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M21 12a9 9 0 0 0-9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/>
                <path d="M3 3v5h5"/>
                <path d="M3 12a9 9 0 0 0 9 9 9.75 9.75 0 0 0 6.74-2.74L21 16"/>
                <path d="M16 21h5v-5"/>
              </svg>
            </button>
          </td>
        </tr>
      </tbody>
    </table>

    <div v-else class="empty-state">
      No products found. Start by syncing products from Shopify.
    </div>
  </div>
</template>

<style scoped>
.product-list {
  width: 100%;
}

.error-banner {
  padding: 1rem;
  background-color: #fee2e2;
  color: #991b1b;
  border-radius: 0.375rem;
  margin-bottom: 1rem;
}

.loading {
  text-align: center;
  padding: 2rem;
  color: #6b7280;
}

.table {
  width: 100%;
  border-collapse: collapse;
}

.table th,
.table td {
  padding: 0.75rem 1rem;
  text-align: left;
  border-bottom: 1px solid #e5e7eb;
}

.table th {
  font-weight: 600;
  color: #374151;
  background-color: #f9fafb;
}

.table-row:hover {
  background-color: #f9fafb;
}

.font-mono {
  font-family: monospace;
}

.text-red {
  color: #ef4444;
}

.actions {
  display: flex;
  gap: 0.25rem;
}

.icon-btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 32px;
  height: 32px;
  padding: 0;
  background: none;
  border: none;
  border-radius: 0.375rem;
  color: #6b7280;
  cursor: pointer;
  transition: all 0.2s;
}

.icon-btn:hover {
  background-color: #f3f4f6;
  color: #111827;
}

.icon-btn:active {
  background-color: #e5e7eb;
}

.empty-state {
  text-align: center;
  padding: 3rem;
  color: #6b7280;
}
</style>
