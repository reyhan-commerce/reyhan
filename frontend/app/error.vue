<script setup lang="ts">
import type { NuxtError } from '#app'

const props = defineProps<{
  error: NuxtError
}>()

const { t, isRtl } = useShopLocale()
const { toPersianDigits } = usePersian()

const is404 = computed(() => props.error?.statusCode === 404)

const displayStatusCode = computed(() => {
  const code = props.error?.statusCode || 500
  return isRtl.value ? toPersianDigits(code) : String(code)
})

const handleError = () => {
  clearError({ redirect: '/' })
}
</script>

<template>
  <div
    :dir="isRtl ? 'rtl' : 'ltr'"
    class="min-h-screen bg-neutral-50 dark:bg-neutral-950 flex items-center justify-center p-4 font-sans"
  >
    <div class="w-full max-w-lg bg-white dark:bg-neutral-900 border border-neutral-200/80 dark:border-neutral-800/80 rounded-3xl p-8 sm:p-12 text-center shadow-xl flex flex-col items-center gap-6 relative overflow-hidden">
      <!-- Decorative background blur -->
      <div class="absolute -top-24 -right-24 w-48 h-48 bg-primary-500/10 rounded-full blur-3xl pointer-events-none" />
      <div class="absolute -bottom-24 -left-24 w-48 h-48 bg-primary-500/10 rounded-full blur-3xl pointer-events-none" />

      <!-- Big Status Badge / Code -->
      <div class="flex flex-col items-center gap-2">
        <div class="w-20 h-20 rounded-3xl bg-primary-50 dark:bg-primary-950/40 text-primary-600 dark:text-primary-400 flex items-center justify-center shadow-sm">
          <UIcon
            :name="is404 ? 'i-lucide-file-question' : 'i-lucide-alert-octagon'"
            class="w-10 h-10"
          />
        </div>
        <span class="text-3xl sm:text-4xl font-black text-neutral-900 dark:text-white mt-2">
          {{ displayStatusCode }}
        </span>
      </div>

      <!-- Error Text -->
      <div class="flex flex-col gap-2">
        <h1 class="text-xl sm:text-2xl font-black text-neutral-900 dark:text-white">
          {{
            is404
              ? t('errors.not_found_title', 'صفحه مورد نظر یافت نشد!')
              : t('errors.server_error_title', 'خطایی در پردازش درخواست رخ داد')
          }}
        </h1>
        <p class="text-xs sm:text-sm text-neutral-500 dark:text-neutral-400 leading-relaxed max-w-sm mx-auto">
          {{
            is404
              ? t('errors.not_found_desc', 'متاسفانه صفحه‌ای که به دنبال آن هستید حذف شده، تغییر نام داده شده یا موقتاً در دسترس نمی‌باشد.')
              : (error.message || t('errors.server_error_desc', 'مشکلی در ارتباط با سرور رخ داده است. لطفاً مجدداً تلاش فرمایید.'))
          }}
        </p>
      </div>

      <!-- Action Buttons -->
      <div class="w-full grid grid-cols-1 sm:grid-cols-2 gap-3 pt-2">
        <UButton
          color="primary"
          size="lg"
          block
          icon="i-lucide-home"
          class="font-bold cursor-pointer"
          @click="handleError"
        >
          {{ t('errors.back_to_home', 'صفحه اصلی') }}
        </UButton>

        <UButton
          to="/products"
          variant="outline"
          color="neutral"
          size="lg"
          block
          icon="i-lucide-shopping-bag"
          class="font-semibold cursor-pointer"
        >
          {{ t('catalog.products', 'مشاهده محصولات') }}
        </UButton>
      </div>
    </div>
  </div>
</template>

