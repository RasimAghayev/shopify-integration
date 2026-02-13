import { defineStore } from 'pinia'
import type { SyncStatus, SyncError, SyncHistoryItem } from '~/types/sync'
import type { BulkSyncResult } from '~/types/api'

interface SyncState {
  status: SyncStatus
  progress: number
  message: string | null
  errors: SyncError[]
  history: SyncHistoryItem[]
}

export const useSyncStore = defineStore('sync', {
  state: (): SyncState => ({
    status: 'idle',
    progress: 0,
    message: null,
    errors: [],
    history: []
  }),

  getters: {
    isSyncing: (state): boolean => state.status === 'syncing',
    hasErrors: (state): boolean => state.errors.length > 0,
    isCompleted: (state): boolean => state.status === 'completed'
  },

  actions: {
    startSync(message?: string) {
      this.status = 'syncing'
      this.progress = 0
      this.message = message ?? 'Starting sync...'
      this.errors = []
    },

    updateProgress(progress: number, message?: string) {
      this.progress = Math.min(100, Math.max(0, progress))
      if (message) {
        this.message = message
      }
    },

    completeSync(message?: string) {
      this.status = 'completed'
      this.progress = 100
      this.message = message ?? 'Sync completed successfully'
    },

    failSync(message: string, errors?: SyncError[]) {
      this.status = 'error'
      this.message = message
      if (errors) {
        this.errors = errors
      }
    },

    addError(error: SyncError) {
      this.errors.push(error)
    },

    reset() {
      this.status = 'idle'
      this.progress = 0
      this.message = null
      this.errors = []
    },

    async syncSingleProduct(shopifyId: string) {
      this.startSync(`Syncing product ${shopifyId}...`)

      try {
        const config = useRuntimeConfig()
        await $fetch(`${config.public.apiBase}/v1/sync/product`, {
          method: 'POST',
          body: { shopify_id: shopifyId }
        })

        this.completeSync('Product synced successfully')
      } catch (e) {
        this.failSync('Failed to sync product')
        throw e
      }
    },

    async syncBulk(shopifyIds: string[]) {
      this.startSync(`Syncing ${shopifyIds.length} products...`)

      try {
        const config = useRuntimeConfig()
        const result = await $fetch<BulkSyncResult>(
          `${config.public.apiBase}/v1/sync/bulk/immediate`,
          {
            method: 'POST',
            body: { shopify_ids: shopifyIds }
          }
        )

        if (result.failedCount > 0) {
          this.failSync(
            `Sync completed with ${result.failedCount} errors`,
            result.errors.map(e => ({
              ...e,
              timestamp: new Date().toISOString()
            }))
          )
        } else {
          this.completeSync(
            `Successfully synced ${result.successCount} products`
          )
        }

        return result
      } catch (e) {
        this.failSync('Bulk sync failed')
        throw e
      }
    },

    async queueBulkSync(shopifyIds: string[]) {
      try {
        const config = useRuntimeConfig()
        const result = await $fetch<{ message: string; count: number }>(
          `${config.public.apiBase}/v1/sync/bulk`,
          {
            method: 'POST',
            body: { shopify_ids: shopifyIds }
          }
        )

        this.message = result.message
        return result
      } catch (e) {
        this.failSync('Failed to queue sync jobs')
        throw e
      }
    }
  }
})
