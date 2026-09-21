<script setup lang="ts">
const catalogStore = useCatalogStore()

await useAsyncData('categories-tree-page', () => catalogStore.fetchCategoryTree())

useSeoMeta({
  title: 'نقشه کامل دسته‌بندی‌های کالا - ایزیشاپ',
  description: 'مشاهده تمام دسته‌بندی‌های مراقبت پوست، آرایشی، مراقبت مو و عطر و ادکلن در فروشگاه اینترنتی ایزیشاپ'
})
</script>

<template>
  <div class="flex flex-col gap-8 py-6">
    <!-- Header -->
    <div class="flex flex-col gap-2">
      <div class="flex items-center gap-2 text-xs text-neutral-400">
        <NuxtLink
          to="/"
          class="hover:text-primary transition-colors"
        >صفحه اصلی</NuxtLink>
        <span>/</span>
        <span class="text-neutral-700 dark:text-neutral-300 font-medium">دسته‌بندی‌های محصولات</span>
      </div>
      <h1 class="text-2xl sm:text-3xl font-black text-neutral-900 dark:text-white">
        نقشه دسته‌بندی‌های فروشگاه
      </h1>
      <p class="text-sm text-neutral-500">
        برای یافتن سریع کالای مورد نظر، دسته‌بندی مربوطه را انتخاب نمایید.
      </p>
    </div>

    <!-- Category Trees Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
      <div
        v-for="rootCat in catalogStore.categoryTree"
        :key="rootCat.id"
        class="flex flex-col p-6 rounded-3xl bg-white dark:bg-neutral-900 border border-neutral-200/80 dark:border-neutral-800 shadow-xs hover:shadow-md transition-shadow"
      >
        <!-- Root Header -->
        <NuxtLink
          :to="`/categories/${rootCat.slug}`"
          class="flex items-center justify-between pb-4 border-b border-neutral-100 dark:border-neutral-800 group"
        >
          <div class="flex items-center gap-3">
            <div class="size-12 rounded-2xl bg-primary/10 text-primary flex items-center justify-center">
              <UIcon
                :name="rootCat.icon || 'i-lucide-folder'"
                class="size-6"
              />
            </div>
            <span class="text-lg font-black text-neutral-900 dark:text-white group-hover:text-primary transition-colors">
              {{ rootCat.name }}
            </span>
          </div>
          <UIcon
            name="i-lucide-arrow-left"
            class="size-5 text-neutral-400 group-hover:text-primary transition-colors"
          />
        </NuxtLink>

        <!-- Subcategories List -->
        <div
          v-if="rootCat.children && rootCat.children.length > 0"
          class="flex flex-col gap-2 pt-4"
        >
          <NuxtLink
            v-for="subCat in rootCat.children"
            :key="subCat.id"
            :to="`/categories/${subCat.slug}`"
            class="flex items-center justify-between py-2 px-3 rounded-xl hover:bg-neutral-50 dark:hover:bg-neutral-800/60 transition-colors text-sm font-medium text-neutral-700 dark:text-neutral-300 hover:text-primary"
          >
            <span>{{ subCat.name }}</span>
            <span
              v-if="subCat.children && subCat.children.length > 0"
              class="text-xs text-neutral-400"
            >
              ({{ subCat.children.length }})
            </span>
          </NuxtLink>
        </div>

        <div
          v-else
          class="py-4 text-xs text-neutral-400"
        >
          مشاهده تمام کالاهای این بخش
        </div>
      </div>
    </div>
  </div>
</template>
