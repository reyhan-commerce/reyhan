<script setup lang="ts">
import type { BlogPost } from '~/types/blog'

interface Props {
  articles: BlogPost[]
}

defineProps<Props>()

const { toPersianDigits } = usePersian()
</script>

<template>
  <section class="flex flex-col gap-6 py-6 border-t border-neutral-200/80 dark:border-neutral-800">
    <div class="flex items-center justify-between">
      <div class="flex items-center gap-3">
        <div class="w-1.5 h-6 rounded-full bg-primary" />
        <h2 class="text-xl sm:text-2xl font-black text-neutral-900 dark:text-white">
          مجله تخصصی و تازه‌ترین مقالات
        </h2>
      </div>
      <UButton
        to="/blog"
        color="neutral"
        variant="ghost"
        trailing-icon="i-lucide-arrow-left"
        size="sm"
        class="font-bold hover:text-primary cursor-pointer"
      >
        ورود به وبلاگ
      </UButton>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
      <article
        v-for="article in articles"
        :key="article.id"
        class="bg-white dark:bg-neutral-900 border border-neutral-200/80 dark:border-neutral-800/80 rounded-3xl overflow-hidden shadow-xs hover:shadow-lg transition-all group flex flex-col justify-between"
      >
        <div>
          <NuxtLink
            :to="`/blog/${article.slug}`"
            class="block aspect-[16/10] overflow-hidden bg-neutral-100 dark:bg-neutral-800 relative"
          >
            <NuxtImg
              v-if="article.featured_image"
              :src="article.featured_image"
              :alt="article.title"
              class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500 ease-out"
              loading="lazy"
              format="webp"
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

            <div
              v-if="article.category"
              class="absolute top-3 right-3"
            >
              <span class="px-3 py-1 rounded-xl bg-white/90 dark:bg-neutral-900/90 backdrop-blur-md text-primary-600 dark:text-primary-400 text-xs font-bold shadow-xs">
                {{ article.category.name }}
              </span>
            </div>
          </NuxtLink>

          <div class="p-6 flex flex-col gap-2.5">
            <div class="flex items-center gap-2 text-[11px] text-neutral-400">
              <UIcon
                name="i-lucide-clock"
                class="size-3"
              />
              <span>{{ toPersianDigits(article.reading_time) }} دقیقه مطالعه</span>
            </div>

            <NuxtLink :to="`/blog/${article.slug}`">
              <h3 class="text-base font-black text-neutral-900 dark:text-white leading-snug group-hover:text-primary-600 dark:group-hover:text-primary-400 transition-colors line-clamp-2">
                {{ article.title }}
              </h3>
            </NuxtLink>

            <p class="text-xs text-neutral-500 dark:text-neutral-400 leading-relaxed line-clamp-2">
              {{ article.summary }}
            </p>
          </div>
        </div>

        <div class="px-6 pb-6 pt-3 flex items-center justify-between border-t border-neutral-100 dark:border-neutral-800 text-xs">
          <span class="text-[11px] text-neutral-400">{{ article.author?.name || 'تحریریه فروشگاه' }}</span>
          <NuxtLink
            :to="`/blog/${article.slug}`"
            class="text-xs font-bold text-primary flex items-center gap-1 group-hover:-translate-x-1 transition-transform"
          >
            <span>مطالعه مقاله</span>
            <UIcon
              name="i-lucide-arrow-left"
              class="size-3.5"
            />
          </NuxtLink>
        </div>
      </article>
    </div>
  </section>
</template>
