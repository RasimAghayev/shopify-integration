<script setup lang="ts">
import Button from "../components/ui/Button.vue";

definePageMeta({
  layout: 'default'
})

const { products, fetchProducts } = useProducts()

const stats = ref({
  totalProducts: 0,
  syncedToday: 0,
  lowStock: 0
})

onMounted(async () => {
  await fetchProducts()
  stats.value.totalProducts = products.value.length
  stats.value.lowStock = products.value.filter(p => p.inventory < 10).length
})
</script>

<template>
  <div class="home-page">
    <h1 class="page-title">Dashboard</h1>

    <div class="stats-grid">
      <div class="stat-card">
        <h3>Total Products</h3>
        <p class="stat-value">{{ stats.totalProducts }}</p>
      </div>

      <div class="stat-card">
        <h3>Synced Today</h3>
        <p class="stat-value">{{ stats.syncedToday }}</p>
      </div>

      <div class="stat-card">
        <h3>Low Stock</h3>
        <p class="stat-value warning">{{ stats.lowStock }}</p>
      </div>
    </div>

    <section class="quick-actions">
      <h2>Quick Actions</h2>
      <div class="action-buttons">
        <NuxtLink to="/sync">
          <Button variant="primary">Sync Products</Button>
        </NuxtLink>
        <NuxtLink to="/products">
          <Button variant="secondary">View Products</Button>
        </NuxtLink>
      </div>
    </section>
  </div>
</template>

<style scoped>
.home-page {
  max-width: 1024px;
}

.page-title {
  font-size: 1.875rem;
  font-weight: 700;
  color: #111827;
  margin: 0 0 2rem;
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 1.5rem;
  margin-bottom: 3rem;
}

.stat-card {
  background-color: white;
  border: 1px solid #e5e7eb;
  border-radius: 0.5rem;
  padding: 1.5rem;
}

.stat-card h3 {
  font-size: 0.875rem;
  font-weight: 500;
  color: #6b7280;
  margin: 0 0 0.5rem;
}

.stat-value {
  font-size: 2rem;
  font-weight: 700;
  color: #111827;
  margin: 0;
}

.stat-value.warning {
  color: #f59e0b;
}

.quick-actions {
  background-color: white;
  border: 1px solid #e5e7eb;
  border-radius: 0.5rem;
  padding: 1.5rem;
}

.quick-actions h2 {
  font-size: 1.125rem;
  font-weight: 600;
  color: #111827;
  margin: 0 0 1rem;
}

.action-buttons {
  display: flex;
  gap: 1rem;
}
</style>
