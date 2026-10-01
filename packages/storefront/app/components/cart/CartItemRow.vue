<script setup lang="ts">
import type { CartItem } from '~/stores/cart'

const props = defineProps<{
  item: CartItem
  compact?: boolean
}>()

const cartStore = useCartStore()
const { formatPrice, toPersianDigits } = usePersian()

const isUpdating = computed(() => cartStore.isUpdatingItem === props.item.id)

const handleIncrease = () => {
  if (isUpdating.value) return
  const maxStock = props.item.variant?.stock ?? 10
  const maxAllowed = Math.min(maxStock, 10)
  if (props.item.quantity < maxAllowed) {
    cartStore.updateQuantity(props.item.id, props.item.quantity + 1)
  }
}

const handleDecrease = () => {
  if (isUpdating.value) return
  if (props.item.quantity > 1) {
    cartStore.updateQuantity(props.item.id, props.item.quantity - 1)
  } else {
    cartStore.removeItem(props.item.id)
  }
}

const handleRemove = () => {
  if (isUpdating.value) return
  cartStore.removeItem(props.item.id)
}
</script>

<template>
  <div class="flex items-start sm:items-center gap-3.5 sm:gap-4 py-4 sm:py-5 border-b border-neutral-100 dark:border-neutral-800/80 last:border-b-0 transition-colors">
    <!-- Thumbnail -->
    <div
      class="relative shrink-0 rounded-2xl overflow-hidden bg-neutral-100 dark:bg-neutral-800 border border-neutral-200/80 dark:border-neutral-700/60 flex items-center justify-center transition-all"
      :class="compact ? 'size-16' : 'size-18 sm:size-22'"
    >
      <NuxtImg
        v-if="item.variant?.product?.thumbnail"
        :src="item.variant.product.thumbnail"
        :alt="item.variant.product.name"
        format="webp"
        loading="lazy"
        :sizes="compact ? '64px' : '96px'"
        class="size-full object-cover"
      />
      <UIcon
        v-else
        name="i-lucide-package"
        class="size-8 text-neutral-400 dark:text-neutral-500"
      />
    </div>

    <!-- Info & Controls -->
    <div class="flex-1 min-w-0 flex flex-col gap-2">
      <!-- Title & Remove button -->
      <div class="flex items-start justify-between gap-3">
        <div class="flex flex-col min-w-0">
          <span
            v-if="item.variant?.product?.brand"
            class="text-[11px] font-bold text-primary dark:text-primary-400 mb-0.5"
          >
            {{ item.variant.product.brand }}
          </span>

          <NuxtLink
            v-if="item.variant?.product?.slug"
            :to="`/product/${item.variant.product.slug}`"
            class="text-xs sm:text-sm font-bold text-neutral-900 dark:text-neutral-100 hover:text-primary dark:hover:text-primary transition-colors line-clamp-2 leading-relaxed"
            @click="cartStore.closeSlideover"
          >
            {{ item.variant.product.name }}
          </NuxtLink>
          <span
            v-else
            class="text-xs sm:text-sm font-bold text-neutral-900 dark:text-neutral-100 line-clamp-2"
          >
            کالای نامشخص
          </span>
        </div>

        <!-- Delete button -->
        <button
          type="button"
          class="size-8 rounded-xl flex items-center justify-center text-neutral-400 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-950/30 transition-colors shrink-0 cursor-pointer disabled:opacity-50"
          :disabled="isUpdating"
          title="حذف از سبد خرید"
          aria-label="حذف کالا"
          @click="handleRemove"
        >
          <UIcon
            name="i-lucide-trash-2"
            class="size-4"
          />
        </button>
      </div>

      <!-- Variant title / Attributes -->
      <div
        v-if="item.variant?.title"
        class="flex items-center gap-1.5 text-xs text-neutral-500 dark:text-neutral-400"
      >
        <span class="size-1.5 rounded-full bg-primary" />
        <span>{{ item.variant.title }}</span>
      </div>

      <!-- Price & Quantity row -->
      <div class="flex flex-wrap items-center justify-between gap-3 pt-1">
        <!-- Quantity control -->
        <div class="flex items-center border border-neutral-200 dark:border-neutral-700/80 rounded-xl p-1 bg-neutral-50/80 dark:bg-neutral-800/80 shadow-2xs">
          <button
            type="button"
            class="size-7 sm:size-8 rounded-lg flex items-center justify-center transition-colors cursor-pointer disabled:opacity-40"
            :class="item.quantity === 1 ? 'text-red-500 hover:bg-red-50 dark:hover:bg-red-950/40' : 'text-neutral-600 dark:text-neutral-300 hover:bg-white dark:hover:bg-neutral-700'"
            :disabled="isUpdating"
            :aria-label="item.quantity === 1 ? 'حذف کالا' : 'کاهش تعداد'"
            @click="handleDecrease"
          >
            <UIcon
              :name="item.quantity === 1 ? 'i-lucide-trash-2' : 'i-lucide-minus'"
              class="size-3.5 sm:size-4"
            />
          </button>

          <span class="w-8 sm:w-9 text-center text-xs sm:text-sm font-black text-neutral-900 dark:text-neutral-100 select-none">
            <UIcon
              v-if="isUpdating"
              name="i-lucide-loader-2"
              class="size-3.5 animate-spin mx-auto text-primary"
            />
            <span v-else>{{ toPersianDigits(item.quantity) }}</span>
          </span>

          <button
            type="button"
            class="size-7 sm:size-8 rounded-lg flex items-center justify-center text-neutral-600 dark:text-neutral-300 hover:bg-white dark:hover:bg-neutral-700 transition-colors cursor-pointer disabled:opacity-40"
            :disabled="Boolean(isUpdating || (item.variant && item.quantity >= Math.min(item.variant.stock, 10)))"
            aria-label="افزایش تعداد"
            @click="handleIncrease"
          >
            <UIcon
              name="i-lucide-plus"
              class="size-3.5 sm:size-4"
            />
          </button>
        </div>

        <!-- Prices -->
        <div class="text-left flex flex-col items-end">
          <div class="flex items-center gap-2">
            <span
              v-if="item.discount_amount > 0"
              class="text-[10px] font-bold text-red-600 dark:text-red-400 bg-red-50 dark:bg-red-950/40 px-1.5 py-0.5 rounded-md"
            >
              {{ formatPrice(item.discount_amount) }} تخفیف
            </span>
            <span class="text-sm sm:text-base font-black text-primary">
              {{ formatPrice(item.subtotal) }}
            </span>
          </div>

          <div
            v-if="item.discount_amount > 0"
            class="text-[11px] text-neutral-400 line-through mt-0.5"
          >
            {{ formatPrice(item.original_subtotal) }}
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
