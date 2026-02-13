<script setup lang="ts">
import StatusBadge from "../../components/ui/StatusBadge.vue";
import Button from "../../components/ui/Button.vue";
import { formatPrice } from '~/utils/formatters'

definePageMeta({
  layout: 'default'
})

const route = useRoute()
const router = useRouter()
const sku = computed(() => route.params.sku as string)

const { currentProduct, loading, error, fetchProduct } = useProducts()

async function handleSync() {
  if (currentProduct.value?.shopifyId) {
    router.push(`/sync?shopifyId=${currentProduct.value.shopifyId}`)
  }
}

function goBack() {
  router.push('/products')
}

onMounted(() => {
  fetchProduct(sku.value)
})

watch(sku, (newSku) => {
  if (newSku) {
    fetchProduct(newSku)
  }
})
</script>

<template>
  <div class="product-detail">
    <div class="back-nav">
      <button class="back-button" @click="goBack">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M19 12H5M12 19l-7-7 7-7"/>
        </svg>
        Back to Products
      </button>
    </div>

    <div v-if="loading" class="loading">
      <div class="spinner"></div>
      Loading product...
    </div>

    <div v-else-if="error" class="error-state">
      <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <circle cx="12" cy="12" r="10"/>
        <line x1="12" y1="8" x2="12" y2="12"/>
        <line x1="12" y1="16" x2="12.01" y2="16"/>
      </svg>
      <h2>Product Not Found</h2>
      <p>{{ error }}</p>
      <Button @click="goBack">Go Back</Button>
    </div>

    <template v-else-if="currentProduct">
      <div class="product-header">
        <div class="product-info">
          <span class="product-sku">{{ currentProduct.sku }}</span>
          <h1 class="product-title">{{ currentProduct.title }}</h1>
          <StatusBadge :status="currentProduct.status" />
        </div>
        <div class="product-actions">
          <Button
            v-if="currentProduct.shopifyId"
            variant="secondary"
            @click="handleSync"
          >
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M21 12a9 9 0 0 0-9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/>
              <path d="M3 3v5h5"/>
              <path d="M3 12a9 9 0 0 0 9 9 9.75 9.75 0 0 0 6.74-2.74L21 16"/>
              <path d="M16 21h5v-5"/>
            </svg>
            Sync from Shopify
          </Button>
        </div>
      </div>

      <div class="product-content">
        <div class="product-main">
          <section class="section">
            <h2>Description</h2>
            <div
              v-if="currentProduct.description"
              class="description"
              v-html="currentProduct.description"
            />
            <p v-else class="no-description">No description available</p>
          </section>

          <section class="section">
            <h2>Variants</h2>
            <div v-if="currentProduct.variants?.length > 0" class="variants-list">
              <div
                v-for="variant in currentProduct.variants"
                :key="variant.id"
                class="variant-item"
              >
                <span class="variant-sku">{{ variant.sku }}</span>
                <span class="variant-price">{{ formatPrice(variant.price) }}</span>
                <span class="variant-stock">Stock: {{ variant.inventory }}</span>
              </div>
            </div>
            <p v-else class="no-variants">No variants</p>
          </section>
        </div>

        <aside class="product-sidebar">
          <div class="info-card">
            <h3>Pricing</h3>
            <div class="price-display">
              <span class="current-price">{{ formatPrice(currentProduct.price) }}</span>
            </div>
          </div>

          <div class="info-card">
            <h3>Inventory</h3>
            <div class="inventory-display">
              <span
                class="stock-count"
                :class="{ 'out-of-stock': !currentProduct.inStock }"
              >
                {{ currentProduct.inventory }} units
              </span>
              <span
                class="stock-status"
                :class="currentProduct.inStock ? 'in-stock' : 'out-of-stock'"
              >
                {{ currentProduct.inStock ? 'In Stock' : 'Out of Stock' }}
              </span>
            </div>
          </div>

          <div class="info-card">
            <h3>Shopify</h3>
            <div class="shopify-info">
              <span v-if="currentProduct.shopifyId" class="shopify-id">
                ID: {{ currentProduct.shopifyId }}
              </span>
              <span v-else class="no-shopify">Not linked to Shopify</span>
            </div>
          </div>

          <div class="info-card">
            <h3>Timestamps</h3>
            <div class="timestamps">
              <div class="timestamp-item">
                <span class="label">Created:</span>
                <span class="value">{{ new Date(currentProduct.createdAt).toLocaleDateString() }}</span>
              </div>
              <div class="timestamp-item">
                <span class="label">Updated:</span>
                <span class="value">{{ new Date(currentProduct.updatedAt).toLocaleDateString() }}</span>
              </div>
            </div>
          </div>
        </aside>
      </div>
    </template>
  </div>
