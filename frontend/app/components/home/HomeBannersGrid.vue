<script setup lang="ts">
export interface BannerItem {
  id: number
  title: string
  subtitle: string | null
  image_url: string
  mobile_image_url: string | null
  link_url: string | null
  position: string
  order: number
}

interface Props {
  banners: BannerItem[]
}

const props = defineProps<Props>()

const middleBanners = computed(() =>
  props.banners.filter(b => b.position === 'home_middle' || b.position === 'home_grid')
)
</script>

<template>
  <div v-if="middleBanners.length > 0" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-2 gap-4 sm:gap-6 my-2">
    <div
      v-for="banner in middleBanners"
      :key="banner.id"
      class="group relative overflow-hidden rounded-3xl border border-neutral-200 dark:border-neutral-800 shadow-sm hover:shadow-lg transition-all duration-300"
    >
      <NuxtLink :to="banner.link_url || '#'" :class="{ 'pointer-events-none': !banner.link_url }" class="block">
        <div class="relative aspect-[21/9] sm:aspect-[16/7] w-full overflow-hidden bg-neutral-100 dark:bg-neutral-800">
          <img
            :src="banner.image_url"
            :alt="banner.title"
            loading="lazy"
            class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105"
          />
          <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/20 to-transparent flex flex-col justify-end p-5 text-white">
            <h3 class="font-extrabold text-base sm:text-lg lg:text-xl drop-shadow">
              {{ banner.title }}
            </h3>
            <p v-if="banner.subtitle" class="text-xs sm:text-sm text-neutral-200 mt-1 line-clamp-1 drop-shadow">
              {{ banner.subtitle }}
            </p>
          </div>
        </div>
      </NuxtLink>
    </div>
  </div>
</template>
