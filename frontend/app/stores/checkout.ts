import { defineStore } from 'pinia'
import type { CartPricing } from '~/types/cart'
import type {
  AddressItem,
  CreateAddressPayload,
  GatewayItem,
  ProvinceItem,
  CityItem,
  AvailableShippingMethod
} from '~/types/order'
import { useCheckoutService } from '~/services/checkoutService'

// Re-export types for backward compatibility
export type { AddressItem, CreateAddressPayload, GatewayItem, ProvinceItem, CityItem, AvailableShippingMethod }

export const useCheckoutStore = defineStore('checkout', () => {
  const checkoutService = useCheckoutService()

  const addresses = ref<AddressItem[]>([])
  const selectedAddressId = ref<number | null>(null)

  // Dynamic Shipping & Logistics state
  const shippingMethods = ref<AvailableShippingMethod[]>([])
  const selectedShippingMethodId = ref<number | null>(null)
  const selectedShippingMethodCode = ref<'pishtaz' | 'express'>('pishtaz')
  const selectedDeliveryDate = ref<string | null>(null)
  const selectedDeliveryTimeSlot = ref<string | null>(null)

  const selectedGateway = ref<string>('sandbox')
  const gateways = ref<GatewayItem[]>([])
  const provinces = ref<ProvinceItem[]>([])
  const cities = ref<CityItem[]>([])
  const previewPricing = ref<CartPricing | null>(null)
  const previewFinalPayable = ref<number | null>(null)
  const notes = ref<string>('')

  // Financials & Wallet
  const useWallet = ref<boolean>(false)
  const walletBalance = ref<number>(0)
  const isCorporateInvoice = ref<boolean>(false)
  const corporateData = ref({
    company_name: '',
    economic_code: '',
    national_id: '',
    registration_number: ''
  })
  const cardTrackingNumber = ref<string>('')
  const cardSourceNumber = ref<string>('')

  const isLoadingAddresses = ref(false)
  const isLoadingShippingMethods = ref(false)
  const isLoadingGateways = ref(false)
  const isLoadingPreview = ref(false)
  const isLoadingWallet = ref(false)
  const isSubmittingOrder = ref(false)

  const selectedAddress = computed(() => {
    return addresses.value.find(a => a.id === selectedAddressId.value) || null
  })

  const selectedShippingMethod = computed(() => {
    if (selectedShippingMethodId.value) {
      return shippingMethods.value.find(m => m.id === selectedShippingMethodId.value) || null
    }
    return shippingMethods.value.find(m => m.slug === selectedShippingMethodCode.value) || shippingMethods.value[0] || null
  })

  const availableTimeSlots = computed(() => {
    return selectedShippingMethod.value?.time_slots || []
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

  async function fetchShippingMethods(): Promise<void> {
    isLoadingShippingMethods.value = true
    try {
      const methods = await checkoutService.getShippingMethods(selectedAddressId.value)
      shippingMethods.value = methods

      if (methods.length > 0) {
        // If current selected method not in list, pick first
        if (!selectedShippingMethodId.value || !methods.some(m => m.id === selectedShippingMethodId.value)) {
          const defaultMethod = methods.find(m => m.slug === 'pishtaz') || methods[0]
          if (defaultMethod) {
            selectShippingMethod(defaultMethod)
          }
        }
      }
    } catch {
      shippingMethods.value = []
    } finally {
      isLoadingShippingMethods.value = false
    }
  }

  function selectShippingMethod(method: AvailableShippingMethod): void {
    selectedShippingMethodId.value = method.id
    selectedShippingMethodCode.value = method.slug.includes('express') ? 'express' : 'pishtaz'

    // If requires time slot, automatically preselect first date and first slot
    if (method.requires_time_slot && method.time_slots.length > 0) {
      const firstDay = method.time_slots[0]
      if (firstDay) {
        selectedDeliveryDate.value = firstDay.date
        selectedDeliveryTimeSlot.value = firstDay.slots[0] || null
      }
    } else {
      selectedDeliveryDate.value = null
      selectedDeliveryTimeSlot.value = null
    }

    fetchPreview()
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
        shipping_method_id: selectedShippingMethodId.value,
        shipping_method: selectedShippingMethodCode.value
      })
      if (data?.pricing) {
        previewPricing.value = data.pricing
        previewFinalPayable.value = data.final_payable
      }
      if (data?.shipping_methods && data.shipping_methods.length > 0) {
        shippingMethods.value = data.shipping_methods
        if (!selectedShippingMethodId.value) {
          const first = data.shipping_methods[0]
          if (first) selectShippingMethod(first)
        }
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
    await fetchShippingMethods()
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
    await fetchShippingMethods()
    await fetchPreview()
  }

  async function fetchWalletBalance(): Promise<void> {
    isLoadingWallet.value = true
    try {
      const data = await checkoutService.getWallet()
      walletBalance.value = data.balance
    } catch {
      walletBalance.value = 0
    } finally {
      isLoadingWallet.value = false
    }
  }

  const effectivePayable = computed(() => {
    const raw = previewFinalPayable.value ?? previewPricing.value?.final_price ?? 0
    if (!useWallet.value) return raw
    return Math.max(0, raw - walletBalance.value)
  })

  async function submitOrder(): Promise<{ orderId: number, orderNumber: string, redirectUrl: string }> {
    if (!selectedAddressId.value) {
      throw new Error('لطفاً آدرس تحویل سفارش را انتخاب کنید.')
    }

    if (selectedShippingMethod.value?.requires_time_slot && (!selectedDeliveryDate.value || !selectedDeliveryTimeSlot.value)) {
      throw new Error('لطفاً روز و بازه زمانی تحویل سفارش را مشخص کنید.')
    }

    if (selectedGateway.value === 'card_to_card' && !cardTrackingNumber.value.trim()) {
      throw new Error('لطفاً شماره پیگیری یا ارجاع فیش واریزی کارت به کارت را وارد کنید.')
    }

    if (isCorporateInvoice.value && (!corporateData.value.company_name.trim() || !corporateData.value.national_id.trim())) {
      throw new Error('لطفاً نام شرکت و شناسه ملی را جهت صدور فاکتور رسمی وارد کنید.')
    }

    isSubmittingOrder.value = true
    try {
      const callbackUrl = `${window.location.origin}/checkout/callback`

      const res = await checkoutService.createOrder({
        address_id: selectedAddressId.value,
        shipping_method_id: selectedShippingMethodId.value || undefined,
        shipping_method: selectedShippingMethod.value?.slug || selectedShippingMethodCode.value,
        delivery_date: selectedDeliveryDate.value || undefined,
        delivery_time_slot: selectedDeliveryTimeSlot.value || undefined,
        gateway: selectedGateway.value,
        callback_url: callbackUrl,
        notes: notes.value.trim() || undefined,
        use_wallet: useWallet.value,
        is_corporate_invoice: isCorporateInvoice.value,
        corporate_data: isCorporateInvoice.value ? corporateData.value : undefined,
        card_tracking_number: selectedGateway.value === 'card_to_card' ? cardTrackingNumber.value.trim() : undefined,
        card_source_number: selectedGateway.value === 'card_to_card' ? cardSourceNumber.value.trim() : undefined
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
    shippingMethods,
    selectedShippingMethodId,
    selectedShippingMethodCode,
    selectedShippingMethod,
    selectedDeliveryDate,
    selectedDeliveryTimeSlot,
    availableTimeSlots,
    selectedGateway,
    gateways,
    provinces,
    cities,
    previewPricing,
    previewFinalPayable,
    notes,
    useWallet,
    walletBalance,
    isCorporateInvoice,
    corporateData,
    cardTrackingNumber,
    cardSourceNumber,
    effectivePayable,
    isLoadingAddresses,
    isLoadingShippingMethods,
    isLoadingGateways,
    isLoadingPreview,
    isLoadingWallet,
    isSubmittingOrder,
    fetchAddresses,
    fetchShippingMethods,
    selectShippingMethod,
    fetchGateways,
    fetchProvinces,
    fetchCities,
    fetchPreview,
    fetchWalletBalance,
    createAddress,
    setDefaultAddress,
    deleteAddress,
    submitOrder
  }
})
