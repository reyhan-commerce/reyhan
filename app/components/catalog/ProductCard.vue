<script setup lang="ts">
import type { ProductCardItem } from '~/stores/catalog'

const props = defineProps<{
  product: ProductCardItem
}>()

const { formatPrice, formatDiscount } = usePersian()

const displayPrice = computed(() => {
  if (props.product.primary_price) {
    return formatPrice(props.product.primary_price)
  }
  if (props.product.price_range.min) {
    return formatPrice(props.product.price_range.min)
  }
  return 'تماس بگیرید'
})

const compareAtPrice = computed(() => {
  if (props.product.primary_compare_at_price) {
    return formatPrice(props.product.primary_compare_at_price)
  }
  return null
})

const discountPercent = computed(() => {
  if (props.product.primary_price && props.product.primary_compare_at_price) {
    return formatDiscount(props.product.primary_price, props.product.primary_compare_at_price)
  }
  return null
})
</script>

<template>
  <NuxtLink
    :to="`/products/${product.slug}`"
    class="group relative h-full rounded-3xl bg-white dark:bg-neutral-900 border border-neutral-200/80 dark:border-neutral-800/80 hover:border-primary/50 dark:hover:border-primary/50 transition-all duration-300 overflow-hidden shadow-xs hover:shadow-xl hover:-translate-y-1 flex flex-col justify-between"
  >
    <!-- Product Thumbnail & Badges -->
    <div class="relative aspect-square w-full overflow-hidden bg-neutral-50/80 dark:bg-neutral-800/40 flex items-center justify-center p-4">
      <img
        v-if="product.thumbnail"
        :src="product.thumbnail"
        :alt="product.name"
        class="h-full w-full object-contain object-center transition-transform duration-500 group-hover:scale-108"
        loading="lazy"
      >
      <div
        v-else
        class="size-16 rounded-2xl bg-primary/10 text-primary flex items-center justify-center text-2xl font-black"
      >
        {{ product.name.charAt(0) }}
      </div>

      <!-- Top Badges (Discount & Featured) -->
      <div class="absolute top-3 start-3 flex flex-col gap-1.5 items-start z-10">
        <span
          v-if="discountPercent"
          class="px-2.5 py-1 rounded-full bg-rose-600 text-white font-black text-xs shadow-sm shadow-rose-600/30"
        >
          {{ discountPercent }}
        </span>
        <span
          v-if="product.is_featured"
          class="px-2 py-0.5 rounded-full bg-amber-500/15 text-amber-600 dark:text-amber-400 font-bold text-[10px] border border-amber-500/30 backdrop-blur-xs"
        >
          ویژه
        </span>
      </div>

      <!-- Quick Action Floating Overlay on Desktop Hover -->
      <div class="absolute inset-x-3 bottom-3 hidden sm:flex items-center justify-center opacity-0 group-hover:opacity-100 transition-all duration-300 translate-y-2 group-hover:translate-y-0 z-10">
        <div class="w-full py-2 px-3 rounded-xl bg-neutral-900/85 dark:bg-white/90 text-white dark:text-neutral-900 backdrop-blur-md text-xs font-bold text-center shadow-lg flex items-center justify-center gap-1.5">
          <UIcon
            name="i-lucide-eye"
            class="size-3.5"
          />
          <span>مشاهده و انتخاب</span>
        </div>
      </div>

      <!-- Out of Stock Overlay -->
      <div
        v-if="!product.is_in_stock"
        class="absolute inset-0 bg-neutral-900/60 backdrop-blur-[2px] flex items-center justify-center z-20"
      >
        <span class="px-3.5 py-1.5 rounded-full bg-neutral-800 text-neutral-200 font-bold text-xs shadow-md border border-neutral-700">
          ناموجود
        </span>
      </div>
    </div>

    <!-- Product Details -->
    <div class="p-4 sm:p-5 flex flex-col flex-1 justify-between gap-3.5">
      <div class="flex flex-col gap-1.5">
        <!-- Brand / Category Info -->
        <div class="flex items-center justify-between text-xs text-neutral-400 font-medium">
          <span
            v-if="product.brand"
            class="text-neutral-500 dark:text-neutral-400 hover:text-primary transition-colors truncate"
          >
            {{ product.brand.name }}
          </span>
          <span
            v-else-if="product.category"
            class="truncate text-[11px]"
          >
            {{ product.category.name }}
          </span>

          <span
            v-if="product.variants_count > 1"
            class="text-[10px] font-bold text-primary px-1.5 py-0.5 rounded-md bg-primary/10"
          >
            {{ product.variants_count }} مدل
          </span>
        </div>

        <!-- Product Name -->
        <h3 class="text-sm font-bold text-neutral-800 dark:text-neutral-100 line-clamp-2 leading-relaxed group-hover:text-primary transition-colors">
          {{ product.name }}
        </h3>
      </div>

      <!-- Pricing Section with 8pt spacing -->
      <div class="pt-3 border-t border-neutral-100 dark:border-neutral-800/80 flex items-end justify-between">
        <div class="flex flex-col">
          <span
            v-if="compareAtPrice"
            class="text-xs text-neutral-400 line-through font-mono decoration-rose-500/50"
          >
            {{ compareAtPrice }}
          </span>
          <div class="flex items-baseline gap-1">
            <span class="text-base sm:text-lg font-black text-neutral-900 dark:text-white font-mono">
              {{ displayPrice }}
            </span>
            <span class="text-[10px] text-neutral-400 font-normal">تومان</span>
          </div>
        </div>

        <div class="size-8 rounded-xl bg-neutral-100 dark:bg-neutral-800 group-hover:bg-primary group-hover:text-white text-neutral-400 flex items-center justify-center transition-colors shadow-xs shrink-0">
          <UIcon
            name="i-lucide-arrow-left"
            class="size-4 group-hover:-translate-x-0.5 transition-transform"
          />
        </div>
      </div>
    </div>
  </NuxtLink>
</template>
