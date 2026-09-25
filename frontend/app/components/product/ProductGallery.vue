<script setup lang="ts">
import type { ProductGalleryItem } from '~/types/product'
import { useWishlistStore } from '~/stores/wishlist'

const props = defineProps<{
  productId: number
  productName: string
  isFeatured?: boolean
  gallery?: ProductGalleryItem[]
}>()

const wishlistStore = useWishlistStore()
const activeImageIndex = ref(0)

const activeImage = computed(() => {
  if (props.gallery && props.gallery.length > 0) {
    return props.gallery[activeImageIndex.value]?.url || props.gallery[0]?.url
  }
  return null
})
</script>

<template>
  <div class="flex flex-col gap-4">
    <!-- Main Large Image Container -->
    <div class="relative aspect-square rounded-3xl bg-neutral-100 dark:bg-neutral-900 border border-neutral-200/80 dark:border-neutral-800 flex items-center justify-center overflow-hidden shadow-sm group">
      <NuxtImg
        v-if="activeImage"
        :src="activeImage"
        :alt="productName"
        format="webp"
        loading="eager"
        sizes="sm:100vw md:50vw lg:600px"
        class="h-full w-full object-cover object-center transition-transform duration-500 group-hover:scale-105"
      />
      <div
        v-else
        class="size-28 rounded-3xl bg-primary/10 text-primary flex items-center justify-center text-4xl font-black"
      >
        {{ productName.charAt(0) }}
      </div>

      <!-- Featured Badge -->
      <div
        v-if="isFeatured"
        class="absolute top-4 start-4 z-10 pointer-events-none"
      >
        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-2xl bg-neutral-950/80 dark:bg-black/75 text-amber-400 border border-amber-400/40 backdrop-blur-md text-xs font-bold shadow-md">
          <UIcon
            name="i-lucide-sparkles"
            class="size-3.5 text-amber-400"
          />
          <span>کالای برگزیده</span>
        </span>
      </div>

      <!-- Wishlist Heart Button -->
      <div class="absolute top-4 end-4 z-10">
        <button
          type="button"
          class="size-10 rounded-2xl bg-white/90 dark:bg-neutral-900/90 backdrop-blur-md border border-neutral-200/80 dark:border-neutral-700/80 flex items-center justify-center shadow-md transition-all active:scale-90 hover:scale-110 cursor-pointer"
          :class="[
            wishlistStore.isInWishlist(productId)
              ? 'text-primary'
              : 'text-neutral-400 hover:text-primary'
          ]"
          title="افزودن به علاقه‌مندی‌ها"
          @click="wishlistStore.toggleWishlist(productId)"
        >
          <UIcon
            name="i-lucide-heart"
            class="w-5 h-5 transition-transform"
            :class="{ 'fill-primary text-primary': wishlistStore.isInWishlist(productId) }"
          />
        </button>
      </div>

      <!-- Full View Zoom Link -->
      <div
        v-if="activeImage"
        class="absolute bottom-4 end-4 z-10"
      >
        <a
          :href="activeImage"
          target="_blank"
          rel="noopener noreferrer"
          class="size-10 rounded-2xl bg-white/85 dark:bg-neutral-900/85 hover:bg-white dark:hover:bg-neutral-900 text-neutral-700 dark:text-neutral-200 border border-neutral-200/80 dark:border-neutral-700/80 backdrop-blur-md shadow-md flex items-center justify-center transition-all hover:scale-105"
          title="مشاهده در اندازه اصلی"
        >
          <UIcon
            name="i-lucide-maximize-2"
            class="size-4"
          />
        </a>
      </div>
    </div>

    <!-- Thumbnails Strip -->
    <div
      v-if="gallery && gallery.length > 1"
      class="flex items-center gap-3 overflow-x-auto pb-1 [scrollbar-width:none] [&::-webkit-scrollbar]:hidden"
    >
      <button
        v-for="(img, idx) in gallery"
        :key="img.id"
        type="button"
        class="size-18 rounded-2xl border-2 overflow-hidden transition-all shrink-0 bg-neutral-100 dark:bg-neutral-800 cursor-pointer"
        :class="activeImageIndex === idx ? 'border-primary ring-2 ring-primary/20 shadow-sm' : 'border-transparent opacity-65 hover:opacity-100'"
        @click="activeImageIndex = idx"
      >
        <NuxtImg
          :src="img.url"
          :alt="img.name"
          format="webp"
          loading="lazy"
          sizes="80px"
          class="h-full w-full object-cover"
        />
      </button>
    </div>
  </div>
</template>
