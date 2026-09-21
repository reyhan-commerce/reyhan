<script setup lang="ts">
import VariantSelector from '~/components/product/VariantSelector.vue'
import type { ProductDetailItem, ProductVariantItem } from '~/stores/catalog'

const route = useRoute()
const api = useApi()
const { formatPrice } = usePersian()

const slug = computed(() => decodeURIComponent(String(route.params.slug || '')))
const selectedVariant = ref<ProductVariantItem | null>(null)
const quantity = ref<number>(1)
const activeImageIndex = ref<number>(0)

// Fetch product details
const { data: productData, error } = await useAsyncData(`product-${slug.value}`, async () => {
  const res = await api<{ success: boolean, data: ProductDetailItem }>(`/products/${encodeURIComponent(slug.value)}`)
  return res.data
})

if (error.value || !productData.value) {
  throw createError({
    statusCode: 404,
    message: 'محصول مورد نظر یافت نشد یا غیرفعال شده است.'
  })
}

const product = computed(() => productData.value!)

// SEO Meta
useSeoMeta({
  title: computed(() => `${product.value.meta_title || product.value.name} - ایزیشاپ`),
  description: computed(() => product.value.meta_description || product.value.short_description || ''),
  ogTitle: computed(() => product.value.name),
  ogDescription: computed(() => product.value.short_description || '')
})

// Quantity controls
function incrementQty() {
  if (selectedVariant.value && quantity.value < selectedVariant.value.stock) {
    quantity.value++
  }
}

function decrementQty() {
  if (quantity.value > 1) {
    quantity.value--
  }
}

const cartStore = useCartStore()

// Add to Cart Action
async function addToCart() {
  if (!selectedVariant.value || selectedVariant.value.stock === 0) return

  await cartStore.addItem(selectedVariant.value.id, quantity.value)
}

// Active displayed image
const activeImage = computed(() => {
  if (product.value.gallery && product.value.gallery.length > 0) {
    return product.value.gallery[activeImageIndex.value]?.url || product.value.gallery[0]?.url
  }
  return null
})
</script>

<template>
  <div class="flex flex-col gap-8 py-6 pb-24 sm:pb-12">
    <!-- Breadcrumb -->
    <nav class="flex items-center gap-2 text-xs text-neutral-400 overflow-x-auto scrollbar-none">
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
      <!-- Media Gallery (5 cols) -->
      <div class="lg:col-span-5 flex flex-col gap-4">
        <!-- Main Large Image Container -->
        <div class="relative aspect-square rounded-3xl bg-neutral-100 dark:bg-neutral-900 border border-neutral-200/80 dark:border-neutral-800 flex items-center justify-center overflow-hidden shadow-sm group">
          <img
            v-if="activeImage"
            :src="activeImage"
            :alt="product.name"
            class="h-full w-full object-cover object-center transition-transform duration-500 group-hover:scale-104"
          >
          <div
            v-else
            class="size-28 rounded-3xl bg-primary/10 text-primary flex items-center justify-center text-4xl font-black"
          >
            {{ product.name.charAt(0) }}
          </div>

          <!-- Featured Badge -->
          <div
            v-if="product.is_featured"
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

          <!-- Full View Button -->
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
          v-if="product.gallery && product.gallery.length > 1"
          class="flex items-center gap-3 overflow-x-auto pb-1"
        >
          <button
            v-for="(img, idx) in product.gallery"
            :key="img.id"
            type="button"
            class="size-18 rounded-2xl border-2 overflow-hidden transition-all shrink-0 bg-neutral-100 dark:bg-neutral-800"
            :class="activeImageIndex === idx ? 'border-primary ring-2 ring-primary/20 shadow-sm' : 'border-transparent opacity-65 hover:opacity-100'"
            @click="activeImageIndex = idx"
          >
            <img
              :src="img.url"
              :alt="img.name"
              class="h-full w-full object-cover"
            >
          </button>
        </div>
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
        <div
          v-if="selectedVariant && selectedVariant.stock > 0"
          class="hidden sm:flex items-center gap-4 pt-2"
        >
          <!-- Quantity Stepper -->
          <div class="flex items-center border border-neutral-200 dark:border-neutral-700 rounded-2xl p-1 bg-white dark:bg-neutral-800">
            <UButton
              color="neutral"
              variant="ghost"
              icon="i-lucide-plus"
              size="sm"
              class="size-10 rounded-xl"
              :disabled="quantity >= selectedVariant.stock"
              @click="incrementQty"
            />
            <span class="w-10 text-center font-bold text-base select-none">
              {{ quantity }}
            </span>
            <UButton
              color="neutral"
              variant="ghost"
              icon="i-lucide-minus"
              size="sm"
              class="size-10 rounded-xl"
              :disabled="quantity <= 1"
              @click="decrementQty"
            />
          </div>

          <!-- Add To Cart Primary Button -->
          <UButton
            color="primary"
            variant="solid"
            size="xl"
            icon="i-lucide-shopping-cart"
            class="flex-1 min-h-12 text-base font-bold rounded-2xl justify-center"
            :loading="cartStore.isLoading"
            @click="addToCart"
          >
            افزودن به سبد خرید • {{ formatPrice(selectedVariant.price * quantity) }}
          </UButton>
        </div>
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

    <!-- Sticky Mobile Bottom Bar (< 640px) -->
    <div
      v-if="selectedVariant"
      class="sm:hidden fixed inset-x-0 bottom-0 z-40 bg-white/95 dark:bg-neutral-900/95 backdrop-blur-md border-t border-neutral-200 dark:border-neutral-800 p-3.5 flex items-center justify-between gap-3 shadow-lg"
    >
      <div class="flex flex-col">
        <span class="text-[11px] text-neutral-400">قیمت نهایی:</span>
        <span class="text-base font-black text-neutral-900 dark:text-white">
          {{ formatPrice(selectedVariant.price) }}
        </span>
      </div>

      <div class="flex items-center gap-2">
        <UButton
          v-if="selectedVariant.stock > 0"
          color="primary"
          variant="solid"
          size="lg"
          icon="i-lucide-shopping-cart"
          class="min-h-11 px-5 font-bold rounded-xl"
          @click="addToCart"
        >
          افزودن به سبد
        </UButton>
        <UBadge
          v-else
          color="neutral"
          variant="solid"
          size="md"
        >
          ناموجود
        </UBadge>
      </div>
    </div>
  </div>
</template>
