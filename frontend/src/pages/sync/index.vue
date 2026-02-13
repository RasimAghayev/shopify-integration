<script setup lang="ts">
import SyncStatus from "../../components/sync/SyncStatus.vue";
import Input from "../../components/ui/Input.vue";
import Button from "../../components/ui/Button.vue";
import Modal from "../../components/ui/Modal.vue";

definePageMeta({
  layout: 'default'
})

const { isSyncing, syncProduct, reset } = useSync()

const shopifyId = ref('')
const showBulkModal = ref(false)
const bulkIds = ref('')

const isValidId = computed(() => /^\d+$/.test(shopifyId.value.trim()))

async function handleSingleSync() {
  if (!shopifyId.value.trim()) return

  try {
    await syncProduct(shopifyId.value.trim())
    shopifyId.value = ''
  } catch (e) {
    console.error(e)
  }
}

async function handleBulkSync() {
  const ids = bulkIds.value
    .split('\n')
    .map(id => id.trim())
    .filter(id => /^\d+$/.test(id))

  if (ids.length === 0) return

  try {
    const { syncBulk } = useSync()
    await syncBulk(ids)
    showBulkModal.value = false
    bulkIds.value = ''
  } catch (e) {
    console.error(e)
  }
}

onMounted(() => {
  const route = useRoute()
  if (route.query.shopifyId) {
    shopifyId.value = route.query.shopifyId as string
  }
})

onUnmounted(() => {
  reset()
})
</script>

<template>
  <div class="sync-page">
    <h1 class="page-title">Product Sync</h1>

    <SyncStatus />

    <section class="sync-section">
      <h2>Single Product Sync</h2>
      <p class="section-description">
        Enter a Shopify product ID to sync a single product.
      </p>
      <div class="sync-form">
        <Input
          v-model="shopifyId"
          type="text"
          placeholder="Enter Shopify Product ID"
          data-testid="shopify-id-input"
          :disabled="isSyncing"
        />
        <Button
          :loading="isSyncing"
          :disabled="!isValidId"
          data-testid="sync-submit"
          @click="handleSingleSync"
        >
          Sync Product
        </Button>
      </div>
    </section>

    <section class="sync-section">
      <h2>Bulk Sync</h2>
      <p class="section-description">
        Sync multiple products from your Shopify store at once.
      </p>
      <Button
        variant="secondary"
        :disabled="isSyncing"
        data-testid="sync-button"
        @click="showBulkModal = true"
      >
        Start Bulk Sync
      </Button>
    </section>

    <Modal
      :open="showBulkModal"
      title="Bulk Product Sync"
      size="md"
      @close="showBulkModal = false"
    >
      <p>Enter Shopify product IDs (one per line):</p>
      <textarea
        v-model="bulkIds"
        class="bulk-textarea"
        placeholder="123456789&#10;987654321&#10;..."
        rows="6"
      />

      <template #footer>
        <Button variant="ghost" @click="showBulkModal = false">
          Cancel
        </Button>
        <Button :loading="isSyncing" @click="handleBulkSync">
          Start Sync
        </Button>
      </template>
    </Modal>
  </div>
</template>

<style scoped>
.sync-page {
  max-width: 640px;
}

.page-title {
  font-size: 1.875rem;
  font-weight: 700;
  color: #111827;
  margin: 0 0 2rem;
}

.sync-section {
  background-color: white;
  border: 1px solid #e5e7eb;
  border-radius: 0.5rem;
  padding: 1.5rem;
  margin-top: 1.5rem;
}

.sync-section h2 {
  font-size: 1.125rem;
  font-weight: 600;
  color: #111827;
  margin: 0 0 0.5rem;
}

.section-description {
  color: #6b7280;
  font-size: 0.875rem;
  margin: 0 0 1rem;
}

.sync-form {
  display: flex;
  gap: 0.75rem;
}

.bulk-textarea {
  width: 100%;
  padding: 0.75rem;
  border: 1px solid #d1d5db;
  border-radius: 0.375rem;
  font-family: monospace;
  font-size: 0.875rem;
  resize: vertical;
  margin-top: 1rem;
}

.bulk-textarea:focus {
  outline: none;
  border-color: #2563eb;
  box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
}
</style>
