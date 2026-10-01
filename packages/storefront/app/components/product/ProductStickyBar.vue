<script setup lang="ts">
import type { ProductVariantItem } from '~/types/product'
import { useCartStore } from '~/stores/cart'
import { usePersian } from '~/composables/usePersian'

const props = defineProps<{
  selectedVariant: ProductVariantItem | null
}>()

const cartStore = useCartStore()
const { formatPrice } = usePersian()

async function handleAddToCart() {
  if (!props.selectedVariant || props.selectedVariant.stock === 0) return
  await cartStore.addItem(props.selectedVariant.id, 1)
}
</script>

<template>
  <div
    v-if="selectedVariant"
    class="sm:hidden fixed inset-x-0 bottom-0 z-40 bg-white/95 dark:bg-neutral-900/95 backdrop-blur-md border-t border-neutral-200 dark:border-neutral-800 p-3.5 flex items-center justify-between gap-3 shadow-lg"
  >
    <div class="flex flex-col">
      <span class="text-[11px] text-neutral-400">قیمت نهایی:</span>
      <span class="text-base font-black text-neutral-900 dark:text-white">
        {{ formatPrice(selectedVariant.price) }}
      </span>
    </div>

    <div class="flex items-center gap-2">
      <UButton
        v-if="selectedVariant.stock > 0"
        color="primary"
        variant="solid"
        size="lg"
        icon="i-lucide-shopping-cart"
        class="min-h-11 px-5 font-bold rounded-xl cursor-pointer"
        :loading="cartStore.isLoading"
        @click="handleAddToCart"
      >
        افزودن به سبد
      </UButton>
      <UBadge
        v-else
        color="neutral"
        variant="solid"
        size="md"
      >
        ناموجود
      </UBadge>
    </div>
  </div>
</template>
