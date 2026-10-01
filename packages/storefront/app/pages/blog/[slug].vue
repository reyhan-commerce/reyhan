<script setup lang="ts">
import type { BlogPostDetailResponse } from '~/types/blog'

definePageMeta({
  middleware: [
    () => {
      const features = useFeatures()
      if (!features.hasFeature('blog')) {
        return navigateTo('/')
      }
    }
  ]
})

const route = useRoute()
const api = useApi()
const toast = useToast()
const settingsStore = useSettingsStore()
const { toPersianDigits } = usePersian()

const slug = computed(() => route.params.slug as string)
const storeName = computed(() => settingsStore.settings.store_name || settingsStore.settings.store_name)

const { data: postResponse, error } = await useAsyncData(`blog-post-${slug.value}`, () =>
  api<BlogPostDetailResponse>(`/blog/posts/${slug.value}`)
)

if (error.value || !postResponse.value?.data) {
  throw createError({
    statusCode: 404,
    statusMessage: 'مقاله مورد نظر یافت نشد'
  })
}

const post = computed(() => postResponse.value?.data)
const relatedPosts = computed(() => postResponse.value?.related ?? [])

useSeoMeta({
  title: () => post.value?.meta_title || post.value?.title || '',
  description: () => post.value?.meta_description || post.value?.summary || '',
  ogTitle: () => post.value?.title,
  ogDescription: () => post.value?.summary || '',
  ogImage: () => post.value?.featured_image
})

const formatJalaliDate = (isoString?: string | null) => {
  if (!isoString) return ''
  try {
    const d = new Date(isoString)
    return new Intl.DateTimeFormat('fa-IR', {
      day: 'numeric',
      month: 'long',
      year: 'numeric'
    }).format(d)
  } catch {
    return ''
  }
}

const copyShareLink = async () => {
  if (import.meta.client) {
    try {
      await navigator.clipboard.writeText(window.location.href)
      toast.add({
        title: 'لینک کپی شد',
        description: 'پیوند این مقاله در حافظه موقت شما کپی گردید.',
        color: 'success'
      })
    } catch {
      toast.add({
        title: 'خطا در کپی',
        description: 'امکان کپی کردن پیوند وجود ندارد.',
        color: 'error'
      })
    }
  }
}

const shareTelegramUrl = computed(() => {
  if (!import.meta.client) return '#'
  return `https://t.me/share/url?url=${encodeURIComponent(window.location.href)}&text=${encodeURIComponent(post.value?.title || '')}`
})

const shareWhatsappUrl = computed(() => {
  if (!import.meta.client) return '#'
  return `https://api.whatsapp.com/send?text=${encodeURIComponent(`${post.value?.title} - ${window.location.href}`)}`
})
</script>

