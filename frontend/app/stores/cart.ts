import { defineStore } from 'pinia'
import type { CartData, CartItem, CartVariant, CartPricing, AppliedCoupon } from '~/types/cart'
import { useCartService } from '~/services/cartService'

// Re-export types for backward compatibility
export type { CartData, CartItem, CartVariant, CartPricing, AppliedCoupon }

export const useCartStore = defineStore('cart', () => {
  const cartService = useCartService()
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
      const data = await cartService.getCart()
      if (data) {
        cart.value = data
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
      const data = await cartService.addItem(variantId, quantity)
      if (data) {
        cart.value = data
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
      const data = await cartService.updateItem(itemId, quantity)
      if (data) {
        cart.value = data
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
      const data = await cartService.removeItem(itemId)
      if (data) {
        cart.value = data
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
      const data = await cartService.clearCart()
      if (data) {
        cart.value = data
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
      const data = await cartService.applyCoupon(code)
      if (data) {
        cart.value = data
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
      const data = await cartService.removeCoupon()
      if (data) {
        cart.value = data
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

    // /cart/sync requires auth:sanctum — skip if not authenticated
    const authStore = useAuthStore()
    if (!authStore.isAuthenticated) return

    // Read token directly from the auth store ref (source of truth).
    // Do NOT rely on the $fetch interceptor's tokenCookie — it may lag
    // due to reactive closure timing when called right after login.
    const token = authStore.token
    if (!token) return

    try {
      const data = await cartService.syncGuestCart(sessionId, token)
      if (data) {
        cart.value = data
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
