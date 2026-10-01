export interface LoyaltyTier {
  key: 'bronze' | 'silver' | 'gold'
  label: string
  color: string
  icon: string
  min_points: number
  next_points: number | null
  discount_percent: number
}

export interface LoyaltySummary {
  balance: number
  tier: LoyaltyTier
  progress: number
  total_earned: number
  total_spent: number
  point_value: number
  monetary_worth: number
}

export interface LoyaltyTransaction {
  id: number
  points: number
  type: 'signup_bonus' | 'order_reward' | 'review_bonus' | 'coupon_redemption' | 'manual_adjustment' | string
  description: string
  reference_id: string | null
  created_at: string
}

export interface LoyaltyTierInfo {
  key: string
  label: string
  min_points: number
  max_points: number | null
  icon: string
  perks: string[]
}

export interface RedeemResult {
  code: string
  discount_amount: number
  min_order_amount: number
  expires_at: string | null
  new_balance: number
}
