<script setup lang="ts">
import type { BlogCategory, BlogPost } from '~/types/blog'

definePageMeta({
  middleware: [
    () => {
      const features = useFeatures()
      if (!features.hasFeature('blog')) {
        return navigateTo('/')
      }
    }
  ],
})

const api = useApi()
const settingsStore = useSettingsStore()
const route = useRoute()
const router = useRouter()
const { toPersianDigits } = usePersian()

const storeName = computed(() => settingsStore.settings.store_name || 'ایزیشاپ')

useSeoMeta({
  title: () => `مجله و مقالات تخصصی - ${storeName.value}`,
  description: 'جدیدترین مقالات، راهنماهای تخصصی انتخاب محصول، نکات مراقبت و آموزش‌های کاربردی.',
})

// Query state
const selectedCategory = ref<string>((route.query.category as string) || 'all')
const searchQuery = ref<string>((route.query.search as string) || '')
const currentPage = ref<number>(Number(route.query.page) || 1)

// Fetch Categories
const { data: categoriesResponse } = await useAsyncData('blog-categories', () =>
  api<ApiResponse<BlogCategory[]>>('/blog/categories')
)
const categories = computed(() => categoriesResponse.value?.data ?? [])

// Fetch Posts with reactive params
const { data: postsResponse, status, refresh } = await useAsyncData(
  'blog-posts',
  () => {
    const params: Record<string, any> = {
      page: currentPage.value,
      per_page: 9,
    }
    if (selectedCategory.value !== 'all') {
      params.category = selectedCategory.value
    }
    if (searchQuery.value.trim()) {
      params.search = searchQuery.value.trim()
    }
    return api<ApiResponse<BlogPost[]> & { meta?: any }>('/blog/posts', { params })
  },
  {
    watch: [selectedCategory, currentPage],
  }
)

const posts = computed(() => postsResponse.value?.data ?? [])
const paginationMeta = computed(() => (postsResponse.value as any)?.meta)

// Featured post for Hero spotlight (only on first page and when not searching)
const heroPost = computed(() => {
  if (selectedCategory.value === 'all' && !searchQuery.value && currentPage.value === 1) {
    return posts.value.find((p) => p.is_featured) || posts.value[0]
  }
  return null
})

// Remaining posts excluding the hero post
const gridPosts = computed(() => {
  if (heroPost.value) {
    return posts.value.filter((p) => p.id !== heroPost.value?.id)
  }
  return posts.value
})

const handleCategorySelect = (catSlug: string) => {
  selectedCategory.value = catSlug
  currentPage.value = 1
  router.push({
    query: {
      ...route.query,
      category: catSlug === 'all' ? undefined : catSlug,
      page: undefined,
    },
  })
}

const handleSearch = () => {
  currentPage.value = 1
  refresh()
}

const formatJalaliDate = (isoString?: string | null) => {
  if (!isoString) return ''
  try {
    const d = new Date(isoString)
    return new Intl.DateTimeFormat('fa-IR', {
      day: 'numeric',
      month: 'long',
      year: 'numeric',
    }).format(d)
  } catch {
    return ''
  }
}
</script>

