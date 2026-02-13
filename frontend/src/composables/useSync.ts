import { useSyncStore } from '~/stores/sync'

export function useSync() {
  const store = useSyncStore()

  const status = computed(() => store.status)
  const progress = computed(() => store.progress)
  const message = computed(() => store.message)
  const errors = computed(() => store.errors)
  const isSyncing = computed(() => store.isSyncing)
  const hasErrors = computed(() => store.hasErrors)

  async function syncProduct(shopifyId: string) {
    await store.syncSingleProduct(shopifyId)
  }

  async function syncBulk(shopifyIds: string[]) {
    return store.syncBulk(shopifyIds)
  }

  async function queueBulkSync(shopifyIds: string[]) {
    return store.queueBulkSync(shopifyIds)
  }

  function reset() {
    store.reset()
  }

  return {
    status,
    progress,
    message,
    errors,
    isSyncing,
    hasErrors,
    syncProduct,
    syncBulk,
    queueBulkSync,
    reset
  }
}
