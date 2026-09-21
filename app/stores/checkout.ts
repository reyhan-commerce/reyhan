import { defineStore } from 'pinia'
import type { CartPricing } from './cart'

export interface ProvinceItem {
  id: number
  name: string
  slug: string
}

export interface CityItem {
  id: number
  province_id: number
  name: string
  slug: string
  postal_prefix?: string | null
}

export interface AddressItem {
  id: number
  recipient_name: string
  recipient_mobile: string
  province: ProvinceItem | null
  city: CityItem | null
  postal_code: string
  address_line: string
  building_number?: string | null
  unit?: string | null
  is_default: boolean
  full_address: string
  created_at: string
}

export interface GatewayItem {
  id: string
  name: string
  description: string
}

export interface CreateAddressPayload {
  province_id: number
  city_id: number
  recipient_name: string
  recipient_mobile: string
  postal_code: string
  address_line: string
  building_number?: string
  unit?: string
  is_default?: boolean
}

export const useCheckoutStore = defineStore('checkout', () => {
  const api = useApi()

  const addresses = ref<AddressItem[]>([])
  const selectedAddressId = ref<number | null>(null)
  const selectedShippingMethod = ref<'pishtaz' | 'express'>('pishtaz')
  const selectedGateway = ref<string>('sandbox')
  const gateways = ref<GatewayItem[]>([])
  const provinces = ref<ProvinceItem[]>([])
  const cities = ref<CityItem[]>([])
  const previewPricing = ref<CartPricing | null>(null)
  const previewFinalPayable = ref<number | null>(null)
  const notes = ref<string>('')

  const isLoadingAddresses = ref(false)
  const isLoadingGateways = ref(false)
  const isLoadingPreview = ref(false)
  const isSubmittingOrder = ref(false)

  const selectedAddress = computed(() => {
    return addresses.value.find(a => a.id === selectedAddressId.value) || null
  })

  async function fetchAddresses() {
    isLoadingAddresses.value = true
    try {
      const res = await api<{ data: AddressItem[] }>('/addresses')
      addresses.value = res.data || []

      // If no address selected or selected one not in list, pick default or first
      if (!selectedAddressId.value || !addresses.value.some(a => a.id === selectedAddressId.value)) {
        const defaultAddr = addresses.value.find(a => a.is_default) || addresses.value[0]
        if (defaultAddr) {
          selectedAddressId.value = defaultAddr.id
        }
      }
    } catch {
      addresses.value = []
    } finally {
      isLoadingAddresses.value = false
    }
  }

  async function fetchGateways() {
    isLoadingGateways.value = true
    try {
      const res = await api<{ success: boolean, data: GatewayItem[] }>('/payment/gateways')
      gateways.value = res.data || []
      if (gateways.value.length > 0 && !gateways.value.some(g => g.id === selectedGateway.value)) {
        selectedGateway.value = gateways.value[0]?.id || 'sandbox'
      }
    } catch {
      gateways.value = [
        { id: 'sandbox', name: 'درگاه پرداخت تستی', description: 'شبیه‌ساز پرداخت جهت تست' }
      ]
    } finally {
      isLoadingGateways.value = false
    }
  }

  async function fetchProvinces() {
    if (provinces.value.length > 0) return
    try {
      const res = await api<{ data: ProvinceItem[] }>('/geo/provinces')
      provinces.value = res.data || []
    } catch {
      provinces.value = []
    }
  }

  async function fetchCities(provinceId: number) {
    cities.value = []
    try {
      const res = await api<{ data: CityItem[] }>(`/geo/provinces/${provinceId}/cities`)
      cities.value = res.data || []
    } catch {
      cities.value = []
    }
  }

  async function fetchPreview() {
    isLoadingPreview.value = true
    try {
      const params: Record<string, string> = {}
      if (selectedAddressId.value) {
        params.address_id = String(selectedAddressId.value)
      }
      params.shipping_method = selectedShippingMethod.value

      const query = new URLSearchParams(params).toString()
      const res = await api<{ success: boolean, data: { pricing: CartPricing, final_payable: number } }>(`/checkout/preview?${query}`)
      if (res.data?.pricing) {
        previewPricing.value = res.data.pricing
        previewFinalPayable.value = res.data.final_payable
      }
    } catch {
      // preview error handled gracefully
    } finally {
      isLoadingPreview.value = false
    }
  }

  async function createAddress(payload: CreateAddressPayload): Promise<AddressItem> {
    const res = await api<{ success: boolean, data: AddressItem }>('/addresses', {
      method: 'POST',
      body: payload,
    })

    await fetchAddresses()
    selectedAddressId.value = res.data.id
    await fetchPreview()
    return res.data
  }

  async function setDefaultAddress(addressId: number) {
    await api(`/addresses/${addressId}/default`, { method: 'PATCH' })
    await fetchAddresses()
  }

  async function deleteAddress(addressId: number) {
    await api(`/addresses/${addressId}`, { method: 'DELETE' })
    if (selectedAddressId.value === addressId) {
      selectedAddressId.value = null
    }
    await fetchAddresses()
    await fetchPreview()
  }

  async function submitOrder(): Promise<{ orderId: number, orderNumber: string, redirectUrl: string }> {
    if (!selectedAddressId.value) {
      throw new Error('لطفاً آدرس تحویل سفارش را انتخاب کنید.')
    }

    isSubmittingOrder.value = true
    try {
      const callbackUrl = `${window.location.origin}/checkout/callback`

      const res = await api<{
        success: boolean
        data: {
          order_id: number
          order_number: string
          final_payable: number
          authority: string
          redirect_url: string
        }
      }>('/checkout/create-order', {
        method: 'POST',
        body: {
          address_id: selectedAddressId.value,
          shipping_method: selectedShippingMethod.value,
          gateway: selectedGateway.value,
          callback_url: callbackUrl,
          notes: notes.value.trim() || undefined,
        },
      })

      return {
        orderId: res.data.order_id,
        orderNumber: res.data.order_number,
        redirectUrl: res.data.redirect_url,
      }
    } finally {
      isSubmittingOrder.value = false
    }
  }

  return {
    addresses,
    selectedAddressId,
    selectedAddress,
    selectedShippingMethod,
    selectedGateway,
    gateways,
    provinces,
    cities,
    previewPricing,
    previewFinalPayable,
    notes,
    isLoadingAddresses,
    isLoadingGateways,
    isLoadingPreview,
    isSubmittingOrder,
    fetchAddresses,
    fetchGateways,
    fetchProvinces,
    fetchCities,
    fetchPreview,
    createAddress,
    setDefaultAddress,
    deleteAddress,
    submitOrder,
  }
})
