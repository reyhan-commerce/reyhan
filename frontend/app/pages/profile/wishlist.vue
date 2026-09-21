<script setup lang="ts">
import { useCartStore } from '~/stores/cart'
import { useWishlistStore } from '~/stores/wishlist'

const wishlistStore = useWishlistStore()
const cartStore = useCartStore()
const { formatPrice } = usePersian()

useSeoMeta({
  title: 'لیست علاقه‌مندی‌ها - ایزیشاپ',
})

onMounted(async () => {
  await wishlistStore.fetchWishlist()
})

const handleAddToCart = async (product: any) => {
  const variantId = product.variants?.[0]?.id || product.id
  await cartStore.addItem(variantId, 1)
}
</script>

<template>
  <div class="flex flex-col gap-6">
    <!-- Header -->
    <div class="bg-white dark:bg-neutral-900 border border-neutral-200/80 dark:border-neutral-800/80 rounded-3xl p-5 shadow-xs">
      <h2 class="text-lg font-black text-neutral-900 dark:text-neutral-100">
        کالاهای مورد علاقه
      </h2>
      <p class="text-xs text-neutral-400 mt-0.5">
        محصولاتی که برای بررسی یا خرید در آینده ذخیره کرده‌اید
      </p>
    </div>

    <!-- Loading State -->
    <div
      v-if="wishlistStore.isLoading"
      class="flex items-center justify-center p-16"
    >
      <UIcon
        name="i-lucide-loader-2"
        class="w-8 h-8 text-primary-500 animate-spin"
      />
    </div>

    <!-- Empty State -->
    <div
      v-else-if="wishlistStore.items.length === 0"
      class="bg-white dark:bg-neutral-900 border border-neutral-200/80 dark:border-neutral-800/80 rounded-3xl p-12 text-center shadow-xs flex flex-col items-center justify-center gap-4"
    >
      <div class="w-16 h-16 rounded-full bg-rose-50 dark:bg-rose-950/30 text-rose-500 flex items-center justify-center">
        <UIcon
          name="i-lucide-heart-off"
          class="w-8 h-8"
        />
      </div>
      <div class="flex flex-col gap-1">
        <h3 class="font-bold text-neutral-800 dark:text-neutral-200 text-base">
          لیست علاقه‌مندی‌های شما خالی است
        </h3>
        <p class="text-xs text-neutral-400">
          با کلیک بر روی آیکون قلب در صفحه محصولات، کالاهای مورد علاقه خود را ذخیره کنید.
        </p>
      </div>
      <UButton
        to="/products"
        color="primary"
        size="md"
        icon="i-lucide-shopping-bag"
        class="mt-2 font-bold cursor-pointer"
      >
        مشاهده کاتالوگ محصولات
      </UButton>
    </div>

    <!-- Wishlist Grid -->
    <div
      v-else
      class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-4"
    >
      <div
        v-for="item in wishlistStore.items"
        :key="item.id"
        class="bg-white dark:bg-neutral-900 border border-neutral-200/80 dark:border-neutral-800/80 rounded-3xl p-4 shadow-xs flex flex-col justify-between gap-4 group relative overflow-hidden transition-all hover:shadow-md"
      >
        <!-- Remove from wishlist button -->
        <button
          type="button"
          class="absolute top-3 left-3 z-10 w-8 h-8 rounded-full bg-white/90 dark:bg-neutral-800/90 text-neutral-400 hover:text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-950/50 transition-colors flex items-center justify-center shadow-xs cursor-pointer"
          title="حذف از لیست علاقه‌مندی‌ها"
          @click="wishlistStore.toggleWishlist(item.product.id)"
        >
          <UIcon
            name="i-lucide-trash-2"
            class="w-4 h-4"
          />
        </button>

        <!-- Product Image & Link -->
        <NuxtLink
          :to="`/products/${item.product.slug}`"
          class="flex flex-col gap-3"
        >
          <div class="aspect-square rounded-2xl bg-neutral-100 dark:bg-neutral-800 overflow-hidden flex items-center justify-center">
            <img
              v-if="item.product.thumbnail"
              :src="item.product.thumbnail"
              :alt="item.product.name"
              class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
              loading="lazy"
            >
            <UIcon
              v-else
              name="i-lucide-image"
              class="w-12 h-12 text-neutral-300 dark:text-neutral-600"
            />
          </div>

          <div class="flex flex-col gap-1 text-right">
            <span
              v-if="item.product.brand?.name"
              class="text-[11px] font-semibold text-primary-600 dark:text-primary-400"
            >
              {{ item.product.brand.name }}
            </span>
            <h3 class="font-bold text-xs sm:text-sm text-neutral-900 dark:text-neutral-100 line-clamp-2 leading-snug">
              {{ item.product.name }}
            </h3>
          </div>
        </NuxtLink>

        <!-- Price and Action -->
        <div class="pt-3 border-t border-neutral-100 dark:border-neutral-800 flex items-center justify-between gap-2">
          <div class="flex flex-col">
            <span class="text-[10px] text-neutral-400">قیمت:</span>
            <span class="font-black text-xs sm:text-sm text-neutral-900 dark:text-neutral-100">
              {{ formatPrice(item.product.primary_price) }}
            </span>
          </div>

          <UButton
            :to="`/products/${item.product.slug}`"
            size="xs"
            color="primary"
            variant="subtle"
            icon="i-lucide-arrow-left"
            trailing
            class="font-bold cursor-pointer"
          >
            مشاهده کالا
          </UButton>
        </div>
      </div>
    </div>
  </div>
</template>
