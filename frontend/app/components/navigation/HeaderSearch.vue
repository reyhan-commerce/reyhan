<script setup lang="ts">
const router = useRouter()
const route = useRoute()
const catalogStore = useCatalogStore()
const api = useApi()
const { formatPrice } = usePersian()
const { recentSearches, addRecentSearch, removeRecentSearch, clearRecentSearches } = useRecentSearches()

const searchQuery = ref(String(route.query.search || ''))
const isFocused = ref(false)
const searchInputRef = ref<HTMLInputElement | null>(null)
const containerRef = ref<HTMLElement | null>(null)

// Live Suggestions State
const isLoading = ref(false)
const hasSearched = ref(false)
const suggestions = ref<{
  products: any[]
  categories: { id: number; name: string; slug: string }[]
  brands: { id: number; name: string; slug: string }[]
}>({
  products: [],
  categories: [],
  brands: []
})

let debounceTimer: ReturnType<typeof setTimeout> | null = null

// Dynamic categories from catalog store (100% real, no hardcoding)
const popularCategories = computed(() => {
  const list: { id: number; name: string; slug: string }[] = []
  const seen = new Set<string>()

  for (const cat of catalogStore.categoryTree) {
    if (!seen.has(cat.slug)) {
      seen.add(cat.slug)
      list.push({ id: cat.id, name: cat.name, slug: cat.slug })
    }
    if (cat.children) {
      for (const child of cat.children) {
        if (!seen.has(child.slug)) {
          seen.add(child.slug)
          list.push({ id: child.id, name: child.name, slug: child.slug })
        }
      }
    }
  }
  return list.slice(0, 8)
})

// Watch route changes to update input value
watch(() => route.query.search, (newVal) => {
  searchQuery.value = String(newVal || '')
})

// Debounced Live Suggestions Fetch
watch(searchQuery, (newVal) => {
  const trimmed = newVal.trim()
  if (debounceTimer) clearTimeout(debounceTimer)

  if (trimmed.length < 2) {
    suggestions.value = { products: [], categories: [], brands: [] }
    isLoading.value = false
    hasSearched.value = false
    return
  }

  isLoading.value = true
  debounceTimer = setTimeout(async () => {
    try {
      const res = await api<ApiResponse<{
        products: any[]
        categories: { id: number; name: string; slug: string }[]
        brands: { id: number; name: string; slug: string }[]
      }>>('/search/suggestions', {
        params: { q: trimmed }
      })

      if (res?.data) {
        suggestions.value = {
          products: res.data.products || [],
          categories: res.data.categories || [],
          brands: res.data.brands || []
        }
      }
    } catch {
      suggestions.value = { products: [], categories: [], brands: [] }
    } finally {
      isLoading.value = false
      hasSearched.value = true
    }
  }, 220)
})

const hasSuggestions = computed(() => {
  return (
    suggestions.value.products.length > 0 ||
    suggestions.value.categories.length > 0 ||
    suggestions.value.brands.length > 0
  )
})

function handleSearch() {
  const trimmed = searchQuery.value.trim()
  isFocused.value = false
  if (trimmed) {
    addRecentSearch(trimmed)
    catalogStore.setFilter('search', trimmed)
    router.push({ path: '/products', query: { search: trimmed } })
  } else {
    catalogStore.setFilter('search', '')
    router.push('/products')
  }
}

function handleSelectRecent(term: string) {
  searchQuery.value = term
  handleSearch()
}

function handleSelectCategory(cat: { name: string; slug: string }) {
  addRecentSearch(cat.name)
  isFocused.value = false
  router.push({ path: '/products', query: { category: cat.slug } })
}

function handleSelectBrand(brand: { name: string; slug: string }) {
  addRecentSearch(brand.name)
  isFocused.value = false
  router.push({ path: '/products', query: { brand: brand.slug } })
}

function handleSelectProduct(prod: any) {
  addRecentSearch(prod.name)
  isFocused.value = false
  router.push(`/products/${prod.slug}`)
}

function onBlur() {
  setTimeout(() => {
    isFocused.value = false
  }, 250)
}
</script>

