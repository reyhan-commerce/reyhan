<script setup lang="ts">
import AddressModal from '~/components/checkout/AddressModal.vue'
import CheckoutSteps from '~/components/checkout/CheckoutSteps.vue'
import CheckoutAddressStep from '~/components/checkout/CheckoutAddressStep.vue'
import CheckoutShippingStep from '~/components/checkout/CheckoutShippingStep.vue'
import CheckoutPaymentStep from '~/components/checkout/CheckoutPaymentStep.vue'
import CheckoutSummaryCard from '~/components/checkout/CheckoutSummaryCard.vue'
import { useAuthStore } from '~/stores/auth'
import { useCartStore } from '~/stores/cart'
import { useCheckoutStore } from '~/stores/checkout'

definePageMeta({
  layout: 'checkout'
})

useSeoMeta({
  title: 'تکمیل سفارش و تسویه‌حساب',
  description: 'انتخاب آدرس تحویل، انتخاب شیوه ارسال و اتصال به درگاه امن بانکی'
})

const authStore = useAuthStore()
const cartStore = useCartStore()
const checkoutStore = useCheckoutStore()
const toast = useToast()

// Current active step
const currentStep = ref<1 | 2 | 3>(1)
const isAddressModalOpen = ref(false)

// Initialize data
onMounted(async () => {
  // If not logged in, prompt user
  if (!authStore.isAuthenticated) {
    authStore.openAuthModal()
    return
  }

  await cartStore.fetchCart()

  if (cartStore.itemsCount === 0) {
    navigateTo('/cart')
    return
  }

  await Promise.all([
    checkoutStore.fetchAddresses(),
    checkoutStore.fetchShippingMethods(),
    checkoutStore.fetchGateways(),
    checkoutStore.fetchPreview()
  ])
})

// When address changes, update shipping methods and preview
watch(() => checkoutStore.selectedAddressId, async () => {
  await checkoutStore.fetchShippingMethods()
  await checkoutStore.fetchPreview()
})

watch(() => checkoutStore.selectedShippingMethodId, () => {
  checkoutStore.fetchPreview()
})

// Submit checkout order
async function handlePay() {
  if (!checkoutStore.selectedAddressId) {
    currentStep.value = 1
    toast.add({
      title: 'انتخاب آدرس الزامی است',
      description: 'لطفاً آدرس تحویل سفارش را مشخص فرمایید.',
      color: 'warning'
    })
    return
  }

  try {
    const result = await checkoutStore.submitOrder()
    if (result.redirectUrl) {
      window.location.href = result.redirectUrl
    }
  } catch (err: unknown) {
    const errorMsg = (err as { data?: { message?: string }, message?: string })?.data?.message
      || (err as Error)?.message
      || 'مشکلی در اتصال به درگاه پرداخت رخ داده است.'
    toast.add({
      title: 'خطا در ثبت سفارش',
      description: errorMsg,
      color: 'error'
    })
  }
}
</script>

<template>
  <div class="py-6 sm:py-10 flex flex-col gap-8 pb-28 sm:pb-16 max-w-6xl mx-auto">
    <!-- Stepper Navigation -->
    <CheckoutSteps v-model:current-step="currentStep" />

    <!-- Two-column Checkout Flow -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
      <!-- Main Flow Steps (8 cols) -->
      <div class="lg:col-span-8 flex flex-col gap-6">
        <!-- STEP 1: Address Selection -->
        <CheckoutAddressStep
          :active="currentStep === 1"
          @next="currentStep = 2"
          @open-address-modal="isAddressModalOpen = true"
        />

        <!-- STEP 2: Shipping Method -->
        <CheckoutShippingStep
          :active="currentStep === 2"
          @prev="currentStep = 1"
          @next="currentStep = 3"
        />

        <!-- STEP 3: Payment Gateway & Notes -->
        <CheckoutPaymentStep
          :active="currentStep === 3"
          @prev="currentStep = 2"
          @pay="handlePay"
        />
      </div>

      <!-- Invoice Summary Sidebar (4 cols) -->
      <div class="lg:col-span-4">
        <CheckoutSummaryCard
          v-model:current-step="currentStep"
          @pay="handlePay"
        />
      </div>
    </div>

    <!-- Address Modal -->
    <AddressModal
      v-model:open="isAddressModalOpen"
      @saved="(id) => { checkoutStore.selectedAddressId = id }"
    />
  </div>
</template>
