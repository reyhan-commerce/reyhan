<script setup lang="ts">
import ProductGallery from '~/components/product/ProductGallery.vue'
import ProductPurchaseBox from '~/components/product/ProductPurchaseBox.vue'
import ProductStickyBar from '~/components/product/ProductStickyBar.vue'
import VariantSelector from '~/components/product/VariantSelector.vue'
import ProductReviews from '~/components/review/ProductReviews.vue'
import ProductSpecsTable from '~/components/product/ProductSpecsTable.vue'
import RelatedProducts from '~/components/product/RelatedProducts.vue'
import CompareFloatingBar from '~/components/catalog/CompareFloatingBar.vue'
import type { ProductVariantItem } from '~/types/product'
import { useCatalogService } from '~/services/catalogService'
import { useWishlistStore } from '~/stores/wishlist'

const route = useRoute()
const catalogService = useCatalogService()
const wishlistStore = useWishlistStore()
const features = useFeatures()

const slug = computed(() => decodeURIComponent(String(route.params.slug || '')))
const selectedVariant = ref<ProductVariantItem | null>(null)
const activeTab = ref<'description' | 'specs' | 'reviews' | 'questions'>('specs')
const isPriceHistoryOpen = ref(false)

// Fetch product details via centralized catalog service
const { data: product, error } = await useAsyncData(`product-${slug.value}`, () => {
  return catalogService.getProductBySlug(slug.value)
})

if (error.value || !product.value) {
  throw createError({
    statusCode: 404,
    message: 'محصول مورد نظر یافت نشد یا غیرفعال شده است.'
  })
}

// SEO Meta
useSeoMeta({
  title: computed(() => product.value?.meta_title || product.value?.name),
  description: computed(() => product.value?.meta_description || product.value?.short_description || ''),
  ogTitle: computed(() => product.value?.name),
  ogDescription: computed(() => product.value?.short_description || '')
})

// Google Rich Snippets (Schema.org / JSON-LD)
useSchemaOrg([
  defineProduct({
    name: product.value.name,
    description: product.value.short_description || product.value.name,
    image: product.value.gallery?.map(g => g.url) || [],
    sku: product.value.variants?.[0]?.sku || '',
    offers: [
      defineOffer({
        price: product.value.price_range?.min ? Math.floor(product.value.price_range.min / 10) : 0,
        priceCurrency: 'IRT',
        availability: product.value.variants?.some(v => v.stock > 0)
          ? 'https://schema.org/InStock'
          : 'https://schema.org/OutOfStock',
        url: `/products/${product.value.slug}`
      })
    ]
  }),
  defineBreadcrumb({
    itemListElement: [
      { name: 'صفحه اصلی', item: '/' },
      ...(product.value.category ? [{ name: product.value.category.name, item: `/categories/${product.value.category.slug}` }] : []),
      { name: product.value.name }
    ]
  })
])

onMounted(() => {
  wishlistStore.fetchWishlistIds()
  // If product has description, default to description, else specs
  if (product.value?.description) {
    activeTab.value = 'description'
  }
})
</script>

