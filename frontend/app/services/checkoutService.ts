import type {
  AddressItem,
  CreateAddressPayload,
  GatewayItem,
  ProvinceItem,
  CityItem,
  AvailableShippingMethod
} from '~/types/order'
import type { CartPricing } from '~/types/cart'
import type { ApiResponse } from '~/types/api'

export interface CheckoutPreviewData {
  pricing: CartPricing
  final_payable: number
  selected_address_id?: number | null
  items_count?: number
  shipping_methods?: AvailableShippingMethod[]
}

export interface CreateOrderPayload {
  address_id: number
  shipping_method_id?: number
  shipping_method?: string
  delivery_date?: string
  delivery_time_slot?: string
  gateway: string
  callback_url: string
  notes?: string
  use_wallet?: boolean
  is_corporate_invoice?: boolean
  corporate_data?: {
    company_name?: string
    economic_code?: string
    national_id?: string
    registration_number?: string
  }
  card_tracking_number?: string
  card_source_number?: string
}

export interface SubmitOrderResponse {
  order_id: number
  order_number: string
  final_payable: number
  authority?: string
  redirect_url: string
}

export function useCheckoutService() {
  const api = useApi()

  async function getAddresses(): Promise<AddressItem[]> {
    const res = await api<{ data: AddressItem[] }>('/addresses')
    return res.data || []
  }

  async function createAddress(payload: CreateAddressPayload): Promise<AddressItem> {
    const res = await api<{ success: boolean, data: AddressItem }>('/addresses', {
      method: 'POST',
      body: payload
    })
    return res.data
  }

  async function setDefaultAddress(addressId: number): Promise<void> {
    await api(`/addresses/${addressId}/default`, { method: 'PATCH' })
  }

  async function deleteAddress(addressId: number): Promise<boolean> {
    await api(`/addresses/${addressId}`, { method: 'DELETE' })
    return true
  }

  async function getGateways(): Promise<GatewayItem[]> {
    try {
      const res = await api<ApiResponse<GatewayItem[]>>('/payment/gateways')
      return res.data || []
    } catch {
      return [
        { id: 'sandbox', name: 'درگاه پرداخت تستی', description: 'شبیه‌ساز پرداخت جهت تست' }
      ]
    }
  }

  async function getProvinces(): Promise<ProvinceItem[]> {
    const res = await api<{ data: ProvinceItem[] }>('/geo/provinces')
    return res.data || []
  }

  async function getCities(provinceId: number): Promise<CityItem[]> {
    const res = await api<{ data: CityItem[] }>(`/geo/provinces/${provinceId}/cities`)
    return res.data || []
  }

  async function getShippingMethods(addressId?: number | null): Promise<AvailableShippingMethod[]> {
    const query = addressId ? `?address_id=${addressId}` : ''
    try {
      const res = await api<ApiResponse<{ methods: AvailableShippingMethod[] }>>(`/checkout/shipping-methods${query}`)
      return res.data?.methods || []
    } catch {
      return []
    }
  }

  async function getPreview(params?: {
    address_id?: number | null
    shipping_method_id?: number | null
    shipping_method?: string
  }): Promise<CheckoutPreviewData | null> {
    const queryParams: Record<string, string> = {}
    if (params?.address_id) {
      queryParams.address_id = String(params.address_id)
    }
    if (params?.shipping_method_id) {
      queryParams.shipping_method_id = String(params.shipping_method_id)
    }
    if (params?.shipping_method) {
      queryParams.shipping_method = params.shipping_method
    }

    const query = new URLSearchParams(queryParams).toString()
    const endpoint = query ? `/checkout/preview?${query}` : '/checkout/preview'
    const res = await api<ApiResponse<CheckoutPreviewData>>(endpoint)
    return res.data || null
  }

  async function createOrder(payload: CreateOrderPayload): Promise<SubmitOrderResponse> {
    const res = await api<ApiResponse<SubmitOrderResponse>>('/checkout/create-order', {
      method: 'POST',
      body: payload
    })
    if (!res.data) {
      throw new Error(res.message || 'خطا در ثبت سفارش')
    }
    return res.data
  }

  async function getWallet(): Promise<{ balance: number }> {
    try {
      const res = await api<ApiResponse<{ balance: number }>>('/wallet')
      return res.data || { balance: 0 }
    } catch {
      return { balance: 0 }
    }
  }

  return {
    getAddresses,
    createAddress,
    setDefaultAddress,
    deleteAddress,
    getGateways,
    getProvinces,
    getCities,
    getShippingMethods,
    getPreview,
    createOrder,
    getWallet
  }
}
