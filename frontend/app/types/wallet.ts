export type WalletTransactionType = 'deposit' | 'withdraw' | 'refund' | 'cashback' | 'admin_adjustment'

export interface WalletTransaction {
  id: number
  type: WalletTransactionType
  type_label: string
  type_color?: string
  is_credit?: boolean
  amount: number
  amount_toman?: number
  balance_after: number
  description: string
  order_id?: number | null
  order_number?: string | null
  created_at: string
  created_at_jalali?: string
}

export interface PaginatedWalletTransactions {
  data: WalletTransaction[]
  current_page: number
  last_page: number
  per_page: number
  total: number
}

export interface WalletData {
  balance: number
  balance_rial?: number
  balance_toman?: number
  transactions: WalletTransaction[] | PaginatedWalletTransactions
}
