<script setup lang="ts">
import FilterSidebar from '~/components/catalog/FilterSidebar.vue'
import ProductCard from '~/components/catalog/ProductCard.vue'

const route = useRoute()
const catalogStore = useCatalogStore()
const { toPersianDigits } = usePersian()

const isFilterDrawerOpen = ref(false)
const searchInput = ref(String(route.query.search || ''))

// Initialize filters from URL query parameters if present
if (route.query.category) catalogStore.filters.category = String(route.query.category)
if (route.query.brand) catalogStore.filters.brand = [String(route.query.brand)]
if (route.query.search) catalogStore.filters.search = String(route.query.search)

await useAsyncData('products-catalog-page', async () => {
  await Promise.all([
    catalogStore.fetchCategoryTree(),
    catalogStore.fetchProducts()
  ])
  return true
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

const sortOptions = [
  { label: 'جدیدترین', value: 'latest' },
  { label: 'ارزان‌ترین', value: 'cheapest' },
  { label: 'گران‌ترین', value: 'expensive' },
  { label: 'محصولات برگزیده', value: 'featured' }
]

useSeoMeta({
  title: 'کاتالوگ محصولات آرایشی و بهداشتی - ایزیشاپ',
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

    <!-- Search & Toolbar -->
    <div class="p-4 rounded-2xl bg-white dark:bg-neutral-900 border border-neutral-200/80 dark:border-neutral-800 flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3 shadow-xs">
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
      </div>
    </div>

    <!-- Layout Grid: Filter Sidebar + Products Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6 pt-2">
      <!-- Desktop Sidebar -->
      <aside class="hidden lg:block lg:col-span-1">
        <div class="sticky top-20 p-5 rounded-3xl bg-white dark:bg-neutral-900 border border-neutral-200/80 dark:border-neutral-800 shadow-xs">
          <FilterSidebar />
        </div>
      </aside>

      <!-- Main Products View -->
      <main class="lg:col-span-3 flex flex-col gap-6">
        <!-- Loading State -->
        <div
          v-if="catalogStore.loading"
          class="grid grid-cols-2 sm:grid-cols-3 gap-4 min-h-80"
        >
          <div
            v-for="i in 6"
            :key="i"
            class="h-72 rounded-2xl bg-neutral-100 dark:bg-neutral-800/50 animate-pulse"
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
            @click="catalogStore.resetFilters"
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

    <!-- Mobile Filter Drawer -->
    <USlideover
      v-model:open="isFilterDrawerOpen"
      title="فیلترهای کاتالوگ"
      side="right"
    >
      <template #body>
        <div class="p-4">
          <FilterSidebar />
        </div>
      </template>
    </USlideover>
  </div>
</template>
