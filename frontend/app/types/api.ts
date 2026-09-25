export interface ApiResponse<T = unknown> {
  success: boolean
  message?: string
  data?: T
  errors?: Record<string, string[]>
}

export interface ApiErrorResponse {
  success: boolean
  message: string
  errors?: Record<string, string[]>
}

export interface PaginationMeta {
  current_page: number
  last_page: number
  per_page: number
  total: number
  from?: number | null
  to?: number | null
}

export interface PaginatedData<T> {
  items: T[]
  meta: PaginationMeta
}

export interface PaginatedResponse<T> {
  success: boolean
  data: PaginatedData<T>
  message?: string
}

export interface LaravelPaginatedResponse<T> {
  data: T[]
  meta?: PaginationMeta
  links?: Record<string, string | null>
  success?: boolean
  message?: string
}
