<script setup lang="ts">
import type { BannerItem } from '~/types/content'

interface Props {
  banners: BannerItem[]
  position?: 'home_middle' | 'home_grid' | 'all'
}

const props = withDefaults(defineProps<Props>(), {
  position: 'all'
})

const { getMediaUrl } = useMediaUrl()

const displayBanners = computed(() => {
  if (props.position === 'home_middle') {
    return props.banners.filter(b => b.position === 'home_middle').sort((a, b) => a.order - b.order)
  }
  if (props.position === 'home_grid') {
    return props.banners.filter(b => b.position === 'home_grid').sort((a, b) => a.order - b.order)
  }
  return props.banners
    .filter(b => b.position === 'home_middle' || b.position === 'home_grid')
    .sort((a, b) => a.order - b.order)
})
</script>

<template>
  <div
    v-if="displayBanners.length > 0"
    class="grid gap-4 sm:gap-6 my-2"
    :class="[
      displayBanners.length === 1
        ? 'grid-cols-1'
        : 'grid-cols-1 sm:grid-cols-2 lg:grid-cols-2'
    ]"
  >
    <div
      v-for="banner in displayBanners"
      :key="banner.id"
      class="group relative overflow-hidden rounded-3xl border border-neutral-200 dark:border-neutral-800 shadow-sm hover:shadow-lg transition-all duration-300"
    >
      <NuxtLink
        :to="banner.link_url || '#'"
        :class="{ 'pointer-events-none': !banner.link_url }"
        class="block"
      >
        <div
          class="relative w-full overflow-hidden bg-neutral-100 dark:bg-neutral-800"
          :class="[
            banner.position === 'home_middle' && displayBanners.length === 1
              ? 'aspect-[16/7] sm:aspect-[24/8] lg:aspect-[28/8]'
              : 'aspect-[21/9] sm:aspect-[16/7]'
          ]"
        >
          <picture class="w-full h-full">
            <source
              v-if="banner.mobile_image_url"
              media="(max-width: 640px)"
              :srcset="getMediaUrl(banner.mobile_image_url)"
            >
            <img
              :src="getMediaUrl(banner.image_url)"
              :alt="banner.title"
              loading="lazy"
              class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105"
            >
          </picture>
          <div class="absolute inset-0 bg-gradient-to-t from-black/75 via-black/25 to-transparent flex flex-col justify-end p-5 sm:p-7 text-white">
            <h3 class="font-extrabold text-base sm:text-lg lg:text-xl drop-shadow">
              {{ banner.title }}
            </h3>
            <p
              v-if="banner.subtitle"
              class="text-xs sm:text-sm text-neutral-200 mt-1 line-clamp-1 drop-shadow"
            >
              {{ banner.subtitle }}
            </p>
          </div>
        </div>
      </NuxtLink>
    </div>
  </div>
</template>
