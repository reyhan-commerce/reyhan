<script setup lang="ts">
import FilterSidebar from '~/components/catalog/FilterSidebar.vue'
import ProductCard from '~/components/catalog/ProductCard.vue'
import CompareFloatingBar from '~/components/catalog/CompareFloatingBar.vue'
import type { CategoryTreeItem } from '~/stores/catalog'

const route = useRoute()
const api = useApi()
const catalogStore = useCatalogStore()

const slug = computed(() => decodeURIComponent(String(route.params.slug || '')))
const isFilterDrawerOpen = ref(false)

// Initialize category-scoped filters
catalogStore.applyFiltersFromQuery({ ...route.query, category: slug.value })

// Fetch Category Detail & Products
const { data: categoryData } = await useAsyncData(
  () => `category-${slug.value}`,
  async () => {
    catalogStore.applyFiltersFromQuery({ ...route.query, category: slug.value })
    const [catRes] = await Promise.all([
      api<{ success: boolean, data: CategoryTreeItem & { parent?: { name: string, slug: string }, attributes?: unknown[] } }>(`/categories/${encodeURIComponent(slug.value)}`),
      catalogStore.fetchCategoryTree(),
      catalogStore.fetchProducts({ category: slug.value, ...route.query })
    ])
    return catRes.data
  },
  { watch: [slug, () => route.query] }
)

const currentCategory = computed(() => categoryData.value)

useSeoMeta({
  title: computed(() => currentCategory.value ? currentCategory.value.name : 'دسته‌بندی کالا'),
  description: computed(() => currentCategory.value?.name ? `خرید آنلاین انواع محصولات ${currentCategory.value.name} با ضمانت اصالت کالا و ارسال سریع` : '')
})
</script>

<template>
  <div class="flex flex-col gap-6 py-4">
    <!-- Breadcrumb Header -->
    <div class="flex flex-col gap-3">
      <div class="flex items-center gap-2 text-xs text-neutral-400">
        <NuxtLink
          to="/"
          class="hover:text-primary transition-colors"
        >صفحه اصلی</NuxtLink>
        <span>/</span>
        <NuxtLink
          to="/categories"
          class="hover:text-primary transition-colors"
        >دسته‌بندی‌ها</NuxtLink>
        <template v-if="currentCategory?.parent">
          <span>/</span>
          <NuxtLink
            :to="`/categories/${currentCategory.parent.slug}`"
            class="hover:text-primary transition-colors"
          >
            {{ currentCategory.parent.name }}
          </NuxtLink>
        </template>
        <span>/</span>
        <span class="text-neutral-800 dark:text-neutral-200 font-medium">
          {{ currentCategory?.name || slug }}
        </span>
      </div>

      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <h1 class="text-2xl sm:text-3xl font-black text-neutral-900 dark:text-white">
            {{ currentCategory?.name }}
          </h1>
          <p class="text-xs sm:text-sm text-neutral-500 mt-1">
            مشاهده و خرید جدیدترین محصولات دسته‌بندی {{ currentCategory?.name }}
          </p>
        </div>

        <!-- Mobile Filter Button -->
        <div class="lg:hidden">
          <UButton
            color="neutral"
            variant="outline"
            icon="i-lucide-filter"
            size="md"
            class="min-h-11 px-4"
            @click="isFilterDrawerOpen = true"
          >
            فیلترها
          </UButton>
        </div>
      </div>
    </div>

    <!-- Subcategories Scroll Pills -->
    <div
      v-if="currentCategory?.children && currentCategory.children.length > 0"
      class="flex items-center gap-2 overflow-x-auto pb-2 scrollbar-none"
    >
      <NuxtLink
        v-for="sub in currentCategory.children"
        :key="sub.id"
        :to="`/categories/${sub.slug}`"
        class="shrink-0 px-4 py-2 rounded-full text-xs font-bold bg-neutral-100 dark:bg-neutral-800 hover:bg-primary/15 hover:text-primary text-neutral-700 dark:text-neutral-300 transition-colors min-h-9 flex items-center"
      >
        {{ sub.name }}
      </NuxtLink>
    </div>

    <!-- Main Layout: Sidebar & Product Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6 pt-2">
      <!-- Desktop Filter Sidebar -->
      <aside class="hidden lg:block lg:col-span-1">
        <div class="sticky top-20 p-5 rounded-3xl bg-white dark:bg-neutral-900 border border-neutral-200/80 dark:border-neutral-800 shadow-xs">
          <FilterSidebar />
        </div>
      </aside>

      <!-- Products Grid & Toolbar -->
      <main class="lg:col-span-3 flex flex-col gap-6">
        <!-- Products Grid -->
        <div
          v-if="catalogStore.loading"
          class="grid grid-cols-2 sm:grid-cols-3 gap-4 min-h-64"
        >
          <div
            v-for="i in 6"
            :key="i"
            class="h-72 rounded-2xl bg-neutral-100 dark:bg-neutral-800/50 animate-pulse"
          />
        </div>

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
            name="i-lucide-package-open"
            class="size-12 text-neutral-400 mb-3"
          />
          <h3 class="font-bold text-base text-neutral-800 dark:text-neutral-200">
            محصولی در این دسته‌بندی یافت نشد
          </h3>
          <p class="text-xs text-neutral-400 mt-1 mb-4">
            می‌توانید فیلترها را تغییر دهید یا تمام محصولات را مشاهده نمایید.
          </p>
          <UButton
            to="/products"
            color="primary"
            variant="solid"
            size="sm"
          >
            مشاهده تمام محصولات
          </UButton>
        </div>
      </main>
    </div>

    <!-- Mobile Slideover Filter Drawer -->
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

    <!-- Compare Floating Bar -->
    <CompareFloatingBar />
  </div>
</template>
