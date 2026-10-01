import { defineStore } from 'pinia'
import type { ProductCardItem } from '~/types/product'

export interface WishlistItem {
  id: number
  product: ProductCardItem
  added_at?: string
}

export const useWishlistStore = defineStore('wishlist', () => {
  const api = useApi()
  const authStore = useAuthStore()
  const toast = useToast()

  const wishlistIds = ref<number[]>([])
  const items = ref<WishlistItem[]>([])
  const isLoading = ref(false)

  const isInWishlist = (productId: number) => wishlistIds.value.includes(productId)

  const fetchWishlistIds = async () => {
    if (!authStore.isAuthenticated) {
      wishlistIds.value = []
      return
    }

    try {
      const res = await api<{ success: boolean, data: { ids: number[] } }>('/wishlist/ids')
      if (res.data?.ids) {
        wishlistIds.value = res.data.ids
      }
    } catch {
      // Silent error for background sync
    }
  }

  const fetchWishlist = async () => {
    if (!authStore.isAuthenticated) {
      items.value = []
      return
    }

    isLoading.value = true
    try {
      const res = await api<{ success: boolean, data: { data: WishlistItem[] } }>('/wishlist')
      if (res.data?.data) {
        items.value = res.data.data
        wishlistIds.value = res.data.data.map(item => item.product.id)
      }
    } catch {
      toast.add({
        title: 'خطا',
        description: 'خطا در دریافت لیست علاقه‌مندی‌ها',
        color: 'error'
      })
    } finally {
      isLoading.value = false
    }
  }

  const toggleWishlist = async (productId: number) => {
    if (!authStore.isAuthenticated) {
      authStore.openAuthModal()
      return false
    }

    const exists = isInWishlist(productId)

    // Optimistic UI update
    if (exists) {
      wishlistIds.value = wishlistIds.value.filter(id => id !== productId)
      items.value = items.value.filter(i => i.product?.id !== productId)
    } else {
      wishlistIds.value.push(productId)
    }

    try {
      const res = await api<{ success: boolean, in_wishlist: boolean, message: string }>(
        `/wishlist/${productId}/toggle`,
        { method: 'POST' }
      )

      toast.add({
        title: res.in_wishlist ? 'افزودن به علاقه‌مندی‌ها' : 'حذف از علاقه‌مندی‌ها',
        description: res.message,
        color: res.in_wishlist ? 'success' : 'neutral'
      })

      return res.in_wishlist
    } catch (err: unknown) {
      // Rollback on failure
      if (exists) {
        wishlistIds.value.push(productId)
      } else {
        wishlistIds.value = wishlistIds.value.filter(id => id !== productId)
      }

      const errorMessage = (err as { data?: { message?: string } })?.data?.message || 'مشکلی در به‌روزرسانی لیست علاقه‌مندی‌ها رخ داد.'
      toast.add({
        title: 'خطا',
        description: errorMessage,
        color: 'error'
      })
      return exists
    }
  }

  return {
    wishlistIds,
    items,
    isLoading,
    isInWishlist,
    fetchWishlistIds,
    fetchWishlist,
    toggleWishlist
  }
})
