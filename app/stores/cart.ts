import { defineStore } from 'pinia'

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
  items_count: number
  items: CartItem[]
  pricing: CartPricing
}

export const useCartStore = defineStore('cart', () => {
  const api = useApi()
  const toast = useToast()
  const cartSessionCookie = useCookie<string | null>('cart_session')

  const isSlideoverOpen = ref(false)
  const cart = ref<CartData | null>(null)
  const isLoading = ref(false)
  const isUpdatingItem = ref<number | null>(null)
  const isApplyingCoupon = ref(false)

  const itemsCount = computed(() => cart.value?.items_count ?? 0)
  const isEmpty = computed(() => !cart.value || cart.value.items.length === 0)
  const pricing = computed(() => cart.value?.pricing ?? null)

  const openSlideover = () => {
    isSlideoverOpen.value = true
  }

  const closeSlideover = () => {
    isSlideoverOpen.value = false
  }

  const fetchCart = async (): Promise<void> => {
    isLoading.value = true
    try {
      const res = await api<ApiResponse<CartData>>('/cart')
      if (res.data) {
        cart.value = res.data
      }
    } catch {
      // Ignored: silent failure on initial fetch
    } finally {
      isLoading.value = false
    }
  }

  const addItem = async (variantId: number, quantity = 1): Promise<boolean> => {
    isLoading.value = true
    try {
      const res = await api<ApiResponse<CartData>>('/cart/items', {
        method: 'POST',
        body: {
          variant_id: variantId,
          quantity
        }
      })

      if (res.data) {
        cart.value = res.data
        toast.add({
          title: 'به سبد خرید اضافه شد',
          description: 'کالای انتخابی با موفقیت در سبد خرید شما قرار گرفت.',
          color: 'success',
          icon: 'i-lucide-check-circle'
        })
        openSlideover()
        return true
      }
      return false
    } catch {
      return false
    } finally {
      isLoading.value = false
    }
  }

  const updateQuantity = async (itemId: number, quantity: number): Promise<boolean> => {
    isUpdatingItem.value = itemId

    // Optimistic quantity update in UI
    const targetItem = cart.value?.items.find(i => i.id === itemId)
    const prevQty = targetItem?.quantity

    if (targetItem) {
      targetItem.quantity = quantity
    }

    try {
      const res = await api<ApiResponse<CartData>>(`/cart/items/${itemId}`, {
        method: 'PUT',
        body: { quantity }
      })

      if (res.data) {
        cart.value = res.data
        return true
      }
      return false
    } catch {
      // Revert if error
      if (targetItem && prevQty !== undefined) {
        targetItem.quantity = prevQty
      }
      return false
    } finally {
      isUpdatingItem.value = null
    }
  }

  const removeItem = async (itemId: number): Promise<boolean> => {
    isUpdatingItem.value = itemId
    try {
      const res = await api<ApiResponse<CartData>>(`/cart/items/${itemId}`, {
        method: 'DELETE'
      })

      if (res.data) {
        cart.value = res.data
        toast.add({
          title: 'حذف از سبد خرید',
          description: 'کالا از سبد خرید شما حذف گردید.',
          color: 'neutral',
          icon: 'i-lucide-trash-2'
        })
        return true
      }
      return false
    } catch {
      return false
    } finally {
      isUpdatingItem.value = null
    }
  }

  const clearCart = async (): Promise<boolean> => {
    isLoading.value = true
    try {
      const res = await api<ApiResponse<CartData>>('/cart', {
        method: 'DELETE'
      })

      if (res.data) {
        cart.value = res.data
        toast.add({
          title: 'سبد خرید خالی شد',
          color: 'neutral',
          icon: 'i-lucide-trash'
        })
        return true
      }
      return false
    } catch {
      return false
    } finally {
      isLoading.value = false
    }
  }

  const applyCoupon = async (code: string): Promise<boolean> => {
    if (!code.trim()) return false

    isApplyingCoupon.value = true
    try {
      const res = await api<ApiResponse<CartData>>('/cart/coupon', {
        method: 'POST',
        body: { code: code.trim() }
      })

      if (res.data) {
        cart.value = res.data
        toast.add({
          title: 'کد تخفیف اعمال شد',
          description: 'تخفیف کوپن بر روی سفارش شما لحاظ گردید.',
          color: 'success',
          icon: 'i-lucide-ticket'
        })
        return true
      }
      return false
    } catch {
      return false
    } finally {
      isApplyingCoupon.value = false
    }
  }

  const removeCoupon = async (): Promise<boolean> => {
    isApplyingCoupon.value = true
    try {
      const res = await api<ApiResponse<CartData>>('/cart/coupon', {
        method: 'DELETE'
      })

      if (res.data) {
        cart.value = res.data
        toast.add({
          title: 'کد تخفیف حذف شد',
          color: 'neutral',
          icon: 'i-lucide-x'
        })
        return true
      }
      return false
    } catch {
      return false
    } finally {
      isApplyingCoupon.value = false
    }
  }

  const syncGuestCart = async (): Promise<void> => {
    const sessionId = cartSessionCookie.value
    if (!sessionId) return

    try {
      const res = await api<ApiResponse<CartData>>('/cart/sync', {
        method: 'POST',
        body: { session_id: sessionId }
      })

      if (res.data) {
        cart.value = res.data
      }
    } catch {
      // Ignored if sync fails
    }
  }

  return {
    cart,
    itemsCount,
    isEmpty,
    pricing,
    isLoading,
    isUpdatingItem,
    isApplyingCoupon,
    isSlideoverOpen,
    openSlideover,
    closeSlideover,
    fetchCart,
    addItem,
    updateQuantity,
    removeItem,
    clearCart,
    applyCoupon,
    removeCoupon,
    syncGuestCart
  }
})
