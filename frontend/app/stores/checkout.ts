import { defineStore } from 'pinia'
import type { CartPricing } from '~/types/cart'
import type {
  AddressItem,
  CreateAddressPayload,
  GatewayItem,
  ProvinceItem,
  CityItem
} from '~/types/order'
import { useCheckoutService } from '~/services/checkoutService'

// Re-export types for backward compatibility
export type { AddressItem, CreateAddressPayload, GatewayItem, ProvinceItem, CityItem }

export const useCheckoutStore = defineStore('checkout', () => {
  const checkoutService = useCheckoutService()

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

  async function fetchAddresses(): Promise<void> {
    isLoadingAddresses.value = true
    try {
      addresses.value = await checkoutService.getAddresses()

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

  async function fetchGateways(): Promise<void> {
    isLoadingGateways.value = true
    try {
      gateways.value = await checkoutService.getGateways()
      if (gateways.value.length > 0 && !gateways.value.some(g => g.id === selectedGateway.value)) {
        selectedGateway.value = gateways.value[0]?.id || 'sandbox'
      }
    } finally {
      isLoadingGateways.value = false
    }
  }

  async function fetchProvinces(): Promise<void> {
    if (provinces.value.length > 0) return
    try {
      provinces.value = await checkoutService.getProvinces()
    } catch {
      provinces.value = []
    }
  }

  async function fetchCities(provinceId: number): Promise<void> {
    cities.value = []
    try {
      cities.value = await checkoutService.getCities(provinceId)
    } catch {
      cities.value = []
    }
  }

  async function fetchPreview(): Promise<void> {
    isLoadingPreview.value = true
    try {
      const data = await checkoutService.getPreview({
        address_id: selectedAddressId.value,
        shipping_method: selectedShippingMethod.value
      })
      if (data?.pricing) {
        previewPricing.value = data.pricing
        previewFinalPayable.value = data.final_payable
      }
    } catch {
      // preview error handled gracefully
    } finally {
      isLoadingPreview.value = false
    }
  }

  async function createAddress(payload: CreateAddressPayload): Promise<AddressItem> {
    const newAddress = await checkoutService.createAddress(payload)
    await fetchAddresses()
    selectedAddressId.value = newAddress.id
    await fetchPreview()
    return newAddress
  }

  async function setDefaultAddress(addressId: number): Promise<void> {
    await checkoutService.setDefaultAddress(addressId)
    await fetchAddresses()
  }

  async function deleteAddress(addressId: number): Promise<void> {
    await checkoutService.deleteAddress(addressId)
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

      const res = await checkoutService.createOrder({
        address_id: selectedAddressId.value,
        shipping_method: selectedShippingMethod.value,
        gateway: selectedGateway.value,
        callback_url: callbackUrl,
        notes: notes.value.trim() || undefined
      })

      return {
        orderId: res.order_id,
        orderNumber: res.order_number,
        redirectUrl: res.redirect_url
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
    submitOrder
  }
})
