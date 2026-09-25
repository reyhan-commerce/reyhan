export interface User {
  id: number
  first_name: string | null
  last_name: string | null
  full_name: string
  national_code: string | null
  mobile: string
  email: string | null
  avatar: string | null
  is_active: boolean
  created_at?: string
}

export interface CaptchaChallenge {
  key: string
  salt: string
  difficulty: number
}

export interface UserProfileCounts {
  orders: number
  wishlist: number
  addresses: number
}

export interface UserProfileResponse {
  user: User
  counts: UserProfileCounts
}

export interface UpdateUserProfilePayload {
  first_name?: string
  last_name?: string
  national_code?: string
  email?: string
}
