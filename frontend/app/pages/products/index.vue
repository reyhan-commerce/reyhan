<script setup lang="ts">
import FilterSidebar from '~/components/catalog/FilterSidebar.vue'
import SidebarBanners from '~/components/catalog/SidebarBanners.vue'
import ProductCard from '~/components/catalog/ProductCard.vue'
import ProductCardSkeleton from '~/components/skeletons/ProductCardSkeleton.vue'
import CompareFloatingBar from '~/components/catalog/CompareFloatingBar.vue'
import type { BannerItem } from '~/types/content'
import type { ApiResponse } from '~/types/api'

const route = useRoute()
const router = useRouter()
const catalogStore = useCatalogStore()
const api = useApi()
const { toPersianDigits } = usePersian()

const isFilterDrawerOpen = ref(false)

// 1. Initialize store filters from URL query parameters (on initial load & SSR)
catalogStore.applyFiltersFromQuery(route.query)
const searchInput = ref(catalogStore.filters.search || '')

// 2. Fetch Category Tree, Products, and Sidebar Banners in SSR / Initial load
const { data: sidebarBannersResponse } = await useAsyncData('catalog-sidebar-banners', () =>
  api<ApiResponse<BannerItem[]>>('/banners?position=sidebar').catch(() => null)
)
const sidebarBanners = computed(() => sidebarBannersResponse.value?.data ?? [])

await useAsyncData('products-catalog-page', async () => {
  await Promise.all([
    catalogStore.fetchCategoryTree(),
    catalogStore.fetchProducts()
  ])
  return true
})

// 3. Two-way sync: Watch store filters and sync changes to route.query
let isUpdatingRoute = false
function syncQueryFromStore() {
  if (isUpdatingRoute) return
  const q = catalogStore.filtersToQuery()
  const currentQuery = route.query

  const qKeys = Object.keys(q)
  const curKeys = Object.keys(currentQuery)
  let isDifferent = qKeys.length !== curKeys.length
  if (!isDifferent) {
    for (const k of qKeys) {
      if (String(q[k]) !== String(currentQuery[k])) {
        isDifferent = true
        break
      }
    }
  }

  if (isDifferent) {
    isUpdatingRoute = true
    router.replace({ query: q }).finally(() => {
      isUpdatingRoute = false
    })
  }
}

// Watch filters deeply to push changes to URL
watch(() => catalogStore.filters, () => {
  syncQueryFromStore()
}, { deep: true })

// 4. Two-way sync: When URL query changes (e.g. Browser Back/Forward or clicking navigation link)
watch(() => route.query, (newQuery) => {
  if (isUpdatingRoute) return
  const currentStoreQuery = catalogStore.filtersToQuery()
  const qKeys = Object.keys(currentStoreQuery)
  const newKeys = Object.keys(newQuery)
  let isDifferent = qKeys.length !== newKeys.length
  if (!isDifferent) {
    for (const k of newKeys) {
      if (String(newQuery[k]) !== String(currentStoreQuery[k])) {
        isDifferent = true
        break
      }
    }
  }

  if (isDifferent) {
    catalogStore.applyFiltersFromQuery(newQuery)
    searchInput.value = catalogStore.filters.search || ''
    catalogStore.fetchProducts()
  }
})

// Debounced search
let searchTimer: ReturnType<typeof setTimeout> | null = null
function onSearchInput(val: string) {
  if (searchTimer) clearTimeout(searchTimer)
  searchTimer = setTimeout(() => {
    catalogStore.setFilter('search', val)
  }, 400)
}

function onSortChange(val: string) {
  catalogStore.setFilter('sort', val)
}

function onResetFilters() {
  searchInput.value = ''
  catalogStore.resetFilters()
}

const sortOptions = [
  { label: 'جدیدترین', value: 'latest' },
  { label: 'ارزان‌ترین', value: 'cheapest' },
  { label: 'گران‌ترین', value: 'expensive' },
  { label: 'پیشنهادات شگفت‌انگیز', value: 'featured' },
  { label: 'پرفروش‌ترین‌ها', value: 'popular' }
]

useSeoMeta({
  title: 'کاتالوگ محصولات',
  description: 'خرید آنلاین انواع لوازم آرایشی، کرم ضدآفتاب، کرم‌پودر، رژلب و محصولات مراقبت از پوست با بهترین قیمت و ضمانت اصالت'
})
</script>

