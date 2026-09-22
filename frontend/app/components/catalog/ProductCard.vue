<script setup lang="ts">
import { useWishlistStore } from '~/stores/wishlist'
import type { ProductCardItem } from '~/stores/catalog'

const props = defineProps<{
  product: ProductCardItem
}>()

const wishlistStore = useWishlistStore()
const { formatPrice, formatDiscount } = usePersian()

const displayPrice = computed(() => {
  if (props.product.primary_price) {
    return formatPrice(props.product.primary_price, { showUnit: false })
  }
  if (props.product.price_range.min) {
    return formatPrice(props.product.price_range.min, { showUnit: false })
  }
  return 'تماس بگیرید'
})

const compareAtPrice = computed(() => {
  if (props.product.primary_compare_at_price) {
    return formatPrice(props.product.primary_compare_at_price, { showUnit: false })
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
    <div class="relative aspect-square w-full overflow-hidden bg-neutral-100 dark:bg-neutral-800/80 border-b border-neutral-100 dark:border-neutral-800/60 flex items-center justify-center">
      <img
        v-if="product.thumbnail"
        :src="product.thumbnail"
        :alt="product.name"
        class="h-full w-full object-cover object-center transition-transform duration-700 ease-out group-hover:scale-106"
        loading="lazy"
      >
      <div
        v-else
        class="size-16 rounded-2xl bg-primary/10 text-primary flex items-center justify-center text-2xl font-black"
      >
        {{ product.name.charAt(0) }}
      </div>

      <!-- Subtle bottom gradient vignette for smooth transition into card -->
      <div class="absolute inset-x-0 bottom-0 h-10 bg-gradient-to-t from-black/25 via-black/5 to-transparent pointer-events-none" />

      <!-- Top Badges (Discount & Featured) -->
      <div class="absolute top-3 start-3 flex flex-col gap-1.5 items-start z-10 pointer-events-none">
        <span
          v-if="discountPercent"
          class="px-2.5 py-1 rounded-xl bg-primary-500 text-white font-black text-xs shadow-md shadow-primary-500/30 tracking-tight"
        >
          {{ discountPercent }}
        </span>
        <span
          v-if="product.is_featured"
          class="px-2.5 py-1 rounded-xl bg-neutral-900/85 dark:bg-black/80 text-amber-400 border border-amber-400/35 backdrop-blur-md font-bold text-[11px] shadow-sm flex items-center gap-1"
        >
          <UIcon
            name="i-lucide-sparkles"
            class="size-3 text-amber-400"
          />
          <span>ویژه</span>
        </span>
      </div>

      <!-- Wishlist Heart Button -->
      <button
        type="button"
        class="absolute top-3 end-3 z-20 w-8 h-8 rounded-xl bg-white/90 dark:bg-neutral-900/90 backdrop-blur-md border border-neutral-200/70 dark:border-neutral-700/70 flex items-center justify-center shadow-xs transition-all active:scale-90 hover:scale-110 cursor-pointer"
        :class="[
          wishlistStore.isInWishlist(product.id)
            ? 'text-primary'
            : 'text-neutral-400 hover:text-primary'
        ]"
        title="علاقه‌مندی‌ها"
        @click.prevent.stop="wishlistStore.toggleWishlist(product.id)"
      >
        <UIcon
          name="i-lucide-heart"
          class="w-4 h-4 transition-transform"
          :class="{ 'fill-primary text-primary': wishlistStore.isInWishlist(product.id) }"
        />
      </button>

      <!-- Quick Action Floating Overlay on Desktop Hover -->
      <div class="absolute inset-x-3 bottom-3 hidden sm:flex items-center justify-center opacity-0 group-hover:opacity-100 transition-all duration-300 translate-y-2 group-hover:translate-y-0 z-10 pointer-events-none">
        <div class="w-full py-2.5 px-3 rounded-2xl bg-neutral-950/85 dark:bg-neutral-900/95 text-white border border-white/10 backdrop-blur-md text-xs font-bold text-center shadow-xl flex items-center justify-center gap-1.5">
          <UIcon
            name="i-lucide-eye"
            class="size-3.5 text-primary"
          />
          <span>مشاهده و انتخاب</span>
        </div>
      </div>

      <!-- Out of Stock Overlay -->
      <div
        v-if="!product.is_in_stock"
        class="absolute inset-0 bg-neutral-900/70 backdrop-blur-[2px] flex items-center justify-center z-20"
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
            class="text-xs text-neutral-400 line-through decoration-primary/50"
          >
            {{ compareAtPrice }}
          </span>
          <div class="flex items-baseline gap-1">
            <span class="text-base sm:text-lg font-black text-neutral-900 dark:text-white">
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
