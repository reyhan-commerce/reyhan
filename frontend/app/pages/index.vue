<script setup lang="ts">
import CategoryNav from '~/components/catalog/CategoryNav.vue'
import HomeHeroBanner from '~/components/home/HomeHeroBanner.vue'
import HomeTrustBadges from '~/components/home/HomeTrustBadges.vue'
import HomeFlashDeals from '~/components/home/HomeFlashDeals.vue'
import HomeFeaturedProducts from '~/components/home/HomeFeaturedProducts.vue'
import HomeBlogSection from '~/components/home/HomeBlogSection.vue'
import HomeBrandsSection, { type BrandDisplayItem } from '~/components/home/HomeBrandsSection.vue'
import type { BlogPost } from '~/types/blog'
import type { ApiResponse } from '~/types/api'

const catalogStore = useCatalogStore()
const settingsStore = useSettingsStore()
const features = useFeatures()
const api = useApi()

// Fetch category tree and products in SSR
await useAsyncData('home-catalog', async () => {
  await Promise.all([
    catalogStore.fetchCategoryTree(),
    catalogStore.fetchProducts({ sort: 'featured', page: 1 })
  ])
  return true
})

// Fetch featured blog articles in SSR if blog feature is enabled
const { data: featuredArticlesResponse } = await useAsyncData('home-featured-articles', () => {
  if (!features.hasFeature('blog')) return Promise.resolve(null)
  return api<ApiResponse<BlogPost[]>>('/blog/featured').catch(() => null)
})
const featuredArticles = computed(() => featuredArticlesResponse.value?.data?.slice(0, 3) ?? [])

// Flash deals (products with discounts)
const flashDeals = computed(() => {
  return catalogStore.products.filter(p => p.has_discount).slice(0, 4)
})

// Featured products
const featuredProducts = computed(() => {
  return catalogStore.products.slice(0, 8)
})

// Distinct brands for showcase
const brands = computed<BrandDisplayItem[]>(() => {
  const map = new Map<string, BrandDisplayItem>()
  for (const p of catalogStore.products) {
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
    />

    <!-- Trust Badges Strip -->
    <HomeTrustBadges />

    <!-- Visual Category Navigation Section -->
    <section class="flex flex-col gap-6">
      <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
          <div class="w-1.5 h-6 rounded-full bg-primary" />
          <h2 class="text-xl sm:text-2xl font-black text-neutral-900 dark:text-white">
            دسته‌بندی‌های تخصصی
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
          مشاهده نقشه کامل
        </UButton>
      </div>

      <CategoryNav :categories="catalogStore.categoryTree" />
    </section>

    <!-- Flash Deals Section with Countdown -->
    <HomeFlashDeals
      :deals="flashDeals"
      :loading="catalogStore.loading"
    />

    <!-- Best Sellers / Featured Products -->
    <HomeFeaturedProducts
      :products="featuredProducts"
      :loading="catalogStore.loading"
    />

    <!-- Latest Blog Articles Showcase -->
    <HomeBlogSection
      v-if="features.hasFeature('blog') && featuredArticles.length > 0"
      :articles="featuredArticles"
    />

    <!-- Brand Showcase Strip -->
    <HomeBrandsSection
      v-if="features.hasFeature('brands')"
      :brands="brands"
    />
  </div>
</template>