<template>
  <div class="flex flex-col gap-6 py-4">
    <!-- Breadcrumb & Title -->
    <div class="flex flex-col gap-2">
      <div class="flex items-center gap-2 text-xs text-neutral-400">
        <NuxtLink
          to="/"
          class="hover:text-primary transition-colors"
        >صفحه اصلی</NuxtLink>
        <span>/</span>
        <span class="text-neutral-800 dark:text-neutral-200 font-medium">کاتالوگ محصولات</span>
      </div>
      <h1 class="text-2xl sm:text-3xl font-black text-neutral-900 dark:text-white">
        کاتالوگ کلیه محصولات
      </h1>
      <p class="text-xs sm:text-sm text-neutral-500">
        جستجو، مقایسه و انتخاب از بین {{ toPersianDigits(catalogStore.totalProducts) }} محصول معتبر
      </p>
    </div>

    <!-- Search & Toolbar (Sticky Header) -->
    <div class="sticky top-[130px] z-30 p-4 rounded-2xl bg-white/95 dark:bg-neutral-900/95 backdrop-blur-md border border-neutral-200/80 dark:border-neutral-800 flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3 shadow-xs">
      <!-- Search Input -->
      <div class="w-full sm:max-w-md">
        <UInput
          v-model="searchInput"
          icon="i-lucide-search"
          placeholder="جستجوی محصول، برند یا دسته‌بندی..."
          size="md"
          class="w-full"
          @update:model-value="onSearchInput"
        />
      </div>

      <!-- Controls: Sort & Mobile Filter Button -->
      <div class="flex items-center gap-3">
        <!-- Sort Select -->
        <div class="flex items-center gap-2 text-sm text-neutral-500 shrink-0">
          <UIcon
            name="i-lucide-arrow-up-down"
            class="size-4 hidden sm:block"
          />
          <USelect
            :model-value="catalogStore.filters.sort"
            :items="sortOptions"
            size="md"
            class="w-36 sm:w-44"
            @update:model-value="onSortChange"
          />
        </div>

        <!-- Mobile Filter Drawer Trigger -->
        <div class="lg:hidden">
          <UButton
            color="neutral"
            variant="outline"
            icon="i-lucide-filter"
            size="md"
            class="min-h-10 px-3"
            @click="isFilterDrawerOpen = true"
          >
            فیلترها
          </UButton>
        </div>

        <!-- Refresh Action Button -->
        <UButton
          color="neutral"
          variant="subtle"
          icon="i-lucide-rotate-cw"
          size="md"
          :loading="catalogStore.loading"
          class="shrink-0"
          title="بروزرسانی کاتالوگ"
          @click="catalogStore.fetchProducts()"
        >
          <span class="hidden sm:inline">بروزرسانی</span>
        </UButton>
      </div>
    </div>

    <!-- Layout: Filters + Products Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
      <!-- Mobile Filter Slideover Drawer -->
      <USlideover
        v-model:open="isFilterDrawerOpen"
        title="فیلترهای جستجو"
        description="انتخاب برند، دسته‌بندی و محدوده قیمت"
        class="lg:hidden"
      >
        <template #body>
          <div class="p-4 flex flex-col gap-6">
            <FilterSidebar @applied="isFilterDrawerOpen = false" />
            <SidebarBanners
              v-if="sidebarBanners.length > 0"
              :banners="sidebarBanners"
            />
          </div>
        </template>
      </USlideover>

      <!-- Desktop Sidebar (Sticky Container) -->
      <aside class="hidden lg:block lg:col-span-1">
        <div class="sticky top-[216px] flex flex-col gap-5">
          <div class="p-5 rounded-3xl bg-white dark:bg-neutral-900 border border-neutral-200/80 dark:border-neutral-800 shadow-xs">
            <FilterSidebar />
          </div>
          <SidebarBanners
            v-if="sidebarBanners.length > 0"
            :banners="sidebarBanners"
          />
        </div>
      </aside>

      <!-- Main Products View -->
      <main class="lg:col-span-3 flex flex-col gap-6">
        <!-- Loading State: Universal Skeleton -->
        <div
          v-if="catalogStore.loading"
          class="grid grid-cols-2 sm:grid-cols-3 gap-4 min-h-80"
        >
          <ProductCardSkeleton
            v-for="i in 6"
            :key="i"
          />
        </div>

        <!-- Products List -->
        <div
          v-else-if="catalogStore.products.length > 0"
          class="grid grid-cols-2 sm:grid-cols-3 gap-4"
        >
          <ProductCard
            v-for="product in catalogStore.products"
            :key="product.id"
            :product="product"
          />
        </div>

        <!-- Empty State -->
        <div
          v-else
          class="flex flex-col items-center justify-center p-12 text-center rounded-3xl bg-neutral-50 dark:bg-neutral-900 border border-neutral-200/60 dark:border-neutral-800 min-h-64"
        >
          <UIcon
            name="i-lucide-search-x"
            class="size-14 text-neutral-400 mb-3"
          />
          <h3 class="font-bold text-base text-neutral-800 dark:text-neutral-200">
            هیچ کالایی با مشخصات انتخابی یافت نشد
          </h3>
          <p class="text-xs text-neutral-400 mt-1 mb-4">
            لطفاً عبارت جستجو یا فیلترهای انتخابی را تغییر دهید.
          </p>
          <UButton
            color="primary"
            variant="solid"
            size="sm"
            @click="onResetFilters"
          >
            پاک کردن همه فیلترها
          </UButton>
        </div>

        <!-- Pagination -->
        <div
          v-if="catalogStore.lastPage > 1"
          class="flex justify-center pt-6 border-t border-neutral-200/60 dark:border-neutral-800"
        >
          <UPagination
            :model-value="catalogStore.currentPage"
            :total="catalogStore.totalProducts"
            :page-count="catalogStore.perPage"
            @update:model-value="(page: number) => catalogStore.setFilter('page', page)"
          />
        </div>
      </main>
    </div>

    <!-- Compare Floating Bar -->
    <CompareFloatingBar />
  </div>
</template>
