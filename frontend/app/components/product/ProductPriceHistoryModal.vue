<script setup lang="ts">
import type { ProductPriceHistoryData, PriceHistoryPoint } from '~/types/customerCare'

const props = defineProps<{
  open: boolean
  productSlug: string
  productName: string
}>()

const emit = defineEmits<{
  (e: 'update:open', val: boolean): void
}>()

const api = useApi()
const { formatPrice, toPersianDigits } = usePersian()

const historyData = ref<ProductPriceHistoryData | null>(null)
const isLoading = ref(false)
const hoveredPoint = ref<PriceHistoryPoint | null>(null)

watch(() => props.open, async (isOpen) => {
  if (isOpen && props.productSlug) {
    isLoading.value = true
    try {
      const res = await api<ApiResponse<ProductPriceHistoryData>>(`/products/${props.productSlug}/price-history`)
      historyData.value = res.data ?? null
    } catch {
      historyData.value = null
    } finally {
      isLoading.value = false
    }
  }
})

// SVG chart dimensions and coordinates
const svgWidth = 600
const svgHeight = 220
const padding = 30

const chartPoints = computed(() => {
  if (!historyData.value?.points || historyData.value.points.length < 2) return []

  const points = historyData.value.points
  const minP = historyData.value.min_price
  const maxP = historyData.value.max_price
  const priceRange = maxP > minP ? maxP - minP : 1

  return points.map((p, index) => {
    const x = padding + (index / (points.length - 1)) * (svgWidth - 2 * padding)
    const normalizedPrice = (p.price - minP) / priceRange
    // In SVG 0 is top, so invert Y
    const y = svgHeight - padding - (normalizedPrice * (svgHeight - 2 * padding))
    return {
      x,
      y,
      raw: p
    }
  })
})

const polylinePoints = computed(() => {
  return chartPoints.value.map(pt => `${pt.x},${pt.y}`).join(' ')
})

const polygonPoints = computed(() => {
  if (chartPoints.value.length === 0) return ''
  const first = chartPoints.value[0]
  const last = chartPoints.value[chartPoints.value.length - 1]
  const base = `${last.x},${svgHeight - padding} ${first.x},${svgHeight - padding}`
  return `${polylinePoints.value} ${base}`
})
</script>

<template>
  <UModal :open="open" @update:open="emit('update:open', $event)">
    <template #content>
      <div class="p-6 flex flex-col gap-5">
        <!-- Modal Header -->
        <div class="flex items-center justify-between pb-3 border-b border-neutral-100 dark:border-neutral-800">
          <div class="flex items-center gap-2">
            <UIcon name="i-lucide-trending-up" class="size-5 text-primary" />
            <h3 class="font-black text-base text-neutral-900 dark:text-white">
              نمودار تغییرات قیمت محصول
            </h3>
          </div>
          <UButton
            color="neutral"
            variant="ghost"
            icon="i-lucide-x"
            size="xs"
            @click="emit('update:open', false)"
          />
        </div>

        <p class="text-xs text-neutral-500 font-bold truncate">
          {{ productName }}
        </p>

        <!-- Loading State -->
        <div v-if="isLoading" class="py-12 flex flex-col items-center justify-center gap-3">
          <UIcon name="i-lucide-loader-2" class="size-7 text-primary animate-spin" />
          <span class="text-xs text-neutral-500">در حال دریافت تاریخچه قیمت...</span>
        </div>

        <!-- Content -->
        <div v-else-if="historyData" class="flex flex-col gap-5">
          <!-- Stats Summary Grid -->
          <div class="grid grid-cols-3 gap-3 text-center">
            <div class="p-3 rounded-2xl bg-emerald-50 dark:bg-emerald-950/30 border border-emerald-200/60 dark:border-emerald-900/50 flex flex-col gap-1">
              <span class="text-[11px] text-emerald-800 dark:text-emerald-400 font-bold">کمترین قیمت</span>
              <span class="text-xs sm:text-sm font-black text-emerald-950 dark:text-emerald-200 font-mono">
                {{ formatPrice(historyData.min_price) }}
              </span>
            </div>

            <div class="p-3 rounded-2xl bg-neutral-100 dark:bg-neutral-800 border border-neutral-200 dark:border-neutral-700 flex flex-col gap-1">
              <span class="text-[11px] text-neutral-500 font-bold">قیمت فعلی</span>
              <span class="text-xs sm:text-sm font-black text-neutral-900 dark:text-white font-mono">
                {{ formatPrice(historyData.current_price) }}
              </span>
            </div>

            <div class="p-3 rounded-2xl bg-rose-50 dark:bg-rose-950/30 border border-rose-200/60 dark:border-rose-900/50 flex flex-col gap-1">
              <span class="text-[11px] text-rose-800 dark:text-rose-400 font-bold">بیشترین قیمت</span>
              <span class="text-xs sm:text-sm font-black text-rose-950 dark:text-rose-200 font-mono">
                {{ formatPrice(historyData.max_price) }}
              </span>
            </div>
          </div>

          <!-- SVG Chart Area -->
          <div class="relative bg-neutral-50 dark:bg-neutral-800/40 border border-neutral-200/80 dark:border-neutral-800 rounded-2xl p-3 overflow-hidden">
            <!-- Hover Tooltip -->
            <div
              v-if="hoveredPoint"
              class="absolute top-3 right-3 bg-neutral-900/90 text-white text-[11px] px-3 py-1.5 rounded-xl shadow-md pointer-events-none flex items-center gap-2"
            >
              <span>تاریخ: {{ hoveredPoint.date_jalali }}</span>
              <span>•</span>
              <span class="font-bold text-primary-300 font-mono">{{ formatPrice(hoveredPoint.price) }}</span>
            </div>

            <svg
              :viewBox="`0 0 ${svgWidth} ${svgHeight}`"
              class="w-full h-44 overflow-visible"
            >
              <defs>
                <linearGradient id="priceGradient" x1="0%" y1="0%" x2="0%" y2="100%">
                  <stop offset="0%" stop-color="#3b82f6" stop-opacity="0.3" />
                  <stop offset="100%" stop-color="#3b82f6" stop-opacity="0.0" />
                </linearGradient>
              </defs>

              <!-- Gradient Fill Area -->
              <polygon
                v-if="polygonPoints"
                :points="polygonPoints"
                fill="url(#priceGradient)"
              />

              <!-- Stroke Line -->
              <polyline
                v-if="polylinePoints"
                :points="polylinePoints"
                fill="none"
                stroke="#3b82f6"
                stroke-width="3"
                stroke-linecap="round"
                stroke-linejoin="round"
              />

              <!-- Interactive Points -->
              <circle
                v-for="(pt, idx) in chartPoints"
                :key="idx"
                :cx="pt.x"
                :cy="pt.y"
                r="4.5"
                class="fill-white stroke-primary stroke-2 hover:r-6 cursor-pointer transition-all"
                @mouseenter="hoveredPoint = pt.raw"
                @mouseleave="hoveredPoint = null"
              />
            </svg>

            <!-- X-axis Date Labels -->
            <div class="flex justify-between text-[10px] text-neutral-400 pt-1 px-4 font-mono">
              <span v-for="(p, i) in historyData.points" :key="i">
                {{ p.date_jalali }}
              </span>
            </div>
          </div>

          <p class="text-[11px] text-neutral-400 text-center">
            تغییرات قیمت در ۹۰ روز گذشته ثبت گردیده است.
          </p>
        </div>
      </div>
    </template>
  </UModal>
</template>
