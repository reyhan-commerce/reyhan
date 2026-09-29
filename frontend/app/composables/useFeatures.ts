import { useApi, type ApiResponse } from './useApi'

export interface FeaturesMap {
  reviews?: boolean
  coupons?: boolean
  wishlist?: boolean
  brands?: boolean
  stock_alerts?: boolean
  comparison?: boolean
  blog?: boolean
  loyalty?: boolean
  wallet?: boolean
  referral?: boolean
  returns?: boolean
  faq?: boolean
  tickets?: boolean
  questions?: boolean
  [key: string]: boolean | undefined
}

export const useFeatures = () => {
  const api = useApi()
  const features = useState<FeaturesMap>('app_features', () => ({
    reviews: true,
    coupons: true,
    wishlist: true,
    brands: true,
    stock_alerts: true,
    comparison: true,
    blog: true,
    loyalty: true,
    wallet: true,
    referral: true,
    returns: true,
    faq: true,
    tickets: true,
    questions: true
  }))
  const isLoading = useState<boolean>('app_features_loading', () => false)

  const fetchFeatures = async (): Promise<FeaturesMap> => {
    try {
      isLoading.value = true
      const res = await api<ApiResponse<FeaturesMap>>('/app/features')
      if (res.success && res.data) {
        features.value = { ...features.value, ...res.data }
      }
    } catch {
      // Keep default values on transient network error
    } finally {
      isLoading.value = false
    }
    return features.value
  }

  const hasFeature = (name: keyof FeaturesMap | string): boolean => {
    return features.value[name] !== false
  }

  return {
    features,
    isLoading,
    fetchFeatures,
    hasFeature
  }
}
