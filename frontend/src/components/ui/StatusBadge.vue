<script setup lang="ts">
import type { ProductStatus } from '~/types/product'

interface Props {
  status: ProductStatus | 'syncing' | 'error'
}

const props = defineProps<Props>()

const statusConfig = {
  active: { class: 'badge-success', label: 'Active' },
  draft: { class: 'badge-warning', label: 'Draft' },
  archived: { class: 'badge-gray', label: 'Archived' },
  syncing: { class: 'badge-info', label: 'Syncing' },
  error: { class: 'badge-error', label: 'Error' }
}

const config = computed(() => statusConfig[props.status])
</script>

<template>
  <span :class="['badge', config.class]">
    {{ config.label }}
  </span>
</template>

<style scoped>
.badge {
  display: inline-flex;
  align-items: center;
  padding: 0.125rem 0.625rem;
  font-size: 0.75rem;
  font-weight: 500;
  border-radius: 9999px;
}

.badge-success {
  background-color: #d1fae5;
  color: #065f46;
}

.badge-warning {
  background-color: #fef3c7;
  color: #92400e;
}

.badge-gray {
  background-color: #e5e7eb;
  color: #374151;
}

.badge-info {
  background-color: #dbeafe;
  color: #1e40af;
}

.badge-error {
  background-color: #fee2e2;
  color: #991b1b;
}
</style>
