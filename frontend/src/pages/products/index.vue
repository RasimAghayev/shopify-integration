<script setup lang="ts">
import type { Product } from '~/types/product'
import Input from "../../components/ui/Input.vue";
import Button from "../../components/ui/Button.vue";

definePageMeta({
  layout: 'default'
})

const router = useRouter()
const { fetchProducts } = useProducts()

const searchQuery = ref('')

function handleView(product: Product) {
  router.push(`/products/${product.sku}`)
}

function handleSync(product: Product) {
  router.push(`/sync?shopifyId=${product.shopifyId}`)
}

onMounted(() => {
  fetchProducts()
})
</script>

<template>
  <div class="products-page">
    <header class="page-header">
      <h1 class="page-title">Products</h1>
      <div class="page-actions">
        <Input
          v-model="searchQuery"
          type="text"
          placeholder="Search products..."
          data-testid="search-input"
        />
        <NuxtLink to="/sync">
          <Button>Sync Products</Button>
        </NuxtLink>
      </div>
    </header>

    <ProductList
      @view="handleView"
      @sync="handleSync"
    />
  </div>
</template>

<style scoped>
.products-page {
  width: 100%;
}

.page-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 2rem;
  flex-wrap: wrap;
  gap: 1rem;
}

.page-title {
  font-size: 1.875rem;
  font-weight: 700;
  color: #111827;
  margin: 0;
}

.page-actions {
  display: flex;
  gap: 1rem;
  align-items: center;
}
</style>