</template>

<style scoped>
.product-detail {
  max-width: 1200px;
}

.back-nav {
  margin-bottom: 1.5rem;
}

.back-button {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.5rem 1rem;
  background: none;
  border: none;
  color: #6b7280;
  font-size: 0.875rem;
  cursor: pointer;
  border-radius: 0.375rem;
  transition: all 0.2s;
}

.back-button:hover {
  background-color: #f3f4f6;
  color: #111827;
}

.loading {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 4rem;
  color: #6b7280;
  gap: 1rem;
}

.spinner {
  width: 32px;
  height: 32px;
  border: 3px solid #e5e7eb;
  border-top-color: #3b82f6;
  border-radius: 50%;
  animation: spin 1s linear infinite;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

.error-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 4rem;
  color: #6b7280;
  gap: 1rem;
  text-align: center;
}

.error-state h2 {
  margin: 0;
  color: #111827;
}

.error-state p {
  margin: 0;
}

.product-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 2rem;
  gap: 1rem;
  flex-wrap: wrap;
}

.product-info {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.product-sku {
  font-family: monospace;
  font-size: 0.875rem;
  color: #6b7280;
}

.product-title {
  font-size: 1.875rem;
  font-weight: 700;
  color: #111827;
  margin: 0;
}

.product-actions {
  display: flex;
  gap: 0.75rem;
}

.product-actions :deep(button) {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
}

.product-content {
  display: grid;
  grid-template-columns: 1fr 320px;
  gap: 2rem;
}

@media (max-width: 768px) {
  .product-content {
    grid-template-columns: 1fr;
  }
}

.product-main {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.section {
  background: white;
  border: 1px solid #e5e7eb;
  border-radius: 0.5rem;
  padding: 1.5rem;
}

.section h2 {
  font-size: 1rem;
  font-weight: 600;
  color: #111827;
  margin: 0 0 1rem;
}

.description {
  color: #4b5563;
  line-height: 1.6;
}

.description :deep(p) {
  margin: 0 0 1rem;
}

.description :deep(p:last-child) {
  margin-bottom: 0;
}

.no-description,
.no-variants {
  color: #9ca3af;
  font-style: italic;
}

.variants-list {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.variant-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0.75rem;
  background: #f9fafb;
  border-radius: 0.375rem;
}

.variant-sku {
  font-family: monospace;
  font-size: 0.875rem;
}

.variant-price {
  font-weight: 600;
}

.variant-stock {
  color: #6b7280;
  font-size: 0.875rem;
}

.product-sidebar {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.info-card {
  background: white;
  border: 1px solid #e5e7eb;
  border-radius: 0.5rem;
  padding: 1.25rem;
}

.info-card h3 {
  font-size: 0.875rem;
  font-weight: 600;
  color: #6b7280;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  margin: 0 0 0.75rem;
}

.price-display {
  display: flex;
  flex-direction: column;
}

.current-price {
  font-size: 1.5rem;
  font-weight: 700;
  color: #111827;
}

.inventory-display {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}

.stock-count {
  font-size: 1.25rem;
  font-weight: 600;
  color: #111827;
}

.stock-count.out-of-stock {
  color: #ef4444;
}

.stock-status {
  font-size: 0.875rem;
  font-weight: 500;
}

.stock-status.in-stock {
  color: #10b981;
}

.stock-status.out-of-stock {
  color: #ef4444;
}

.shopify-info {
  display: flex;
  flex-direction: column;
}

.shopify-id {
  font-family: monospace;
  font-size: 0.875rem;
  color: #4b5563;
}

.no-shopify {
  color: #9ca3af;
  font-style: italic;
}

.timestamps {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.timestamp-item {
  display: flex;
  justify-content: space-between;
  font-size: 0.875rem;
}

.timestamp-item .label {
  color: #6b7280;
}

.timestamp-item .value {
  color: #111827;
}
</style>
