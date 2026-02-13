<script setup lang="ts">
const { status, progress, message, hasErrors } = useSync()

const statusText = computed(() => {
  switch (status.value) {
    case 'idle': return 'Ready to sync'
    case 'syncing': return 'Syncing...'
    case 'completed': return 'Sync completed'
    case 'error': return 'Sync failed'
    default: return 'Unknown'
  }
})

const statusClass = computed(() => `status-${status.value}`)
</script>

<template>
  <div class="sync-status" :class="statusClass">
    <div class="status-header">
      <span class="status-text">{{ statusText }}</span>
      <span v-if="status === 'syncing'" class="status-progress">
        {{ progress }}%
      </span>
    </div>

    <p v-if="message" class="status-message">{{ message }}</p>

    <div v-if="status === 'syncing'" class="progress-bar">
      <div class="progress-fill" :style="{ width: `${progress}%` }" />
    </div>

    <div v-if="hasErrors" class="error-indicator">
      Errors occurred during sync
    </div>
  </div>
</template>

<style scoped>
.sync-status {
  padding: 1rem;
  border-radius: 0.5rem;
  background-color: #f9fafb;
  border: 1px solid #e5e7eb;
}

.status-idle {
  border-color: #e5e7eb;
}

.status-syncing {
  border-color: #3b82f6;
  background-color: #eff6ff;
}

.status-completed {
  border-color: #10b981;
  background-color: #ecfdf5;
}

.status-error {
  border-color: #ef4444;
  background-color: #fef2f2;
}

.status-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.status-text {
  font-weight: 600;
  color: #374151;
}

.status-progress {
  font-family: monospace;
  color: #6b7280;
}

.status-message {
  font-size: 0.875rem;
  color: #6b7280;
  margin: 0.5rem 0 0;
}

.progress-bar {
  height: 0.5rem;
  background-color: #e5e7eb;
  border-radius: 9999px;
  overflow: hidden;
  margin-top: 0.75rem;
}

.progress-fill {
  height: 100%;
  background-color: #3b82f6;
  transition: width 0.3s ease;
}

.error-indicator {
  margin-top: 0.5rem;
  font-size: 0.875rem;
  color: #ef4444;
}
</style>
