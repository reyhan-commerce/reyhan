<script setup lang="ts">
import type { ProductCardItem } from '~/types/product'
import ProductCard from '~/components/catalog/ProductCard.vue'
import ProductCardSkeleton from '~/components/skeletons/ProductCardSkeleton.vue'

interface Props {
  deals: ProductCardItem[]
  loading?: boolean
  sectionTitle?: string | null
  sectionSubtitle?: string | null
}

defineProps<Props>()

const { toPersianDigits } = usePersian()

// Countdown timer for flash deals
const timeLeft = ref({ hours: 14, minutes: 35, seconds: 20 })
let timerInterval: ReturnType<typeof setInterval> | null = null

onMounted(() => {
  timerInterval = setInterval(() => {
    if (timeLeft.value.seconds > 0) {
      timeLeft.value.seconds--
    } else if (timeLeft.value.minutes > 0) {
      timeLeft.value.minutes--
      timeLeft.value.seconds = 59
    } else if (timeLeft.value.hours > 0) {
      timeLeft.value.hours--
      timeLeft.value.minutes = 59
      timeLeft.value.seconds = 59
    }
  }, 1000)
})

onUnmounted(() => {
  if (timerInterval) clearInterval(timerInterval)
})
</script>

<template>
  <section
    v-if="deals.length > 0 || loading"
    class="rounded-3xl bg-linear-to-r from-primary-500/10 via-primary-500/5 to-transparent border border-primary-500/20 p-6 sm:p-8 flex flex-col gap-6"
  >
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
      <div class="flex items-center gap-3.5">
        <div class="size-11 rounded-2xl bg-primary-500/15 text-primary flex items-center justify-center shrink-0">
          <UIcon
            name="i-lucide-flame"
            class="size-6 text-primary animate-bounce"
          />
        </div>
        <div>
          <h2 class="text-lg sm:text-2xl font-black text-neutral-900 dark:text-white">
            {{ sectionTitle || 'پیشنهادات شگفت‌انگیز روز' }}
          </h2>
          <p class="text-xs text-neutral-500">
            {{ sectionSubtitle || 'فرصت محدود با تخفیف‌های ویژه تا پایان امروز' }}
          </p>
        </div>
      </div>

      <!-- Live Glass Countdown Timer -->
      <div class="flex items-center gap-2 self-start sm:self-auto text-sm font-black">
        <div class="size-11 rounded-2xl bg-white/90 dark:bg-neutral-900/90 backdrop-blur-md border border-neutral-200/80 dark:border-neutral-800 flex items-center justify-center text-primary-600 dark:text-primary-400 shadow-xs">
          {{ toPersianDigits(String(timeLeft.hours).padStart(2, '0')) }}
        </div>
        <span class="text-primary-500">:</span>
        <div class="size-11 rounded-2xl bg-white/90 dark:bg-neutral-900/90 backdrop-blur-md border border-neutral-200/80 dark:border-neutral-800 flex items-center justify-center text-primary-600 dark:text-primary-400 shadow-xs">
          {{ toPersianDigits(String(timeLeft.minutes).padStart(2, '0')) }}
        </div>
        <span class="text-primary-500">:</span>
        <div class="size-11 rounded-2xl bg-white/90 dark:bg-neutral-900/90 backdrop-blur-md border border-neutral-200/80 dark:border-neutral-800 flex items-center justify-center text-primary-600 dark:text-primary-400 shadow-xs">
          {{ toPersianDigits(String(timeLeft.seconds).padStart(2, '0')) }}
        </div>
      </div>
    </div>

    <!-- Flash Deals Cards Grid -->
    <div
      v-if="loading"
      class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4 sm:gap-6"
    >
      <ProductCardSkeleton
        v-for="i in 4"
        :key="i"
      />
    </div>
    <div
      v-else
      class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4 sm:gap-6"
    >
      <ProductCard
        v-for="product in deals"
        :key="product.id"
        :product="product"
      />
    </div>
  </section>
</template>
