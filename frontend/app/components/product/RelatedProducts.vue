<script setup lang="ts">
import type { ProductCardItem } from '~/types/product'
import ProductCard from '~/components/catalog/ProductCard.vue'

const props = defineProps<{
  productSlug: string
}>()

const api = useApi()
const relatedProducts = ref<ProductCardItem[]>([])
const isLoading = ref(true)

onMounted(async () => {
  try {
    const res = await api<{ success: boolean, data: ProductCardItem[] }>(`/products/${props.productSlug}/related`)
    if (res?.data) {
      relatedProducts.value = res.data
    }
  } catch {
    // ignore
  } finally {
    isLoading.value = false
  }
})
</script>

<template>
  <div
    v-if="relatedProducts.length > 0"
    class="flex flex-col gap-6"
  >
    <div class="flex items-center gap-2">
      <div class="w-1.5 h-6 rounded-full bg-primary" />
      <h2 class="text-xl font-black text-neutral-900 dark:text-white">
        کالاهای مشابه و پیشنهادی
      </h2>
    </div>

    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4 sm:gap-6">
      <ProductCard
        v-for="item in relatedProducts"
        :key="item.id"
        :product="item"
      />
    </div>
  </div>
</template>
