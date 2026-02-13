<script setup lang="ts">
</script>

<template>
  <div class="app-layout">
    <header class="app-header">
      <nav class="nav">
        <NuxtLink to="/" class="nav-brand">Shopify Integration</NuxtLink>
        <ul class="nav-links">
          <li>
            <NuxtLink to="/products" class="nav-link">Products</NuxtLink>
          </li>
          <li>
            <NuxtLink to="/sync" class="nav-link">Sync</NuxtLink>
          </li>
        </ul>
      </nav>
    </header>

    <main class="app-main">
      <slot />
    </main>

    <footer class="app-footer">
      <p>Shopify Integration System</p>
    </footer>
  </div>
</template>

<style scoped>
.app-layout {
  min-height: 100vh;
  display: flex;
  flex-direction: column;
  background-color: #f9fafb;
}

.app-header {
  background-color: white;
  border-bottom: 1px solid #e5e7eb;
  padding: 0 1.5rem;
}

.nav {
  display: flex;
  align-items: center;
  justify-content: space-between;
  height: 4rem;
  max-width: 1280px;
  margin: 0 auto;
}

.nav-brand {
  font-size: 1.25rem;
  font-weight: 700;
  color: #111827;
  text-decoration: none;
}

.nav-links {
  display: flex;
  gap: 2rem;
  list-style: none;
  margin: 0;
  padding: 0;
}

.nav-link {
  color: #6b7280;
  text-decoration: none;
  font-weight: 500;
  transition: color 0.15s ease;
}

.nav-link:hover,
.nav-link.router-link-active {
  color: #2563eb;
}

.app-main {
  flex: 1;
  padding: 2rem;
  max-width: 1280px;
  margin: 0 auto;
  width: 100%;
}

.app-footer {
  background-color: white;
  border-top: 1px solid #e5e7eb;
  padding: 1.5rem;
  text-align: center;
}

.app-footer p {
  margin: 0;
  color: #6b7280;
  font-size: 0.875rem;
}
</style>
