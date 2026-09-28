export interface OrderReturnItem {
  id: number
  product_name: string
  variant_title?: string | null
  quantity: number
  price: number
  reason?: string | null
}

export interface OrderReturn {
  id: number
  return_number: string
  order_number: string
  status: 'pending' | 'approved' | 'rejected' | 'item_received' | 'refunded' | 'cancelled'
  status_label: string
  status_color?: string
  reason: string
  description?: string | null
  photos?: string[]
  refund_method: string
  refund_amount: number
  refund_amount_toman: number
  items_count?: number
  items?: OrderReturnItem[]
  admin_notes?: string | null
  created_at: string
  created_at_jalali?: string | null
}

export type TicketDepartment = 'support' | 'finance' | 'sales' | 'shipping' | 'complaints'
export type TicketPriority = 'low' | 'medium' | 'high' | 'urgent'
export type TicketStatus = 'open' | 'answered' | 'awaiting_reply' | 'closed'

export interface TicketMessage {
  id: number
  message: string
  is_staff: boolean
  author_name: string
  attachments?: string[]
  created_at: string
  created_at_jalali?: string | null
}

export interface SupportTicket {
  id: number
  ticket_number: string
  subject: string
  department: TicketDepartment
  department_label: string
  priority: TicketPriority
  priority_label: string
  priority_color?: string
  status: TicketStatus
  status_label: string
  status_color?: string
  order_number?: string | null
  last_reply_at?: string | null
  last_reply_at_jalali?: string | null
  messages?: TicketMessage[]
  created_at: string
  created_at_jalali?: string | null
}

export interface ProductAnswer {
  id: number
  answer: string
  author_name: string
  is_staff: boolean
  likes_count: number
  created_at: string
  created_at_jalali?: string | null
}

export interface ProductQuestion {
  id: number
  question: string
  author_name: string
  likes_count: number
  created_at: string
  created_at_jalali?: string | null
  answers: ProductAnswer[]
}

export interface PriceHistoryPoint {
  date: string
  date_jalali: string
  price: number
  price_toman: number
}

export interface ProductPriceHistoryData {
  product_id: number
  product_name: string
  current_price: number
  current_price_toman: number
  min_price: number
  min_price_toman: number
  max_price: number
  max_price_toman: number
  points: PriceHistoryPoint[]
}
