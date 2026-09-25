<script setup lang="ts">
const catalogStore = useCatalogStore()
const { toPersianDigits } = usePersian()

await useAsyncData('categories-tree-page', () => catalogStore.fetchCategoryTree().then(v => v.length > 0 ? v : null))

const iconMap: Record<string, string> = {
  skincare: 'i-lucide-sparkles',
  makeup: 'i-lucide-heart',
  haircare: 'i-lucide-scissors',
  fragrance: 'i-lucide-flame'
}

useSeoMeta({
  title: 'نقشه کامل دسته‌بندی‌های کالا - ایزیشاپ',
  description: 'مشاهده تمام دسته‌بندی‌های مراقبت پوست، آرایشی، مراقبت مو و عطر و ادکلن در فروشگاه اینترنتی ایزیشاپ'
})
</script>

<template>
  <div class="flex flex-col gap-8 py-6 pb-16">
    <!-- Header -->
    <div class="flex flex-col gap-2.5">
      <div class="flex items-center gap-2 text-xs text-neutral-400 font-medium">
        <NuxtLink
          to="/"
          class="hover:text-primary transition-colors"
        >
          صفحه اصلی
        </NuxtLink>
        <span>/</span>
        <span class="text-neutral-700 dark:text-neutral-300">دسته‌بندی‌های محصولات</span>
      </div>
      <h1 class="text-2xl sm:text-4xl font-black text-neutral-900 dark:text-white tracking-tight">
        نقشه دسته‌بندی‌های فروشگاه
      </h1>
      <p class="text-xs sm:text-sm text-neutral-500 max-w-xl leading-relaxed">
        محصولات تخصصی بر اساس کاربرد و برند تفکیک شده‌اند؛ برای دسترسی سریع گروه مورد نظر خود را انتخاب نمایید.
      </p>
    </div>

    <!-- Category Trees Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      <div
        v-for="rootCat in catalogStore.categoryTree"
        :key="rootCat.id"
        class="flex flex-col p-6 sm:p-7 rounded-3xl bg-white dark:bg-neutral-900 border border-neutral-200/80 dark:border-neutral-800/80 shadow-xs hover:shadow-xl transition-all duration-300 hover:-translate-y-1 justify-between"
      >
        <!-- Root Header -->
        <div>
          <NuxtLink
            :to="`/categories/${rootCat.slug}`"
            class="flex items-center justify-between pb-5 border-b border-neutral-100 dark:border-neutral-800/80 group"
          >
            <div class="flex items-center gap-3.5">
              <div class="size-13 rounded-2xl bg-primary/10 text-primary flex items-center justify-center group-hover:scale-105 transition-transform">
                <UIcon
                  :name="iconMap[rootCat.slug] || rootCat.icon || 'i-lucide-folder'"
                  class="size-7"
                />
              </div>
              <div class="flex flex-col">
                <span class="text-base sm:text-lg font-black text-neutral-900 dark:text-white group-hover:text-primary transition-colors">
                  {{ rootCat.name }}
                </span>
                <span
                  v-if="rootCat.children"
                  class="text-xs text-neutral-400 mt-0.5"
                >
                  {{ toPersianDigits(rootCat.children.length) }} زیردسته تخصصی
                </span>
              </div>
            </div>
            <div class="size-8 rounded-xl bg-neutral-100 dark:bg-neutral-800 group-hover:bg-primary group-hover:text-white text-neutral-400 flex items-center justify-center transition-colors">
              <UIcon
                name="i-lucide-arrow-left"
                class="size-4 group-hover:-translate-x-0.5 transition-transform"
              />
            </div>
          </NuxtLink>

          <!-- Subcategories List -->
          <div
            v-if="rootCat.children && rootCat.children.length > 0"
            class="grid grid-cols-2 gap-2 pt-4"
          >
            <NuxtLink
              v-for="subCat in rootCat.children"
              :key="subCat.id"
              :to="`/categories/${subCat.slug}`"
              class="flex items-center justify-between py-2 px-3 rounded-xl hover:bg-neutral-100 dark:hover:bg-neutral-800/70 transition-colors text-xs font-medium text-neutral-700 dark:text-neutral-300 hover:text-primary group/item"
            >
              <span class="truncate">{{ subCat.name }}</span>
              <UIcon
                name="i-lucide-chevron-left"
                class="size-3 text-neutral-400 group-hover/item:text-primary opacity-0 group-hover/item:opacity-100 transition-opacity"
              />
            </NuxtLink>
          </div>

          <div
            v-else
            class="py-6 text-xs text-neutral-400 text-center"
          >
            کالاهای موجود در این دسته
          </div>
        </div>

        <!-- Action footer -->
        <div class="pt-5 mt-4 border-t border-neutral-100 dark:border-neutral-800/80">
          <NuxtLink
            :to="`/categories/${rootCat.slug}`"
            class="w-full py-2.5 px-4 rounded-xl bg-neutral-50 dark:bg-neutral-800/60 hover:bg-primary hover:text-white dark:hover:bg-primary text-xs font-bold text-neutral-700 dark:text-neutral-300 flex items-center justify-center gap-1.5 transition-colors"
          >
            <span>مشاهده همه محصولات {{ rootCat.name }}</span>
            <UIcon
              name="i-lucide-arrow-left"
              class="size-3.5"
            />
          </NuxtLink>
        </div>
      </div>
    </div>
  </div>
</template>
