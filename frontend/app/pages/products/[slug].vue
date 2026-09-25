<script setup lang="ts">
import ProductGallery from '~/components/product/ProductGallery.vue'
import ProductPurchaseBox from '~/components/product/ProductPurchaseBox.vue'
import ProductStickyBar from '~/components/product/ProductStickyBar.vue'
import VariantSelector from '~/components/product/VariantSelector.vue'
import ProductReviews from '~/components/review/ProductReviews.vue'
import type { ProductVariantItem } from '~/types/product'
import { useCatalogService } from '~/services/catalogService'
import { useWishlistStore } from '~/stores/wishlist'

const route = useRoute()
const catalogService = useCatalogService()
const wishlistStore = useWishlistStore()
const features = useFeatures()

const slug = computed(() => decodeURIComponent(String(route.params.slug || '')))
const selectedVariant = ref<ProductVariantItem | null>(null)

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
  title: computed(() => `${product.value?.meta_title || product.value?.name} - ایزیشاپ`),
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
        url: `https://easyshop.ir/products/${product.value.slug}`
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
})
</script>

<template>
  <div
    v-if="product"
    class="flex flex-col gap-8 py-6 pb-24 sm:pb-12"
  >
    <!-- Breadcrumb -->
    <nav class="flex items-center gap-2 text-xs text-neutral-400 overflow-x-auto [scrollbar-width:none] [&::-webkit-scrollbar]:hidden">
      <NuxtLink
        to="/"
        class="hover:text-primary transition-colors shrink-0"
      >صفحه اصلی</NuxtLink>
      <span>/</span>
      <NuxtLink
        to="/products"
        class="hover:text-primary transition-colors shrink-0"
      >محصولات</NuxtLink>
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
        <ProductPurchaseBox :selected-variant="selectedVariant" />
      </div>
    </div>

    <!-- Product Full Description -->
    <div
      v-if="product.description"
      class="mt-8 pt-8 border-t border-neutral-200 dark:border-neutral-800 flex flex-col gap-4"
    >
      <div class="flex items-center gap-2">
        <div class="w-1.5 h-6 rounded-full bg-primary" />
        <h2 class="text-xl font-black text-neutral-900 dark:text-white">
          توضیحات و نقد تخصصی
        </h2>
      </div>

      <div class="max-w-none text-neutral-700 dark:text-neutral-300 text-sm sm:text-base leading-loose">
        <div v-html="product.description" />
      </div>
    </div>

    <!-- Product Reviews & Ratings Section -->
    <div
      v-if="features.hasFeature('reviews')"
      class="mt-8 pt-8 border-t border-neutral-200 dark:border-neutral-800 flex flex-col gap-6"
    >
      <div class="flex items-center gap-2">
        <div class="w-1.5 h-6 rounded-full bg-primary" />
        <h2 class="text-xl font-black text-neutral-900 dark:text-white">
          نظرات و بررسی تخصصی خریداران
        </h2>
      </div>

      <ProductReviews
        :product-id="product.id"
        :product-name="product.name"
      />
    </div>

    <!-- Sticky Mobile Bottom Bar (< 640px) -->
    <ProductStickyBar :selected-variant="selectedVariant" />
  </div>
</template>
