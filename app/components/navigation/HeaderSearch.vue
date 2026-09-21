<script setup lang="ts">
const router = useRouter()
const route = useRoute()
const catalogStore = useCatalogStore()

const searchQuery = ref(String(route.query.search || ''))
const isFocused = ref(false)
const searchInputRef = ref<HTMLInputElement | null>(null)

// Watch route changes to update input value
watch(() => route.query.search, (newVal) => {
  searchQuery.value = String(newVal || '')
})

function handleSearch() {
  const trimmed = searchQuery.value.trim()
  isFocused.value = false
  if (trimmed) {
    catalogStore.setFilter('search', trimmed)
    router.push({ path: '/products', query: { search: trimmed } })
  } else {
    catalogStore.setFilter('search', '')
    router.push('/products')
  }
}

function handleSelectSuggestion(term: string) {
  searchQuery.value = term
  handleSearch()
}

function onBlur() {
  setTimeout(() => {
    isFocused.value = false
  }, 200)
}

// Popular quick search tags
const popularTags = [
  'ضدآفتاب',
  'سرم ویتامین C',
  'کرم آبرسان',
  'ریمل اسنس',
  'تینت لب',
  'میسلار واتر'
]
</script>

<template>
  <div class="relative w-full max-w-md xl:max-w-lg">
    <form
      class="relative flex items-center"
      @submit.prevent="handleSearch"
    >
      <div class="relative w-full">
        <input
          ref="searchInputRef"
          v-model="searchQuery"
          type="text"
          placeholder="جستجوی محصول، برند یا دسته‌بندی..."
          class="w-full h-11 pr-11 pl-10 rounded-2xl bg-neutral-100 dark:bg-neutral-800/80 border border-neutral-200/80 dark:border-neutral-700/60 text-sm text-neutral-800 dark:text-neutral-100 placeholder:text-neutral-400 focus:outline-none focus:ring-2 focus:ring-primary/40 focus:border-primary transition-all duration-200"
          @focus="isFocused = true"
          @blur="onBlur"
        >
        <!-- Search Icon -->
        <div class="absolute right-3.5 top-1/2 -translate-y-1/2 text-neutral-400 pointer-events-none flex items-center">
          <UIcon
            name="i-lucide-search"
            class="size-5"
          />
        </div>

        <!-- Clear Button -->
        <button
          v-if="searchQuery"
          type="button"
          class="absolute left-3 top-1/2 -translate-y-1/2 text-neutral-400 hover:text-neutral-600 dark:hover:text-neutral-200 p-1 rounded-full transition-colors"
          @click="searchQuery = ''; handleSearch()"
        >
          <UIcon
            name="i-lucide-x"
            class="size-4"
          />
        </button>
      </div>
    </form>

    <!-- Quick Suggestions Dropdown Popover -->
    <Transition
      enter-active-class="transition duration-150 ease-out"
      enter-from-class="opacity-0 translate-y-1"
      enter-to-class="opacity-100 translate-y-0"
      leave-active-class="transition duration-100 ease-in"
      leave-from-class="opacity-100 translate-y-0"
      leave-to-class="opacity-0 translate-y-1"
    >
      <div
        v-if="isFocused"
        class="absolute top-full mt-2 inset-x-0 bg-white dark:bg-neutral-900 border border-neutral-200/80 dark:border-neutral-800 rounded-2xl shadow-xl p-4 z-50 flex flex-col gap-3"
      >
        <div class="flex items-center gap-2 text-xs font-bold text-neutral-500">
          <UIcon
            name="i-lucide-trending-up"
            class="size-4 text-primary"
          />
          <span>بیشترین جستجوهای اخیر:</span>
        </div>

        <div class="flex flex-wrap gap-2">
          <button
            v-for="tag in popularTags"
            :key="tag"
            type="button"
            class="px-3 py-1.5 rounded-xl bg-neutral-100 dark:bg-neutral-800 hover:bg-primary/10 hover:text-primary dark:hover:bg-primary/20 text-xs font-medium text-neutral-700 dark:text-neutral-300 transition-colors"
            @mousedown.prevent="handleSelectSuggestion(tag)"
          >
            {{ tag }}
          </button>
        </div>
      </div>
    </Transition>
  </div>
</template>
