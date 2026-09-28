<script setup lang="ts">
import CategoryNav from '~/components/catalog/CategoryNav.vue'
import HomeHeroBanner from '~/components/home/HomeHeroBanner.vue'
import HomeTrustBadges from '~/components/home/HomeTrustBadges.vue'
import HomeFlashDeals from '~/components/home/HomeFlashDeals.vue'
import HomeFeaturedProducts from '~/components/home/HomeFeaturedProducts.vue'
import HomeBlogSection from '~/components/home/HomeBlogSection.vue'
import HomeBrandsSection, { type BrandDisplayItem } from '~/components/home/HomeBrandsSection.vue'
import HomeBannersGrid, { type BannerItem } from '~/components/home/HomeBannersGrid.vue'
import type { BlogPost } from '~/types/blog'
import type { ApiResponse } from '~/types/api'
import { useCatalogService } from '~/services/catalogService'

const catalogStore = useCatalogStore()
const catalogService = useCatalogService()
const settingsStore = useSettingsStore()
const features = useFeatures()
const api = useApi()

// Fetch category tree and isolated featured products for homepage
const { data: homeProductsData, status: homeProductsStatus } = await useAsyncData('home-products', () => {
  return catalogService.getProducts({ sort: 'featured', page: 1 })
})
const homeProducts = computed(() => homeProductsData.value?.items ?? [])
const isHomeLoading = computed(() => homeProductsStatus.value === 'pending')

await useAsyncData('home-category-tree', () => catalogStore.fetchCategoryTree())

// Fetch featured blog articles in SSR if blog feature is enabled
const { data: featuredArticlesResponse } = await useAsyncData('home-featured-articles', () => {
  if (!features.hasFeature('blog')) return Promise.resolve(null)
  return api<ApiResponse<BlogPost[]>>('/blog/featured').catch(() => null)
})
const featuredArticles = computed(() => featuredArticlesResponse.value?.data?.slice(0, 3) ?? [])

// Fetch active promotional banners in SSR
const { data: bannersResponse } = await useAsyncData('home-banners', () =>
  api<ApiResponse<BannerItem[]>>('/banners').catch(() => null)
)
const banners = computed(() => bannersResponse.value?.data ?? [])

// Flash deals (products with discounts)
const flashDeals = computed(() => {
  return homeProducts.value.filter(p => p.has_discount).slice(0, 4)
})

// Featured products
const featuredProducts = computed(() => {
  return homeProducts.value.slice(0, 8)
})

// Distinct brands for showcase
const brands = computed<BrandDisplayItem[]>(() => {
  const map = new Map<string, BrandDisplayItem>()
  for (const p of homeProducts.value) {
    if (p.brand && !map.has(p.brand.slug)) {
      map.set(p.brand.slug, {
        name: p.brand.name,
        slug: p.brand.slug,
        name_en: p.brand.name_en || p.brand.slug,
        icon: 'i-lucide-tag'
      })
    }
  }
  return Array.from(map.values())
})
</script>

<template>
  <div class="flex flex-col gap-10 sm:gap-14 lg:gap-18 pt-4 pb-16">
    <!-- Hero Banner with Liquid Glass & Luxury Accents -->
    <HomeHeroBanner
      :store-name="settingsStore.settings.store_name"
      :store-slogan="settingsStore.settings.store_slogan"
      :badge-text="settingsStore.settings.hero_badge_text"
      :primary-button-text="settingsStore.settings.hero_primary_button_text"
      :secondary-button-text="settingsStore.settings.hero_secondary_button_text"
    />

    <!-- Trust Badges Strip -->
    <HomeTrustBadges :badges="settingsStore.settings.trust_badges" />

    <!-- Visual Category Navigation Section -->
    <section class="flex flex-col gap-6">
      <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
          <div class="w-1.5 h-6 rounded-full bg-primary" />
          <h2 class="text-xl sm:text-2xl font-black text-neutral-900 dark:text-white">
            {{ settingsStore.settings.categories_title || 'دسته‌بندی‌های تخصصی' }}
          </h2>
        </div>
        <UButton
          to="/categories"
          color="neutral"
          variant="ghost"
          trailing-icon="i-lucide-arrow-left"
          size="sm"
          class="font-bold hover:text-primary"
        >
          {{ settingsStore.settings.categories_button_text || 'مشاهده نقشه کامل' }}
        </UButton>
      </div>

      <CategoryNav :categories="catalogStore.categoryTree" />
    </section>

    <!-- Flash Deals Section with Countdown -->
    <HomeFlashDeals
      :deals="flashDeals"
      :loading="isHomeLoading"
      :section-title="settingsStore.settings.flash_deals_title"
      :section-subtitle="settingsStore.settings.flash_deals_subtitle"
    />

    <!-- Middle / Grid Banners Strip -->
    <HomeBannersGrid
      v-if="banners.length > 0"
      :banners="banners"
    />

    <!-- Best Sellers / Featured Products -->
    <HomeFeaturedProducts
      :products="featuredProducts"
      :loading="isHomeLoading"
      :section-title="settingsStore.settings.featured_products_title"
      :button-text="settingsStore.settings.featured_products_button_text"
    />

    <!-- Latest Blog Articles Showcase -->
    <HomeBlogSection
      v-if="features.hasFeature('blog') && featuredArticles.length > 0"
      :articles="featuredArticles"
      :section-title="settingsStore.settings.blog_title"
      :button-text="settingsStore.settings.blog_button_text"
    />

    <!-- Brand Showcase Strip -->
    <HomeBrandsSection
      v-if="features.hasFeature('brands')"
      :brands="brands"
      :section-title="settingsStore.settings.brands_title"
    />
  </div>
</template>
