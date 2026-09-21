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
  <div class="flex items-center gap-3 py-3 border-b border-gray-100 dark:border-gray-800 last:border-b-0">
    <!-- Thumbnail -->
    <div class="relative w-16 h-16 shrink-0 rounded-xl overflow-hidden bg-gray-50 dark:bg-gray-800 border border-gray-100 dark:border-gray-700/60 flex items-center justify-center">
      <img
        v-if="item.variant?.product?.thumbnail"
        :src="item.variant.product.thumbnail"
        :alt="item.variant.product.name"
        class="w-full h-full object-cover"
        loading="lazy"
      >
      <UIcon
        v-else
        name="i-lucide-package"
        class="w-7 h-7 text-gray-400"
      />
    </div>

    <!-- Info & Controls -->
    <div class="flex-1 min-w-0">
      <!-- Title & Brand -->
      <div class="flex items-start justify-between gap-2">
        <NuxtLink
          v-if="item.variant?.product?.slug"
          :to="`/product/${item.variant.product.slug}`"
          class="text-xs sm:text-sm font-medium text-gray-900 dark:text-gray-100 hover:text-primary-600 dark:hover:text-primary-400 line-clamp-1 transition-colors"
          @click="cartStore.closeSlideover"
        >
          {{ item.variant.product.name }}
        </NuxtLink>
        <span
          v-else
          class="text-xs sm:text-sm font-medium text-gray-900 dark:text-gray-100 line-clamp-1"
        >
          کالای نامشخص
        </span>

        <!-- Delete button -->
        <UButton
          color="neutral"
          variant="ghost"
          size="xs"
          icon="i-lucide-x"
          class="text-gray-400 hover:text-red-500 shrink-0"
          :disabled="isUpdating"
          @click="handleRemove"
        />
      </div>

      <!-- Variant title -->
      <div
        v-if="item.variant?.title"
        class="text-xs text-gray-500 dark:text-gray-400 mt-0.5"
      >
        {{ item.variant.title }}
      </div>

      <!-- Price & Quantity row -->
      <div class="flex items-center justify-between mt-2.5">
        <!-- Quantity control -->
        <div class="flex items-center border border-gray-200 dark:border-gray-700 rounded-lg p-0.5 bg-gray-50/50 dark:bg-gray-800/50">
          <UButton
            color="neutral"
            variant="ghost"
            size="xs"
            :icon="item.quantity === 1 ? 'i-lucide-trash-2' : 'i-lucide-minus'"
            :class="item.quantity === 1 ? 'text-red-500 hover:bg-red-50 dark:hover:bg-red-950/30' : 'text-gray-600 dark:text-gray-300'"
            :disabled="isUpdating"
            @click="handleDecrease"
          />

          <span class="w-7 text-center text-xs sm:text-sm font-bold text-gray-800 dark:text-gray-200">
            <UIcon
              v-if="isUpdating"
              name="i-lucide-loader-2"
              class="w-3.5 h-3.5 animate-spin mx-auto text-primary-500"
            />
            <span v-else>{{ toPersianDigits(item.quantity) }}</span>
          </span>

          <UButton
            color="neutral"
            variant="ghost"
            size="xs"
            icon="i-lucide-plus"
            class="text-gray-600 dark:text-gray-300"
            :disabled="Boolean(isUpdating || (item.variant && item.quantity >= Math.min(item.variant.stock, 10)))"
            @click="handleIncrease"
          />
        </div>

        <!-- Prices -->
        <div class="text-left flex flex-col items-end">
          <div class="text-xs sm:text-sm font-extrabold text-primary-600 dark:text-primary-400">
            {{ formatPrice(item.subtotal) }}
          </div>
          <div
            v-if="item.discount_amount > 0"
            class="text-[11px] text-gray-400 line-through"
          >
            {{ formatPrice(item.original_subtotal) }}
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
