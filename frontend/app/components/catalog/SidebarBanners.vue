<script setup lang="ts">
import type { BannerItem } from '~/types/content'

interface Props {
  banners: BannerItem[]
}

const props = defineProps<Props>()

const { getMediaUrl } = useMediaUrl()

const sidebarBanners = computed(() =>
  props.banners
    .filter(b => b.position === 'sidebar')
    .sort((a, b) => a.order - b.order)
)
</script>

<template>
  <div v-if="sidebarBanners.length > 0" class="flex flex-col gap-4">
    <div
      v-for="banner in sidebarBanners"
      :key="banner.id"
      class="group relative overflow-hidden rounded-3xl border border-neutral-200/80 dark:border-neutral-800 bg-white dark:bg-neutral-900 shadow-xs hover:shadow-md transition-all duration-300"
    >
      <NuxtLink
        :to="banner.link_url || '#'"
        :class="{ 'pointer-events-none': !banner.link_url }"
        class="block"
      >
        <div class="relative aspect-[4/5] sm:aspect-[3/4] w-full overflow-hidden bg-neutral-100 dark:bg-neutral-800">
          <picture class="w-full h-full">
            <source
              v-if="banner.mobile_image_url"
              media="(max-width: 640px)"
              :srcset="getMediaUrl(banner.mobile_image_url)"
            />
            <img
              :src="getMediaUrl(banner.image_url)"
              :alt="banner.title"
              loading="lazy"
              class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105"
            />
          </picture>
          <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/30 to-transparent flex flex-col justify-end p-4 text-white">
            <h4 class="font-extrabold text-sm sm:text-base drop-shadow">
              {{ banner.title }}
            </h4>
            <p v-if="banner.subtitle" class="text-xs text-neutral-200 mt-0.5 line-clamp-2 drop-shadow">
              {{ banner.subtitle }}
            </p>
          </div>
        </div>
      </NuxtLink>
    </div>
  </div>
</template>
