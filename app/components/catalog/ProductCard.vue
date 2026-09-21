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
    class="group h-full rounded-2xl bg-white dark:bg-neutral-900 border border-neutral-200/80 dark:border-neutral-800 hover:border-primary/50 dark:hover:border-primary/50 transition-all duration-200 overflow-hidden shadow-xs hover:shadow-md flex flex-col justify-between"
  >
    <!-- Product Thumbnail & Badges -->
    <div class="relative aspect-square w-full overflow-hidden bg-neutral-50 dark:bg-neutral-800/50 flex items-center justify-center p-4">
      <img
        v-if="product.thumbnail"
        :src="product.thumbnail"
        :alt="product.name"
        class="h-full w-full object-contain object-center transition-transform duration-300 group-hover:scale-105"
        loading="lazy"
      >
      <div
        v-else
        class="size-16 rounded-2xl bg-primary/10 text-primary flex items-center justify-center text-2xl font-bold"
      >
        {{ product.name.charAt(0) }}
      </div>

      <!-- Top Badges -->
      <div class="absolute top-2.5 start-2.5 flex flex-col gap-1.5 items-start">
        <UBadge
          v-if="discountPercent"
          color="error"
          variant="solid"
          size="sm"
          class="font-bold rounded-full"
        >
          {{ discountPercent }}
        </UBadge>
        <UBadge
          v-if="product.is_featured"
          color="warning"
          variant="subtle"
          size="xs"
        >
          ویژه
        </UBadge>
      </div>

      <!-- Out of Stock Overlay -->
      <div
        v-if="!product.is_in_stock"
        class="absolute inset-0 bg-neutral-900/40 backdrop-blur-[1px] flex items-center justify-center"
      >
        <UBadge
          color="neutral"
          variant="solid"
          size="md"
          class="font-bold px-3 py-1"
        >
          ناموجود
        </UBadge>
      </div>
    </div>

    <!-- Product Details -->
    <div class="p-3.5 sm:p-4 flex flex-col flex-1 justify-between gap-3">
      <div class="flex flex-col gap-1.5">
        <!-- Brand & Category -->
        <div class="flex items-center justify-between text-xs text-neutral-400">
          <span
            v-if="product.brand"
            class="font-medium text-neutral-500 dark:text-neutral-400 truncate"
          >
            {{ product.brand.name }}
          </span>
          <span
            v-else-if="product.category"
            class="truncate"
          >
            {{ product.category.name }}
          </span>
        </div>

        <!-- Product Name -->
        <h3 class="text-sm font-semibold text-neutral-800 dark:text-neutral-100 line-clamp-2 leading-relaxed group-hover:text-primary transition-colors">
          {{ product.name }}
        </h3>
      </div>

      <!-- Pricing Row -->
      <div class="pt-2 border-t border-neutral-100 dark:border-neutral-800/80 flex items-end justify-between">
        <div class="flex flex-col">
          <span
            v-if="compareAtPrice"
            class="text-xs text-neutral-400 line-through"
          >
            {{ compareAtPrice }}
          </span>
          <span class="text-sm sm:text-base font-bold text-neutral-900 dark:text-white">
            {{ displayPrice }}
          </span>
        </div>

        <div class="text-xs text-neutral-400">
          <span
            v-if="product.variants_count > 1"
            class="text-primary font-medium"
          >
            {{ product.variants_count }} مدل
          </span>
        </div>
      </div>
    </div>
  </NuxtLink>
</template>
