<script setup lang="ts">
import type { Product } from '~/types/product'
import { formatPrice } from '~/utils/formatters'
import StatusBadge from "../ui/StatusBadge.vue";

interface Props {
  product: Product
}

const props = defineProps<Props>()

const emit = defineEmits<{
  view: [product: Product]
  sync: [product: Product]
}>()
</script>

<template>
  <div class="product-card" data-testid="product-card">
    <div class="product-header">
      <span class="product-sku">{{ product.sku }}</span>
      <StatusBadge :status="product.status" />
    </div>

    <h3 class="product-title">{{ product.title }}</h3>

    <p v-if="product.description" class="product-description">
      {{ product.description }}
    </p>

    <div class="product-meta">
      <span class="product-price">{{ formatPrice(product.price) }}</span>
      <span :class="['product-stock', { 'out-of-stock': !product.inStock }]">
        Stock: {{ product.inventory }}
      </span>
    </div>

    <div class="product-actions">
      <button
        class="icon-btn icon-btn-primary"
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
    </div>
  </div>
</template>

<style scoped>
.product-card {
  background-color: white;
  border: 1px solid #e5e7eb;
  border-radius: 0.5rem;
  padding: 1.25rem;
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.product-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.product-sku {
  font-family: monospace;
  font-size: 0.875rem;
  color: #6b7280;
}

.product-title {
  font-size: 1rem;
  font-weight: 600;
  color: #111827;
  margin: 0;
}

.product-description {
  font-size: 0.875rem;
  color: #6b7280;
  margin: 0;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.product-meta {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.product-price {
  font-weight: 600;
  color: #111827;
}

.product-stock {
  font-size: 0.875rem;
  color: #6b7280;
}

.out-of-stock {
  color: #ef4444;
}

.product-actions {
  display: flex;
  gap: 0.5rem;
  margin-top: 0.5rem;
}

.icon-btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 36px;
  height: 36px;
  padding: 0;
  background: none;
  border: 1px solid #e5e7eb;
  border-radius: 0.375rem;
  color: #6b7280;
  cursor: pointer;
  transition: all 0.2s;
}

.icon-btn:hover {
  background-color: #f3f4f6;
  color: #111827;
  border-color: #d1d5db;
}

.icon-btn:active {
  background-color: #e5e7eb;
}

.icon-btn-primary {
  background-color: #3b82f6;
  border-color: #3b82f6;
  color: white;
}

.icon-btn-primary:hover {
  background-color: #2563eb;
  border-color: #2563eb;
  color: white;
}
</style>