<template>
  <div
    v-if="product"
    class="flex flex-col gap-10 py-6 pb-24 sm:pb-16"
  >
    <!-- Breadcrumb -->
    <nav class="flex items-center gap-2 text-xs text-neutral-400 overflow-x-auto [scrollbar-width:none] [&::-webkit-scrollbar]:hidden">
      <NuxtLink
        to="/"
        class="hover:text-primary transition-colors shrink-0"
      >
        صفحه اصلی
      </NuxtLink>
      <span>/</span>
      <NuxtLink
        to="/products"
        class="hover:text-primary transition-colors shrink-0"
      >
        محصولات
      </NuxtLink>
      <template
        v-for="crumb in product.breadcrumbs"
        :key="crumb.id"
      >
        <span>/</span>
        <NuxtLink
          :to="`/categories/${crumb.slug}`"
          class="hover:text-primary transition-colors shrink-0"
        >
          {{ crumb.name }}
        </NuxtLink>
      </template>
      <span>/</span>
      <span class="text-neutral-700 dark:text-neutral-300 font-medium truncate max-w-xs">
        {{ product.name }}
      </span>
    </nav>

    <!-- Main PDP Container: Gallery + Product Info -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12">
      <!-- Media Gallery Component (5 cols) -->
      <div class="lg:col-span-5">
        <ProductGallery
          :product-id="product.id"
          :product-name="product.name"
          :is-featured="product.is_featured"
          :gallery="product.gallery"
        />
      </div>

      <!-- Product Information & Variant Selector (7 cols) -->
      <div class="lg:col-span-7 flex flex-col gap-5">
        <!-- Brand & Title -->
        <div class="flex flex-col gap-2">
          <div class="flex items-center justify-between">
            <div
              v-if="product.brand"
              class="flex items-center gap-2"
            >
              <NuxtLink
                :to="`/products?brand=${product.brand.slug}`"
                class="text-xs font-bold text-primary hover:underline"
              >
                برند: {{ product.brand.name }}
                <span
                  v-if="product.brand.name_en"
                  class="font-mono text-neutral-400"
                >({{ product.brand.name_en }})</span>
              </NuxtLink>
            </div>
            <div v-else />

            <button
              type="button"
              class="inline-flex items-center gap-1.5 text-xs text-neutral-500 hover:text-primary transition-colors cursor-pointer font-bold px-2.5 py-1 rounded-xl hover:bg-neutral-100 dark:hover:bg-neutral-800"
              @click="isPriceHistoryOpen = true"
            >
              <UIcon name="i-lucide-trending-up" class="size-4 text-primary" />
              <span>نمودار تغییرات قیمت</span>
            </button>
          </div>

          <h1 class="text-xl sm:text-2xl lg:text-3xl font-black text-neutral-900 dark:text-white leading-snug">
            {{ product.name }}
          </h1>

          <p
            v-if="product.short_description"
            class="text-sm text-neutral-500 leading-relaxed pt-1"
          >
            {{ product.short_description }}
          </p>
        </div>

        <USeparator />

        <!-- Scoped Variant Matrix Engine -->
        <VariantSelector
          v-model="selectedVariant"
          :product="product"
        />

        <!-- Purchase Action Box (Desktop & Tablet) -->
        <ProductPurchaseBox
          :selected-variant="selectedVariant"
          :product-name="product.name"
          :product-slug="product.slug"
        />
      </div>
    </div>

    <!-- Product Tabs: Review / Specs / Comments -->
    <div class="mt-4 pt-6 border-t border-neutral-200 dark:border-neutral-800 flex flex-col gap-6">
      <!-- Tab Header Buttons -->
      <div class="flex items-center gap-2 border-b border-neutral-200 dark:border-neutral-800 pb-px overflow-x-auto">
        <button
          v-if="product.description"
          type="button"
          class="flex items-center gap-2 px-5 py-3 text-sm font-bold border-b-2 transition-all cursor-pointer whitespace-nowrap"
          :class="[
            activeTab === 'description'
              ? 'border-primary text-primary bg-primary/5 rounded-t-xl'
              : 'border-transparent text-neutral-500 hover:text-neutral-800 dark:hover:text-neutral-200'
          ]"
          @click="activeTab = 'description'"
        >
          <UIcon
            name="i-lucide-file-text"
            class="size-4"
          />
          <span>بررسی تخصصی کالا</span>
        </button>

        <button
          type="button"
          class="flex items-center gap-2 px-5 py-3 text-sm font-bold border-b-2 transition-all cursor-pointer whitespace-nowrap"
          :class="[
            activeTab === 'specs'
              ? 'border-primary text-primary bg-primary/5 rounded-t-xl'
              : 'border-transparent text-neutral-500 hover:text-neutral-800 dark:hover:text-neutral-200'
          ]"
          @click="activeTab = 'specs'"
        >
          <UIcon
            name="i-lucide-list"
            class="size-4"
          />
          <span>مشخصات فنی</span>
          <span
            v-if="product.specifications"
            class="text-[11px] px-1.5 py-0.5 rounded-full bg-neutral-200 dark:bg-neutral-800 text-neutral-600 dark:text-neutral-300 font-mono"
          >
            {{ product.specifications.reduce((acc, g) => acc + g.items.length, 0) }}
          </span>
        </button>

        <button
          v-if="features.hasFeature('reviews')"
          type="button"
          class="flex items-center gap-2 px-5 py-3 text-sm font-bold border-b-2 transition-all cursor-pointer whitespace-nowrap"
          :class="[
            activeTab === 'reviews'
              ? 'border-primary text-primary bg-primary/5 rounded-t-xl'
              : 'border-transparent text-neutral-500 hover:text-neutral-800 dark:hover:text-neutral-200'
          ]"
          @click="activeTab = 'reviews'"
        >
          <UIcon
            name="i-lucide-message-square"
            class="size-4"
          />
          <span>نظرات و بررسی خریداران</span>
        </button>

        <button
          type="button"
          class="flex items-center gap-2 px-5 py-3 text-sm font-bold border-b-2 transition-all cursor-pointer whitespace-nowrap"
          :class="[
            activeTab === 'questions'
              ? 'border-primary text-primary bg-primary/5 rounded-t-xl'
              : 'border-transparent text-neutral-500 hover:text-neutral-800 dark:hover:text-neutral-200'
          ]"
          @click="activeTab = 'questions'"
        >
          <UIcon
            name="i-lucide-help-circle"
            class="size-4"
          />
          <span>پرسش و پاسخ</span>
        </button>
      </div>

      <!-- Tab Content 1: Description -->
      <div
        v-if="activeTab === 'description' && product.description"
        class="max-w-none text-neutral-700 dark:text-neutral-300 text-sm sm:text-base leading-loose py-2"
      >
        <div v-html="product.description" />
      </div>

      <!-- Tab Content 2: Technical Specifications -->
      <div
        v-if="activeTab === 'specs'"
        class="py-2"
      >
        <ProductSpecsTable :groups="product.specifications" />
      </div>

      <!-- Tab Content 3: Customer Reviews -->
      <div
        v-if="activeTab === 'reviews' && features.hasFeature('reviews')"
        class="py-2"
      >
        <ProductReviews
          :product-id="product.id"
          :product-name="product.name"
        />
      </div>

      <!-- Tab Content 4: Questions & Answers -->
      <div
        v-if="activeTab === 'questions'"
        class="py-2"
      >
        <ProductQuestionsSection :product-slug="product.slug" />
      </div>
    </div>

    <!-- Related Products Showcase -->
    <div class="mt-8 pt-8 border-t border-neutral-200 dark:border-neutral-800">
      <RelatedProducts :product-slug="product.slug" />
    </div>

    <!-- Sticky Mobile Bottom Bar (< 640px) -->
    <ProductStickyBar :selected-variant="selectedVariant" />

    <!-- Compare Floating Dock -->
    <CompareFloatingBar />

    <!-- Price History Modal -->
    <ProductPriceHistoryModal
      v-model:open="isPriceHistoryOpen"
      :product-slug="product.slug"
      :product-name="product.name"
    />
  </div>
</template>