<template>
  <div
    v-if="post"
    class="py-8 sm:py-12 flex flex-col gap-10 max-w-4xl mx-auto"
  >
    <!-- Breadcrumb -->
    <nav class="flex items-center gap-2 text-xs text-neutral-400">
      <NuxtLink
        to="/"
        class="hover:text-primary transition-colors"
      >
        صفحه اصلی
      </NuxtLink>
      <span>/</span>
      <NuxtLink
        to="/blog"
        class="hover:text-primary transition-colors"
      >
        مجله و وبلاگ
      </NuxtLink>
      <template v-if="post.category">
        <span>/</span>
        <NuxtLink
          :to="`/blog?category=${post.category.slug}`"
          class="hover:text-primary transition-colors"
        >
          {{ post.category.name }}
        </NuxtLink>
      </template>
      <span>/</span>
      <span class="text-neutral-700 dark:text-neutral-300 font-medium truncate max-w-[200px]">
        {{ post.title }}
      </span>
    </nav>

    <!-- Article Header Card -->
    <header class="flex flex-col gap-4 text-right">
      <div class="flex items-center gap-3 flex-wrap text-xs">
        <NuxtLink
          v-if="post.category"
          :to="`/blog?category=${post.category.slug}`"
          class="px-3 py-1 rounded-xl bg-primary-50 dark:bg-primary-950/40 text-primary-600 dark:text-primary-400 font-bold border border-primary-100 dark:border-primary-900/50 hover:bg-primary-100 dark:hover:bg-primary-900/60 transition-colors"
        >
          {{ post.category.name }}
        </NuxtLink>

        <span class="flex items-center gap-1 text-neutral-400">
          <UIcon
            name="i-lucide-clock"
            class="size-3.5"
          />
          {{ toPersianDigits(post.reading_time) }} دقیقه زمان مطالعه
        </span>

        <span class="text-neutral-300 dark:text-neutral-700">•</span>

        <span class="flex items-center gap-1 text-neutral-400">
          <UIcon
            name="i-lucide-eye"
            class="size-3.5"
          />
          {{ toPersianDigits(post.views_count) }} بازدید
        </span>

        <span
          v-if="post.published_at"
          class="text-neutral-300 dark:text-neutral-700"
        >•</span>

        <span
          v-if="post.published_at"
          class="text-neutral-400"
        >
          {{ formatJalaliDate(post.published_at) }}
        </span>
      </div>

      <h1 class="text-2xl sm:text-4xl font-black text-neutral-900 dark:text-white leading-tight tracking-tight">
        {{ post.title }}
      </h1>

      <p
        v-if="post.summary"
        class="text-sm sm:text-base text-neutral-600 dark:text-neutral-300 leading-relaxed bg-neutral-50 dark:bg-neutral-900/50 border-r-4 border-primary p-4 rounded-2xl"
      >
        {{ post.summary }}
      </p>
    </header>

    <!-- Featured Image -->
    <div
      v-if="post.featured_image"
      class="aspect-[16/9] rounded-3xl overflow-hidden shadow-sm bg-neutral-100 dark:bg-neutral-800 relative"
    >
      <img
        :src="post.featured_image"
        :alt="post.title"
        class="w-full h-full object-cover"
      >
    </div>

    <!-- Article Body -->
    <div class="bg-white dark:bg-neutral-900 border border-neutral-200/80 dark:border-neutral-800/80 rounded-3xl p-6 sm:p-10 shadow-xs">
      <!-- Article Text / Content -->
      <div
        class="text-sm sm:text-base leading-loose text-neutral-700 dark:text-neutral-300 space-y-6 font-normal"
        style="white-space: pre-line;"
      >
        {{ post.content }}
      </div>

      <!-- Tags Section -->
      <div
        v-if="post.tags && post.tags.length > 0"
        class="mt-10 pt-6 border-t border-neutral-100 dark:border-neutral-800 flex items-center gap-2 flex-wrap"
      >
        <span class="text-xs font-bold text-neutral-400 flex items-center gap-1">
          <UIcon
            name="i-lucide-tags"
            class="size-4"
          />
          برچسب‌ها:
        </span>
        <NuxtLink
          v-for="t in post.tags"
          :key="t"
          :to="`/blog?search=${encodeURIComponent(t)}`"
          class="px-3 py-1 rounded-xl bg-neutral-100 dark:bg-neutral-800 hover:bg-primary-50 dark:hover:bg-primary-950/40 text-neutral-600 dark:text-neutral-300 hover:text-primary text-xs font-medium transition-colors"
        >
          #{{ t }}
        </NuxtLink>
      </div>

      <!-- Social Share Strip -->
      <div class="mt-8 pt-6 border-t border-neutral-100 dark:border-neutral-800 flex items-center justify-between flex-wrap gap-4">
        <span class="text-xs font-bold text-neutral-500 dark:text-neutral-400">
          اشتراک‌گذاری این مقاله با دوستان:
        </span>

        <div class="flex items-center gap-2">
          <a
            :href="shareTelegramUrl"
            target="_blank"
            rel="noopener noreferrer"
            class="size-9 rounded-xl bg-neutral-100 dark:bg-neutral-800 hover:bg-sky-500/10 hover:text-sky-500 text-neutral-600 dark:text-neutral-300 flex items-center justify-center transition-colors"
            aria-label="اشتراک در تلگرام"
          >
            <UIcon
              name="i-lucide-send"
              class="size-4"
            />
          </a>

          <a
            :href="shareWhatsappUrl"
            target="_blank"
            rel="noopener noreferrer"
            class="size-9 rounded-xl bg-neutral-100 dark:bg-neutral-800 hover:bg-emerald-500/10 hover:text-emerald-500 text-neutral-600 dark:text-neutral-300 flex items-center justify-center transition-colors"
            aria-label="اشتراک در واتساپ"
          >
            <UIcon
              name="i-lucide-message-circle"
              class="size-4"
            />
          </a>

          <button
            type="button"
            class="flex items-center gap-1.5 px-3 py-2 rounded-xl bg-neutral-100 dark:bg-neutral-800 hover:bg-neutral-200 dark:hover:bg-neutral-700 text-xs font-bold text-neutral-700 dark:text-neutral-200 transition-colors cursor-pointer"
            @click="copyShareLink"
          >
            <UIcon
              name="i-lucide-copy"
              class="size-3.5"
            />
            <span>کپی لینک</span>
          </button>
        </div>
      </div>
    </div>

    <!-- Author Bio Box -->
    <div class="bg-white dark:bg-neutral-900 border border-neutral-200/80 dark:border-neutral-800/80 rounded-3xl p-6 shadow-xs flex items-center gap-4">
      <div class="size-14 rounded-2xl bg-gradient-to-tr from-primary-600 to-primary-400 text-white flex items-center justify-center text-lg font-black shrink-0 shadow-md shadow-primary/20">
        {{ post.author?.name?.[0] || 'ت' }}
      </div>
      <div class="flex flex-col gap-1">
        <span class="text-xs font-bold text-primary">نویسنده مقاله</span>
        <h4 class="text-sm font-black text-neutral-900 dark:text-white">
          {{ post.author?.name || 'تیم تحریریه و کارشناسان زیبایی' }}
        </h4>
        <p class="text-xs text-neutral-400">
          تولید محتوای موثق و علمی با هدف ارتقای دانش سلامت و زیبایی مصرف‌کنندگان.
        </p>
      </div>
    </div>

    <!-- Related Articles Section -->
    <div
      v-if="relatedPosts.length > 0"
      class="flex flex-col gap-6"
    >
      <div class="flex items-center justify-between">
        <h3 class="text-lg font-black text-neutral-900 dark:text-white flex items-center gap-2">
          <span class="w-2 h-5 rounded-full bg-primary" />
          <span>مقالات مرتبط و پیشنهادی</span>
        </h3>
        <NuxtLink
          to="/blog"
          class="text-xs font-bold text-primary hover:underline"
        >
          مشاهده همه مقالات
        </NuxtLink>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <NuxtLink
          v-for="rel in relatedPosts"
          :key="rel.id"
          :to="`/blog/${rel.slug}`"
          class="bg-white dark:bg-neutral-900 border border-neutral-200/80 dark:border-neutral-800/80 rounded-2xl p-4 shadow-xs hover:border-primary/50 transition-all group flex flex-col gap-3"
        >
          <div class="aspect-video rounded-xl overflow-hidden bg-neutral-100 dark:bg-neutral-800 relative">
            <img
              v-if="rel.featured_image"
              :src="rel.featured_image"
              :alt="rel.title"
              class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
            >
          </div>
          <h4 class="text-xs font-bold text-neutral-900 dark:text-white line-clamp-2 group-hover:text-primary transition-colors leading-snug">
            {{ rel.title }}
          </h4>
          <span class="text-[11px] text-neutral-400 flex items-center gap-1 mt-auto">
            <UIcon
              name="i-lucide-clock"
              class="size-3"
            />
            {{ toPersianDigits(rel.reading_time) }} دقیقه مطالعه
          </span>
        </NuxtLink>
      </div>
    </div>
  </div>
</template>
