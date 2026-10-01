export interface CartProduct {
  id: number
  name: string
  slug: string
  thumbnail?: string | null
  brand?: string | null
}

export interface CartVariant {
  id: number
  sku: string
  title: string
  price: number
  compare_at_price: number | null
  stock: number
  is_in_stock: boolean
  is_low_stock: boolean
  product: CartProduct | null
}

export interface CartItem {
  id: number
  quantity: number
  unit_price: number
  subtotal: number
  original_subtotal: number
  discount_amount: number
  variant: CartVariant | null
}

export interface AppliedCoupon {
  code: string
  title: string | null
  type: string
  value: number
}

export interface CartPricing {
  original_items_subtotal: number
  items_subtotal: number
  catalog_discount: number
  coupon_discount: number
  total_discount: number
  tax_amount: number
  shipping_fee: number
  is_free_shipping: boolean
  free_shipping_threshold: number
  remaining_for_free_shipping: number
  free_shipping_progress: number
  final_payable: number
  total_items_count: number
  total_weight_grams: number
  applied_coupon: AppliedCoupon | null
}

export interface CartData {
  id: number
  session_id: string | null
  items_count: number
  items: CartItem[]
  pricing: CartPricing
}