<template>
  <div
    ref="containerRef"
    class="relative w-full max-w-md xl:max-w-lg"
  >
    <form
      class="relative flex items-center"
      @submit.prevent="handleSearch"
    >
      <div class="relative w-full">
        <input
          ref="searchInputRef"
          v-model="searchQuery"
          type="text"
          placeholder="جستجوی کالا، برند یا دسته‌بندی..."
          class="w-full h-11 pr-11 pl-10 rounded-2xl bg-neutral-100 dark:bg-neutral-800/80 border border-neutral-200/80 dark:border-neutral-700/60 text-sm text-neutral-800 dark:text-neutral-100 placeholder:text-neutral-400 focus:outline-none focus:ring-2 focus:ring-primary/40 focus:border-primary transition-all duration-200"
          @focus="isFocused = true"
          @blur="onBlur"
          @keydown.esc="isFocused = false"
        >

        <!-- Search Icon / Loading Spinner -->
        <div class="absolute right-3.5 top-1/2 -translate-y-1/2 text-neutral-400 pointer-events-none flex items-center">
          <UIcon
            v-if="isLoading"
            name="i-lucide-loader-2"
            class="size-5 animate-spin text-primary"
          />
          <UIcon
            v-else
            name="i-lucide-search"
            class="size-5"
          />
        </div>

        <!-- Clear Button -->
        <button
          v-if="searchQuery"
          type="button"
          class="absolute left-3 top-1/2 -translate-y-1/2 text-neutral-400 hover:text-neutral-600 dark:hover:text-neutral-200 p-1 rounded-full transition-colors cursor-pointer"
          title="پاک کردن متن"
          @click="searchQuery = ''; searchInputRef?.focus()"
        >
          <UIcon
            name="i-lucide-x"
            class="size-4"
          />
        </button>
      </div>
    </form>

    <!-- Search Dropdown Popover (ClientOnly & v-show to prevent initial focus blink/refresh) -->
    <ClientOnly>
      <Transition
        enter-active-class="transition duration-150 ease-out"
        enter-from-class="opacity-0 translate-y-1"
        enter-to-class="opacity-100 translate-y-0"
        leave-active-class="transition duration-100 ease-in"
        leave-from-class="opacity-100 translate-y-0"
        leave-to-class="opacity-0 translate-y-1"
      >
        <div
          v-show="isFocused"
          class="absolute top-full mt-2 inset-x-0 bg-white dark:bg-neutral-900 border border-neutral-200/80 dark:border-neutral-800 rounded-3xl shadow-2xl p-4 z-50 flex flex-col gap-4 max-h-[460px] overflow-y-auto no-scrollbar"
        >
          <!-- ============================================== -->
          <!-- STATE 1: RECENT SEARCHES & REAL CATEGORIES     -->
          <!-- ============================================== -->
          <template v-if="searchQuery.trim().length < 2">
            <!-- 1. Recent Searches -->
            <div
              v-if="recentSearches.length > 0"
              class="flex flex-col gap-2.5"
            >
              <div class="flex items-center justify-between text-xs font-bold text-neutral-500 dark:text-neutral-400">
                <div class="flex items-center gap-1.5">
                  <UIcon
                    name="i-lucide-history"
                    class="size-4 text-primary"
                  />
                  <span>جستجوهای اخیر شما:</span>
                </div>
                <button
                  type="button"
                  class="text-[11px] text-neutral-400 hover:text-red-500 transition-colors cursor-pointer"
                  @mousedown.prevent="clearRecentSearches"
                >
                  پاک کردن همه
                </button>
              </div>

              <div class="flex flex-wrap gap-2">
                <div
                  v-for="term in recentSearches"
                  :key="term"
                  class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-neutral-100 dark:bg-neutral-800 text-xs font-medium text-neutral-700 dark:text-neutral-300 hover:bg-primary-50 dark:hover:bg-primary-950/40 hover:text-primary transition-all group cursor-pointer border border-transparent hover:border-primary/20"
                  @mousedown.prevent="handleSelectRecent(term)"
                >
                  <UIcon
                    name="i-lucide-clock"
                    class="size-3 text-neutral-400 group-hover:text-primary"
                  />
                  <span>{{ term }}</span>
                  <button
                    type="button"
                    class="p-0.5 rounded-md hover:bg-neutral-200 dark:hover:bg-neutral-700 text-neutral-400 hover:text-red-500 transition-colors"
                    title="حذف"
                    @mousedown.stop.prevent="removeRecentSearch(term)"
                  >
                    <UIcon
                      name="i-lucide-x"
                      class="size-3"
                    />
                  </button>
                </div>
              </div>
            </div>

            <div
              v-if="recentSearches.length > 0 && popularCategories.length > 0"
              class="border-t border-neutral-100 dark:border-neutral-800/80"
            />

            <!-- 2. Real Dynamic Categories from Store -->
            <div
              v-if="popularCategories.length > 0"
              class="flex flex-col gap-2.5"
            >
              <div class="flex items-center gap-1.5 text-xs font-bold text-neutral-500 dark:text-neutral-400">
                <UIcon
                  name="i-lucide-layers"
                  class="size-4 text-primary"
                />
                <span>دسته‌بندی‌های پرطرفدار فروشگاه:</span>
              </div>

              <div class="flex flex-wrap gap-2">
                <button
                  v-for="cat in popularCategories"
                  :key="`pop-cat-${cat.id}`"
                  type="button"
                  class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-neutral-100 dark:bg-neutral-800/80 hover:bg-primary/10 hover:text-primary dark:hover:bg-primary/20 text-xs font-medium text-neutral-700 dark:text-neutral-300 transition-colors cursor-pointer"
                  @mousedown.prevent="handleSelectCategory(cat)"
                >
                  <UIcon
                    name="i-lucide-folder"
                    class="size-3 text-neutral-400"
                  />
                  <span>{{ cat.name }}</span>
                </button>
              </div>
            </div>
          </template>

          <!-- ============================================== -->
          <!-- STATE 2: LIVE SUGGESTIONS (TYPING >= 2 CHARS)  -->
          <!-- ============================================== -->
          <template v-else>
            <!-- Loading State -->
            <div
              v-if="isLoading && !hasSuggestions"
              class="flex flex-col gap-3 py-2"
            >
              <div class="flex items-center gap-2">
                <div class="h-4 w-28 bg-neutral-200 dark:bg-neutral-800 rounded animate-pulse" />
              </div>
              <div
                v-for="i in 3"
                :key="i"
                class="flex items-center gap-3 p-2 rounded-2xl bg-neutral-50 dark:bg-neutral-800/40"
              >
                <div class="size-11 rounded-xl bg-neutral-200 dark:bg-neutral-800 animate-pulse shrink-0" />
                <div class="flex-1 flex flex-col gap-1.5">
                  <div class="h-3.5 w-3/5 bg-neutral-200 dark:bg-neutral-800 rounded animate-pulse" />
                  <div class="h-3 w-1/4 bg-neutral-200 dark:bg-neutral-800 rounded animate-pulse" />
                </div>
              </div>
            </div>

            <!-- Matching Categories & Brands -->
            <div
              v-if="suggestions.categories.length > 0 || suggestions.brands.length > 0"
              class="flex flex-col gap-2"
            >
              <div class="text-[11px] font-bold text-neutral-400">
                دسته‌ها و برندهای مرتبط
              </div>
              <div class="flex flex-wrap gap-1.5">
                <button
                  v-for="cat in suggestions.categories"
                  :key="`cat-${cat.id}`"
                  type="button"
                  class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-primary-50 dark:bg-primary-950/40 text-primary text-xs font-semibold hover:bg-primary-100 dark:hover:bg-primary-900/50 transition-colors cursor-pointer"
                  @mousedown.prevent="handleSelectCategory(cat)"
                >
                  <UIcon
                    name="i-lucide-folder"
                    class="size-3.5"
                  />
                  <span>دسته: {{ cat.name }}</span>
                </button>

                <button
                  v-for="brand in suggestions.brands"
                  :key="`brand-${brand.id}`"
                  type="button"
                  class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-neutral-100 dark:bg-neutral-800 text-neutral-700 dark:text-neutral-300 text-xs font-semibold hover:bg-neutral-200 dark:hover:bg-neutral-700 transition-colors cursor-pointer"
                  @mousedown.prevent="handleSelectBrand(brand)"
                >
                  <UIcon
                    name="i-lucide-tag"
                    class="size-3.5 text-neutral-400"
                  />
                  <span>برند: {{ brand.name }}</span>
                </button>
              </div>
            </div>

            <!-- Matching Products -->
            <div
              v-if="suggestions.products.length > 0"
              class="flex flex-col gap-2"
            >
              <div class="text-[11px] font-bold text-neutral-400">
                کالاهای پیشنهادی
              </div>
              <div class="flex flex-col gap-1.5">
                <div
                  v-for="prod in suggestions.products"
                  :key="prod.id"
                  class="flex items-center justify-between p-2 rounded-2xl hover:bg-neutral-50 dark:hover:bg-neutral-800/60 transition-colors cursor-pointer group"
                  @mousedown.prevent="handleSelectProduct(prod)"
                >
                  <div class="flex items-center gap-3 min-w-0">
                    <div class="size-11 rounded-xl bg-neutral-100 dark:bg-neutral-800 border border-neutral-200/60 dark:border-neutral-700/60 flex items-center justify-center overflow-hidden shrink-0">
                      <img
                        v-if="prod.thumbnail"
                        :src="prod.thumbnail"
                        :alt="prod.name"
                        class="size-full object-cover"
                      >
                      <UIcon
                        v-else
                        name="i-lucide-package"
                        class="size-5 text-neutral-400"
                      />
                    </div>

                    <div class="flex flex-col min-w-0">
                      <span class="text-xs font-bold text-neutral-800 dark:text-neutral-100 truncate group-hover:text-primary transition-colors">
                        {{ prod.name }}
                      </span>
                      <span class="text-[11px] text-neutral-400 truncate">
                        {{ prod.brand?.name || prod.category?.name || 'کالای اصل' }}
                      </span>
                    </div>
                  </div>

                  <div class="text-xs font-bold text-neutral-900 dark:text-white shrink-0 ms-2">
                    {{ formatPrice(prod.primary_price) }}
                  </div>
                </div>
              </div>
            </div>

            <!-- No Results State -->
            <div
              v-if="!isLoading && hasSearched && !hasSuggestions"
              class="flex flex-col items-center justify-center py-6 text-center gap-2"
            >
              <div class="w-12 h-12 rounded-2xl bg-neutral-100 dark:bg-neutral-800 flex items-center justify-center text-neutral-400 mb-1">
                <UIcon
                  name="i-lucide-search-x"
                  class="size-6"
                />
              </div>
              <p class="text-xs font-bold text-neutral-700 dark:text-neutral-300">
                نتیجه‌ای برای «{{ searchQuery }}» یافت نشد
              </p>
              <p class="text-[11px] text-neutral-400">
                املای عبارت را بررسی کنید یا کلید اینتر را برای جستجو در کل فروشگاه بزنید.
              </p>
            </div>

            <!-- Footer Action: Search All -->
            <div class="border-t border-neutral-100 dark:border-neutral-800/80 pt-2 flex items-center justify-between">
              <button
                type="button"
                class="w-full flex items-center justify-between px-3 py-2 rounded-xl text-xs font-bold text-primary hover:bg-primary-50 dark:hover:bg-primary-950/40 transition-colors cursor-pointer"
                @mousedown.prevent="handleSearch"
              >
                <div class="flex items-center gap-2">
                  <UIcon
                    name="i-lucide-search"
                    class="size-4"
                  />
                  <span>مشاهده همه نتایج برای «{{ searchQuery }}»</span>
                </div>
                <UIcon
                  name="i-lucide-arrow-left"
                  class="size-4"
                />
              </button>
            </div>
          </template>
        </div>
      </Transition>
    </ClientOnly>
  </div>
</template>
