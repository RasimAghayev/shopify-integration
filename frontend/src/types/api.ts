export interface ApiResponse<T> {
  data: T
  meta?: PaginationMeta
}

export interface PaginationMeta {
  total: number
  page: number
  perPage: number
  lastPage: number
}

export interface ApiError {
  error: string
  message: string
  errors?: Record<string, string[]>
}

export interface SyncRequest {
  shopifyId: string
  forceUpdate?: boolean
}

export interface BulkSyncRequest {
  shopifyIds: string[]
  skipDuplicates?: boolean
}

export interface BulkSyncResult {
  successCount: number
  failedCount: number
  skippedCount: number
  totalProcessed: number
  successRate: number
  errors: SyncError[]
}

export interface SyncError {
  shopifyId: string
  error: string
}

export interface UpdateInventoryRequest {
  sku: string
  quantity: number
  reason?: string
}
