export interface ProvinceItem {
  id: number
  name: string
  slug: string
}

export interface CityItem {
  id: number
  province_id: number
  name: string
  slug: string
  postal_prefix?: string | null
}

export interface AddressItem {
  id: number
  recipient_name: string
  recipient_mobile: string
  province: ProvinceItem | null
  city: CityItem | null
  postal_code: string
  address_line: string
  building_number?: string | null
  unit?: string | null
  is_default: boolean
  full_address: string
  created_at: string
}

export interface CreateAddressPayload {
  province_id: number
  city_id: number
  recipient_name: string
  recipient_mobile: string
  postal_code: string
  address_line: string
  building_number?: string
  unit?: string
  is_default?: boolean
}

export interface GatewayItem {
  id: string
  name: string
  description: string
}

export interface OrderItem {
  id: number
  product_id: number
  product_variant_id: number
  product_name: string
  variant_title: string
  sku: string
  unit_price: number
  discount_amount: number
  final_price: number
  quantity: number
  total_price: number
  attributes?: Record<string, string> | null
  thumbnail?: string | null
}

export interface OrderShippingAddress {
  recipient_name: string
  recipient_mobile: string
  province_name?: string
  city_name?: string
  city?: string
  address_line?: string
  full_address?: string
  postal_code: string
  building_number?: string | null
  unit?: string | null
}

export interface ShippingTimeSlotDay {
  date: string
  jalali_date: string
  day_name: string
  slots: string[]
}

export interface AvailableShippingMethod {
  id: number
  name: string
  slug: string
  description?: string | null
  icon?: string | null
  shipping_fee: number
  is_free: boolean
  estimated_delivery_days?: string | null
  requires_time_slot: boolean
  time_slots: ShippingTimeSlotDay[]
}

export type OrderStatusColor = 'error' | 'primary' | 'secondary' | 'success' | 'info' | 'warning' | 'neutral'

export interface Order {
  id: number
  order_number: string
  status: 'pending' | 'processing' | 'shipped' | 'delivered' | 'cancelled' | 'refunded'
  status_label: string
  status_color?: OrderStatusColor | string
  shipping_method?: string | null
  shipping_method_id?: number | null
  shipping_method_title?: string | null
  shipping_method_icon?: string | null
  tracking_code?: string | null
  tracking_url?: string | null
  delivery_date?: string | null
  delivery_date_jalali?: string | null
  delivery_time_slot?: string | null
  shipping_address?: OrderShippingAddress | null
  items_subtotal: number
  discount_amount: number
  coupon_discount: number
  coupon_code?: string | null
  shipping_fee: number
  final_payable: number
  items_count: number
  wallet_paid_amount?: number
  is_corporate_invoice?: boolean
  corporate_data?: {
    company_name?: string
    economic_code?: string
    national_id?: string
    registration_number?: string
  } | null
  card_receipt?: {
    status?: string
    tracking_number?: string
    source_card_number?: string
    amount?: number
  } | null
  notes?: string | null
  paid_at?: string | null
  shipped_at?: string | null
  shipped_at_jalali?: string | null
  created_at: string
  created_at_jalali?: string | null
  items?: OrderItem[]
  user?: {
    name: string | null
    mobile: string | null
  } | null
}
