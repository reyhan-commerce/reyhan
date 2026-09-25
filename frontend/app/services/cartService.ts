import type { CartData } from '~/types/cart'
import type { ApiResponse } from '~/types/api'

export function useCartService() {
  const api = useApi()

  async function getCart(): Promise<CartData | null> {
    const res = await api<ApiResponse<CartData>>('/cart')
    return res.data || null
  }

  async function addItem(variantId: number, quantity: number = 1): Promise<CartData | null> {
    const res = await api<ApiResponse<CartData>>('/cart/items', {
      method: 'POST',
      body: {
        variant_id: variantId,
        quantity
      }
    })
    return res.data || null
  }

  async function updateItem(itemId: number, quantity: number): Promise<CartData | null> {
    const res = await api<ApiResponse<CartData>>(`/cart/items/${itemId}`, {
      method: 'PUT',
      body: { quantity }
    })
    return res.data || null
  }

  async function removeItem(itemId: number): Promise<CartData | null> {
    const res = await api<ApiResponse<CartData>>(`/cart/items/${itemId}`, {
      method: 'DELETE'
    })
    return res.data || null
  }

  async function clearCart(): Promise<CartData | null> {
    const res = await api<ApiResponse<CartData>>('/cart', {
      method: 'DELETE'
    })
    return res.data || null
  }

  async function applyCoupon(code: string): Promise<CartData | null> {
    const res = await api<ApiResponse<CartData>>('/cart/coupon', {
      method: 'POST',
      body: { code: code.trim() }
    })
    return res.data || null
  }

  async function removeCoupon(): Promise<CartData | null> {
    const res = await api<ApiResponse<CartData>>('/cart/coupon', {
      method: 'DELETE'
    })
    return res.data || null
  }

  async function syncGuestCart(sessionId: string, authToken: string): Promise<CartData | null> {
    const res = await api<ApiResponse<CartData>>('/cart/sync', {
      method: 'POST',
      body: { session_id: sessionId },
      // Pass token explicitly — do NOT rely on the $fetch interceptor here.
      // The interceptor's tokenCookie ref may lag behind the just-set login token.
      headers: { Authorization: `Bearer ${authToken}` }
    })
    return res.data || null
  }

  return {
    getCart,
    addItem,
    updateItem,
    removeItem,
    clearCart,
    applyCoupon,
    removeCoupon,
    syncGuestCart
  }
}
