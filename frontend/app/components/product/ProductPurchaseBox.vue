<script setup lang="ts">
import type { ProductVariantItem } from '~/types/product'
import { useCartStore } from '~/stores/cart'
import { usePersian } from '~/composables/usePersian'

const props = defineProps<{
  selectedVariant: ProductVariantItem | null
}>()

const cartStore = useCartStore()
const { formatPrice } = usePersian()

const quantity = ref(1)

function incrementQty() {
  if (props.selectedVariant && quantity.value < props.selectedVariant.stock) {
    quantity.value++
  }
}

function decrementQty() {
  if (quantity.value > 1) {
    quantity.value--
  }
}

async function handleAddToCart() {
  if (!props.selectedVariant || props.selectedVariant.stock === 0) return
  await cartStore.addItem(props.selectedVariant.id, quantity.value)
}
</script>

<template>
  <div
    v-if="selectedVariant && selectedVariant.stock > 0"
    class="hidden sm:flex items-center gap-4 pt-2"
  >
    <!-- Quantity Stepper -->
    <div class="flex items-center border border-neutral-200 dark:border-neutral-700 rounded-2xl p-1 bg-white dark:bg-neutral-800">
      <UButton
        color="neutral"
        variant="ghost"
        icon="i-lucide-plus"
        size="sm"
        class="size-10 rounded-xl"
        :disabled="quantity >= selectedVariant.stock"
        @click="incrementQty"
      />
      <span class="w-10 text-center font-bold text-base select-none">
        {{ quantity }}
      </span>
      <UButton
        color="neutral"
        variant="ghost"
        icon="i-lucide-minus"
        size="sm"
        class="size-10 rounded-xl"
        :disabled="quantity <= 1"
        @click="decrementQty"
      />
    </div>

    <!-- Add To Cart Primary Button -->
    <UButton
      color="primary"
      variant="solid"
      size="xl"
      icon="i-lucide-shopping-cart"
      class="flex-1 min-h-12 text-base font-bold rounded-2xl justify-center shadow-md shadow-primary/25 cursor-pointer"
      :loading="cartStore.isLoading"
      @click="handleAddToCart"
    >
      افزودن به سبد خرید • {{ formatPrice(selectedVariant.price * quantity) }}
    </UButton>
  </div>
</template>
