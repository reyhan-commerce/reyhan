export interface ReviewItem {
  id: number
  user_name: string
  rating: number
  comment: string
  created_at: string
  status?: string
  is_buyer?: boolean
  is_verified_purchase?: boolean
  admin_reply?: string | null
  strengths?: string[]
  weaknesses?: string[]
  criteria_ratings?: Record<string, number>
}

export interface ProductReviewsData {
  items: ReviewItem[]
  total_count: number
  average_rating: number
  criteria_averages?: Record<string, number>
}
