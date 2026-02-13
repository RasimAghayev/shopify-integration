export type SyncStatus = 'idle' | 'syncing' | 'completed' | 'error'

export interface SyncState {
  status: SyncStatus
  progress: number
  message: string | null
  errors: SyncError[]
}

export interface SyncError {
  shopifyId: string
  error: string
  timestamp: string
}

export interface SyncHistoryItem {
  id: number
  type: 'single' | 'bulk'
  productsCount: number
  status: 'completed' | 'failed' | 'partial'
  startedAt: string
  completedAt: string | null
  errors: SyncError[]
}
