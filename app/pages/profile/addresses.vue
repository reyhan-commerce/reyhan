<script setup lang="ts">
import AddressModal from '~/components/checkout/AddressModal.vue'
import { useCheckoutStore } from '~/stores/checkout'

const checkoutStore = useCheckoutStore()
const toast = useToast()
const { toPersianDigits } = usePersian()

useSeoMeta({
  title: 'آدرس‌های من - ایزیشاپ',
})

const isAddressModalOpen = ref(false)

onMounted(async () => {
  await checkoutStore.fetchAddresses()
})

const handleSetDefault = async (addressId: number) => {
  try {
    await checkoutStore.setDefaultAddress(addressId)
    toast.add({
      title: 'موفقیت‌آمیز',
      description: 'آدرس پیش‌فرض شما با موفقیت تغییر یافت.',
      color: 'success',
    })
  } catch {
    // handled
  }
}

const handleDelete = async (addressId: number) => {
  if (confirm('آیا از حذف این آدرس اطمینان دارید؟')) {
    try {
      await checkoutStore.deleteAddress(addressId)
      toast.add({
        title: 'حذف آدرس',
        description: 'آدرس مورد نظر با موفقیت حذف گردید.',
        color: 'neutral',
      })
    } catch {
      // handled
    }
  }
}
</script>

<template>
  <div class="flex flex-col gap-6">
    <!-- Header with Action -->
    <div class="bg-white dark:bg-neutral-900 border border-neutral-200/80 dark:border-neutral-800/80 rounded-3xl p-5 shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-4">
      <div>
        <h2 class="text-lg font-black text-neutral-900 dark:text-neutral-100">
          دفترچه آدرس‌ها
        </h2>
        <p class="text-xs text-neutral-400 mt-0.5">
          مدیریت نشانی‌ها جهت دریافت سریع‌تر سفارش‌های آینده
        </p>
      </div>

      <UButton
        color="primary"
        size="md"
        icon="i-lucide-plus"
        class="font-bold cursor-pointer"
        @click="isAddressModalOpen = true"
      >
        افزودن آدرس جدید
      </UButton>
    </div>

    <!-- Loading State -->
    <div
      v-if="checkoutStore.isLoadingAddresses"
      class="flex items-center justify-center p-16"
    >
      <UIcon
        name="i-lucide-loader-2"
        class="w-8 h-8 text-primary-500 animate-spin"
      />
    </div>

    <!-- Empty State -->
    <div
      v-else-if="checkoutStore.addresses.length === 0"
      class="bg-white dark:bg-neutral-900 border border-neutral-200/80 dark:border-neutral-800/80 rounded-3xl p-12 text-center shadow-xs flex flex-col items-center justify-center gap-4"
    >
      <div class="w-16 h-16 rounded-full bg-neutral-100 dark:bg-neutral-800 text-neutral-400 flex items-center justify-center">
        <UIcon
          name="i-lucide-map-pin-off"
          class="w-8 h-8"
        />
      </div>
      <div class="flex flex-col gap-1">
        <h3 class="font-bold text-neutral-800 dark:text-neutral-200 text-base">
          هنوز آدرسی ثبت نکرده‌اید
        </h3>
        <p class="text-xs text-neutral-400">
          برای تجربه خریدی سریع‌تر، نشانی تحویل سفارش خود را ثبت نمایید.
        </p>
      </div>
      <UButton
        color="primary"
        size="md"
        icon="i-lucide-plus"
        class="mt-2 font-bold cursor-pointer"
        @click="isAddressModalOpen = true"
      >
        ثبت اولین آدرس
      </UButton>
    </div>

    <!-- Addresses Grid -->
    <div
      v-else
      class="grid grid-cols-1 md:grid-cols-2 gap-4"
    >
      <div
        v-for="addr in checkoutStore.addresses"
        :key="addr.id"
        class="bg-white dark:bg-neutral-900 border rounded-3xl p-5 shadow-xs flex flex-col justify-between gap-4 transition-all"
        :class="[
          addr.is_default
            ? 'border-primary-500/50 dark:border-primary-500/40 ring-2 ring-primary-500/10'
            : 'border-neutral-200/80 dark:border-neutral-800/80'
        ]"
      >
        <!-- Top Info -->
        <div class="flex flex-col gap-2.5">
          <div class="flex items-center justify-between">
            <span class="font-bold text-sm text-neutral-900 dark:text-neutral-100">
              {{ addr.recipient_name }}
            </span>
            <UBadge
              v-if="addr.is_default"
              color="primary"
              variant="subtle"
              size="xs"
              class="font-bold px-2 py-0.5 rounded-full"
            >
              آدرس پیش‌فرض
            </UBadge>
          </div>

          <p class="text-xs text-neutral-600 dark:text-neutral-300 leading-relaxed">
            {{ addr.full_address }}
          </p>

          <div class="flex flex-wrap items-center gap-3 text-xs text-neutral-400 pt-1">
            <span class="flex items-center gap-1">
              <UIcon
                name="i-lucide-phone"
                class="w-3.5 h-3.5"
              />
              <span class="font-mono dir-ltr">{{ addr.recipient_mobile }}</span>
            </span>
            <span>•</span>
            <span class="flex items-center gap-1">
              <UIcon
                name="i-lucide-mail"
                class="w-3.5 h-3.5"
              />
              <span>کد پستی: {{ toPersianDigits(addr.postal_code) }}</span>
            </span>
          </div>
        </div>

        <!-- Actions -->
        <div class="pt-3 border-t border-neutral-100 dark:border-neutral-800 flex items-center justify-between gap-2">
          <UButton
            v-if="!addr.is_default"
            variant="ghost"
            color="neutral"
            size="xs"
            icon="i-lucide-check"
            class="font-semibold cursor-pointer"
            @click="handleSetDefault(addr.id)"
          >
            تنظیم به عنوان پیش‌فرض
          </UButton>
          <span v-else />

          <UButton
            variant="ghost"
            color="error"
            size="xs"
            icon="i-lucide-trash-2"
            class="cursor-pointer"
            @click="handleDelete(addr.id)"
          >
            حذف
          </UButton>
        </div>
      </div>
    </div>

    <!-- Address Modal -->
    <AddressModal
      v-model:open="isAddressModalOpen"
      @saved="checkoutStore.fetchAddresses"
    />
  </div>
</template>
