<script setup lang="ts">
import type { ProductVariantItem } from '~/types/product'
import { useCartStore } from '~/stores/cart'
import { useCompareStore } from '~/stores/compare'
import StockAlertModal from '~/components/product/StockAlertModal.vue'

const props = defineProps<{
  selectedVariant: ProductVariantItem | null
  productName: string
  productSlug: string
}>()

const cartStore = useCartStore()
const compareStore = useCompareStore()
const { formatPrice } = usePersian()

const quantity = ref(1)
const isAlertModalOpen = ref(false)

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
  <div class="hidden sm:flex flex-col gap-3 pt-2">
    <!-- In Stock Controls -->
    <div
      v-if="selectedVariant && selectedVariant.stock > 0"
      class="flex items-center gap-3"
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

      <!-- Compare Button -->
      <UButton
        color="neutral"
        :variant="compareStore.isInCompare(productSlug) ? 'soft' : 'outline'"
        size="xl"
        icon="i-lucide-arrow-left-right"
        class="min-h-12 rounded-2xl px-4 font-bold"
        :title="compareStore.isInCompare(productSlug) ? 'در لیست مقایسه' : 'افزودن به مقایسه'"
        @click="compareStore.addToCompare(productSlug, productName)"
      >
        <span class="hidden lg:inline text-xs">مقایسه</span>
      </UButton>
    </div>

    <!-- Out of Stock Controls -->
    <div
      v-else-if="selectedVariant && selectedVariant.stock <= 0"
      class="flex items-center gap-3 p-4 rounded-2xl bg-neutral-50 dark:bg-neutral-800/60 border border-neutral-200/80 dark:border-neutral-700/80"
    >
      <div class="flex-1 flex flex-col gap-0.5">
        <span class="text-sm font-bold text-neutral-800 dark:text-neutral-200">
          متأسفانه این تنوع در حال حاضر موجود نیست
        </span>
        <span class="text-xs text-neutral-500">
          می‌توانید ثبت کنید تا به محض موجود شدن با پیامک مطلع شوید.
        </span>
      </div>

      <UButton
        color="primary"
        variant="solid"
        size="md"
        leading-icon="i-lucide-bell-ring"
        class="font-bold rounded-xl"
        @click="isAlertModalOpen = true"
      >
        خبرم کن وقتی موجود شد
      </UButton>

      <UButton
        color="neutral"
        :variant="compareStore.isInCompare(productSlug) ? 'soft' : 'outline'"
        size="md"
        icon="i-lucide-arrow-left-right"
        class="rounded-xl px-3 font-bold"
        @click="compareStore.addToCompare(productSlug, productName)"
      >
        <span class="text-xs">مقایسه</span>
      </UButton>
    </div>

    <!-- Stock Alert Modal Component -->
    <StockAlertModal
      v-if="selectedVariant"
      v-model="isAlertModalOpen"
      :variant-id="selectedVariant.id"
      :product-name="productName"
      :variant-title="selectedVariant.title"
    />
  </div>
</template>
