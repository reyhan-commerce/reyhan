<script setup lang="ts">
import type { ProductCardItem } from '~/types/product'
import ProductCard from '~/components/catalog/ProductCard.vue'
import ProductCardSkeleton from '~/components/skeletons/ProductCardSkeleton.vue'

interface Props {
  products: ProductCardItem[]
  loading?: boolean
  sectionTitle?: string | null
  buttonText?: string | null
}

defineProps<Props>()
</script>

<template>
  <section class="flex flex-col gap-6">
    <div class="flex items-center justify-between">
      <div class="flex items-center gap-3">
        <div class="w-1.5 h-6 rounded-full bg-primary" />
        <h2 class="text-xl sm:text-2xl font-black text-neutral-900 dark:text-white">
          {{ sectionTitle || 'محصولات برگزیده فروشگاه' }}
        </h2>
      </div>
      <UButton
        to="/products"
        color="neutral"
        variant="ghost"
        trailing-icon="i-lucide-arrow-left"
        size="sm"
        class="font-bold hover:text-primary"
      >
        {{ buttonText || 'مشاهده همه کاتالوگ' }}
      </UButton>
    </div>

    <div
      v-if="loading"
      class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4 sm:gap-6"
    >
      <ProductCardSkeleton
        v-for="i in 8"
        :key="i"
      />
    </div>
    <div
      v-else
      class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4 sm:gap-6"
    >
      <ProductCard
        v-for="product in products"
        :key="product.id"
        :product="product"
      />
    </div>
  </section>
</template>
