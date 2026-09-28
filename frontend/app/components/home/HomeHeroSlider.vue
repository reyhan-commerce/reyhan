<script setup lang="ts">
import type { BannerItem } from '~/types/content'

interface Props {
  banners: BannerItem[]
  storeName: string
  storeSlogan?: string | null
  badgeText?: string | null
  primaryButtonText?: string | null
  secondaryButtonText?: string | null
}

const props = withDefaults(defineProps<Props>(), {
  storeSlogan: null,
  badgeText: null,
  primaryButtonText: null,
  secondaryButtonText: null
})

const { getMediaUrl } = useMediaUrl()

const sliderBanners = computed(() =>
  props.banners
    .filter(b => b.position === 'home_slider')
    .sort((a, b) => a.order - b.order)
)
</script>

<template>
  <!-- Fallback to Store Hero Glass Card when no slider banners exist -->
  <HomeHeroBanner
    v-if="sliderBanners.length === 0"
    :store-name="storeName"
    :store-slogan="storeSlogan"
    :badge-text="badgeText"
    :primary-button-text="primaryButtonText"
    :secondary-button-text="secondaryButtonText"
  />

  <!-- High-Impact Interactive Hero Slider using Nuxt UI UCarousel -->
  <section
    v-else
    class="relative w-full overflow-hidden rounded-3xl border border-neutral-200/80 dark:border-neutral-800 shadow-md group"
  >
    <UCarousel
      v-slot="{ item: banner }"
      :items="sliderBanners"
      :arrows="sliderBanners.length > 1"
      :dots="sliderBanners.length > 1"
      :autoplay="sliderBanners.length > 1 ? { delay: 5000 } : false"
      loop
      class="w-full"
      :prev="{
        color: 'neutral',
        variant: 'solid',
        icon: 'i-lucide-chevron-right',
        class: 'size-10 sm:size-12 rounded-full bg-black/60 hover:bg-black/90 text-white backdrop-blur-md border border-white/20 shadow-xl flex items-center justify-center p-0 transition-all duration-300 opacity-80 group-hover:opacity-100 hover:scale-105'
      }"
      :next="{
        color: 'neutral',
        variant: 'solid',
        icon: 'i-lucide-chevron-left',
        class: 'size-10 sm:size-12 rounded-full bg-black/60 hover:bg-black/90 text-white backdrop-blur-md border border-white/20 shadow-xl flex items-center justify-center p-0 transition-all duration-300 opacity-80 group-hover:opacity-100 hover:scale-105'
      }"
      :ui="{
        item: 'basis-full',
        prev: 'absolute start-3 sm:start-6 top-1/2 -translate-y-1/2 z-20',
        next: 'absolute end-3 sm:end-6 top-1/2 -translate-y-1/2 z-20',
        dots: 'absolute bottom-4 sm:bottom-6 inset-x-0 z-20 flex items-center justify-center gap-2',
        dot: 'cursor-pointer size-2.5 rounded-full transition-all duration-300 bg-white/50 data-[state=active]:w-7 data-[state=active]:bg-primary'
      }"
    >
      <NuxtLink
        :to="banner.link_url || '#'"
        :class="{ 'pointer-events-none': !banner.link_url }"
        class="block w-full h-full relative aspect-[16/9] sm:aspect-[21/9] lg:aspect-[24/9] overflow-hidden bg-neutral-900 select-none"
      >
        <!-- Responsive Desktop / Mobile Picture -->
        <picture class="w-full h-full">
          <source
            v-if="banner.mobile_image_url"
            media="(max-width: 640px)"
            :srcset="getMediaUrl(banner.mobile_image_url)"
          />
          <img
            :src="getMediaUrl(banner.image_url)"
            :alt="banner.title"
            class="w-full h-full object-cover transform transition-transform duration-700 ease-out group-hover:scale-102"
            loading="lazy"
          />
        </picture>

        <!-- Luxury Gradient Scrim Overlay -->
        <div class="absolute inset-0 bg-gradient-to-t from-black/85 via-black/35 to-black/10 flex flex-col justify-end p-6 sm:p-10 lg:p-14 text-white">
          <div class="max-w-2xl flex flex-col gap-2 sm:gap-3 items-start text-start">
            <span
              v-if="badgeText || banner.subtitle"
              class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-white/20 backdrop-blur-md text-white text-xs sm:text-sm font-bold border border-white/25 drop-shadow"
            >
              <UIcon name="i-lucide-sparkles" class="size-3.5 text-primary-300" />
              <span>{{ banner.subtitle || badgeText }}</span>
            </span>

            <h2 class="text-xl sm:text-3xl lg:text-5xl font-black drop-shadow-md leading-tight text-white">
              {{ banner.title }}
            </h2>

            <div v-if="banner.link_url" class="pt-2">
              <span class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-primary text-white font-bold text-xs sm:text-sm shadow-lg shadow-primary/30 hover:bg-primary-600 transition-colors">
                <span>مشاهده پیشنهاد</span>
                <UIcon name="i-lucide-arrow-left" class="size-4" />
              </span>
            </div>
          </div>
        </div>
      </NuxtLink>
    </UCarousel>
  </section>
</template>