<template>
  <div class="py-8 sm:py-12 flex flex-col gap-10 max-w-6xl mx-auto">
    <!-- Breadcrumb -->
    <nav class="flex items-center gap-2 text-xs text-neutral-400">
      <NuxtLink
        to="/"
        class="hover:text-primary transition-colors"
      >
        صفحه اصلی
      </NuxtLink>
      <span>/</span>
      <span class="text-neutral-700 dark:text-neutral-300 font-medium">مجله و وبلاگ</span>
    </nav>

    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-6 border-b border-neutral-200/80 dark:border-neutral-800 pb-8">
      <div class="flex flex-col gap-2 max-w-xl">
        <span class="px-3.5 py-1 rounded-full bg-primary-50 dark:bg-primary-950/40 text-primary-600 dark:text-primary-400 text-xs font-bold w-fit border border-primary-100 dark:border-primary-900/50">
          مجله تخصصی و مقالات آموزشی
        </span>
        <h1 class="text-2xl sm:text-4xl font-black text-neutral-900 dark:text-white tracking-tight">
          دانستنی‌ها، راهنماها و تازه‌ها
        </h1>
        <p class="text-xs sm:text-sm text-neutral-500 dark:text-neutral-400 leading-relaxed">
          جدیدترین راهنماهای خرید، مقایسه فرمولاسیون محصولات و ترفندهای تخصصی زیبایی به قلم کارشناسان {{ storeName }}
        </p>
      </div>

      <!-- Search Input -->
      <div class="w-full sm:w-72">
        <UInput
          v-model="searchQuery"
          icon="i-lucide-search"
          placeholder="جستجو در مقالات..."
          size="lg"
          class="w-full"
          @keydown.enter="handleSearch"
        />
      </div>
    </div>

    <!-- Category Filter Pills -->
    <div class="flex items-center gap-2 overflow-x-auto pb-2 flex-wrap">
      <button
        type="button"
        class="px-4 py-2 rounded-2xl text-xs font-bold transition-all cursor-pointer border shrink-0"
        :class="
          selectedCategory === 'all'
            ? 'bg-primary-500 text-white border-primary-500 shadow-sm shadow-primary-500/25'
            : 'bg-white dark:bg-neutral-900 text-neutral-600 dark:text-neutral-300 border-neutral-200 dark:border-neutral-800 hover:border-primary-300 dark:hover:border-primary-800'
        "
        @click="handleCategorySelect('all')"
      >
        همه مقالات
      </button>

      <button
        v-for="cat in categories"
        :key="cat.slug"
        type="button"
        class="px-4 py-2 rounded-2xl text-xs font-bold transition-all cursor-pointer border shrink-0 flex items-center gap-1.5"
        :class="
          selectedCategory === cat.slug
            ? 'bg-primary-500 text-white border-primary-500 shadow-sm shadow-primary-500/25'
            : 'bg-white dark:bg-neutral-900 text-neutral-600 dark:text-neutral-300 border-neutral-200 dark:border-neutral-800 hover:border-primary-300 dark:hover:border-primary-800'
        "
        @click="handleCategorySelect(cat.slug)"
      >
        <span>{{ cat.name }}</span>
        <span
          v-if="cat.posts_count"
          class="text-[10px] opacity-75 font-mono"
        >
          ({{ toPersianDigits(cat.posts_count) }})
        </span>
      </button>
    </div>

    <!-- Hero Spotlight Article (Featured) -->
    <div
      v-if="heroPost"
      class="bg-white dark:bg-neutral-900 border border-neutral-200/80 dark:border-neutral-800/80 rounded-3xl overflow-hidden shadow-xs grid grid-cols-1 lg:grid-cols-12 group transition-all"
    >
      <div class="lg:col-span-7 relative aspect-video lg:aspect-auto overflow-hidden bg-neutral-100 dark:bg-neutral-800 min-h-[260px]">
        <img
          v-if="heroPost.featured_image"
          :src="heroPost.featured_image"
          :alt="heroPost.title"
          class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700 ease-out"
        />
        <div
          v-else
          class="w-full h-full flex items-center justify-center text-neutral-400"
        >
          <UIcon
            name="i-lucide-image"
            class="size-16"
          />
        </div>
        <div class="absolute inset-0 bg-gradient-to-t from-neutral-950/60 via-transparent to-transparent lg:hidden" />
      </div>

      <div class="lg:col-span-5 p-6 sm:p-10 flex flex-col justify-between gap-6">
        <div class="flex flex-col gap-3">
          <div class="flex items-center gap-3">
            <span
              v-if="heroPost.category"
              class="px-3 py-1 rounded-xl bg-primary-50 dark:bg-primary-950/40 text-primary-600 dark:text-primary-400 text-xs font-bold border border-primary-100 dark:border-primary-900/50"
            >
              {{ heroPost.category.name }}
            </span>
            <span
              v-if="heroPost.is_featured"
              class="flex items-center gap-1 text-[11px] font-bold text-amber-500 bg-amber-50 dark:bg-amber-950/40 px-2.5 py-0.5 rounded-lg border border-amber-200 dark:border-amber-900/40"
            >
              <UIcon
                name="i-lucide-sparkles"
                class="size-3"
              />
              مقاله برگزیده
            </span>
          </div>

          <NuxtLink :to="`/blog/${heroPost.slug}`">
            <h2 class="text-xl sm:text-2xl font-black text-neutral-900 dark:text-white leading-tight group-hover:text-primary-600 dark:group-hover:text-primary-400 transition-colors">
              {{ heroPost.title }}
            </h2>
          </NuxtLink>

          <p class="text-xs sm:text-sm text-neutral-500 dark:text-neutral-400 leading-relaxed line-clamp-3">
            {{ heroPost.summary }}
          </p>
        </div>

        <div class="flex items-center justify-between pt-4 border-t border-neutral-100 dark:border-neutral-800 text-xs text-neutral-400">
          <div class="flex items-center gap-4">
            <span class="flex items-center gap-1">
              <UIcon
                name="i-lucide-clock"
                class="size-3.5"
              />
              {{ toPersianDigits(heroPost.reading_time) }} دقیقه مطالعه
            </span>
            <span v-if="heroPost.published_at">
              {{ formatJalaliDate(heroPost.published_at) }}
            </span>
          </div>

          <NuxtLink
            :to="`/blog/${heroPost.slug}`"
            class="text-xs font-bold text-primary flex items-center gap-1 group-hover:-translate-x-1 transition-transform"
          >
            <span>مطالعه مقاله</span>
            <UIcon
              name="i-lucide-arrow-left"
              class="size-3.5"
            />
          </NuxtLink>
        </div>
      </div>
    </div>

    <!-- Articles Grid -->
    <div
      v-if="status === 'pending'"
      class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6"
    >
      <div
        v-for="i in 6"
        :key="i"
        class="bg-white dark:bg-neutral-900 border border-neutral-200/80 dark:border-neutral-800/80 rounded-3xl overflow-hidden shadow-xs animate-pulse"
      >
        <div class="aspect-video bg-neutral-200 dark:bg-neutral-800" />
        <div class="p-6 space-y-3">
          <div class="h-4 bg-neutral-200 dark:bg-neutral-800 rounded w-1/3" />
          <div class="h-5 bg-neutral-200 dark:bg-neutral-800 rounded w-full" />
          <div class="h-3 bg-neutral-100 dark:bg-neutral-800/60 rounded w-4/5" />
        </div>
      </div>
    </div>

    <div
      v-else-if="gridPosts.length > 0"
      class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6"
    >
      <article
        v-for="post in gridPosts"
        :key="post.id"
        class="bg-white dark:bg-neutral-900 border border-neutral-200/80 dark:border-neutral-800/80 rounded-3xl overflow-hidden shadow-xs hover:shadow-lg transition-all group flex flex-col justify-between"
      >
        <div>
          <!-- Thumbnail -->
          <NuxtLink
            :to="`/blog/${post.slug}`"
            class="block aspect-[16/10] overflow-hidden bg-neutral-100 dark:bg-neutral-800 relative"
          >
            <img
              v-if="post.featured_image"
              :src="post.featured_image"
              :alt="post.title"
              class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500 ease-out"
              loading="lazy"
            />
            <div
              v-else
              class="w-full h-full flex items-center justify-center text-neutral-300 dark:text-neutral-700"
            >
              <UIcon
                name="i-lucide-image"
                class="size-12"
              />
            </div>

            <!-- Category Badge Over Image -->
            <div
              v-if="post.category"
              class="absolute top-3 right-3"
            >
              <span class="px-3 py-1 rounded-xl bg-white/90 dark:bg-neutral-900/90 backdrop-blur-md text-primary-600 dark:text-primary-400 text-xs font-bold shadow-xs">
                {{ post.category.name }}
              </span>
            </div>
          </NuxtLink>

          <!-- Content Details -->
          <div class="p-6 flex flex-col gap-2.5">
            <div class="flex items-center gap-3 text-[11px] text-neutral-400">
              <span class="flex items-center gap-1">
                <UIcon
                  name="i-lucide-clock"
                  class="size-3"
                />
                {{ toPersianDigits(post.reading_time) }} دقیقه مطالعه
              </span>
              <span>•</span>
              <span v-if="post.published_at">{{ formatJalaliDate(post.published_at) }}</span>
            </div>

            <NuxtLink :to="`/blog/${post.slug}`">
              <h3 class="text-base font-black text-neutral-900 dark:text-white leading-snug group-hover:text-primary-600 dark:group-hover:text-primary-400 transition-colors line-clamp-2">
                {{ post.title }}
              </h3>
            </NuxtLink>

            <p class="text-xs text-neutral-500 dark:text-neutral-400 leading-relaxed line-clamp-3">
              {{ post.summary }}
            </p>
          </div>
        </div>

        <!-- Footer strip -->
        <div class="px-6 pb-6 pt-3 flex items-center justify-between border-t border-neutral-100 dark:border-neutral-800 text-xs">
          <div class="flex items-center gap-2 text-neutral-500 dark:text-neutral-400">
            <div class="size-6 rounded-full bg-primary/10 text-primary flex items-center justify-center text-[10px] font-bold">
              {{ post.author?.name?.[0] || 'ت' }}
            </div>
            <span class="text-[11px] font-medium">{{ post.author?.name || 'تحریریه فروشگاه' }}</span>
          </div>

          <NuxtLink
            :to="`/blog/${post.slug}`"
            class="text-xs font-bold text-primary flex items-center gap-1 group-hover:-translate-x-1 transition-transform"
          >
            <span>مطالعه</span>
            <UIcon
              name="i-lucide-arrow-left"
              class="size-3.5"
            />
          </NuxtLink>
        </div>
      </article>
    </div>

    <!-- Empty State -->
    <div
      v-else
      class="py-16 text-center flex flex-col items-center gap-3 bg-white dark:bg-neutral-900 border border-neutral-200/80 dark:border-neutral-800/80 rounded-3xl p-8"
    >
      <UIcon
        name="i-lucide-newspaper"
        class="size-12 text-neutral-300 dark:text-neutral-700"
      />
      <h3 class="text-base font-bold text-neutral-800 dark:text-neutral-200">
        مقاله‌ای یافت نشد
      </h3>
      <p class="text-xs text-neutral-400 max-w-sm">
        نتیجه‌ای متناسب با فیلترها یا عبارت جستجو شده پیدا نشد. می‌توانید با انتخاب دسته‌بندی دیگر به سایر مقالات دسترسی داشته باشید.
      </p>
      <UButton
        color="neutral"
        variant="outline"
        size="sm"
        class="mt-2 cursor-pointer"
        @click="handleCategorySelect('all')"
      >
        مشاهده همه مقالات
      </UButton>
    </div>
  </div>
</template>
