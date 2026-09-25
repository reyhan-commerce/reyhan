<script setup lang="ts">
export interface BrandDisplayItem {
  name: string
  slug: string
  name_en?: string | null
  icon: string
}

interface Props {
  brands: BrandDisplayItem[]
}

defineProps<Props>()
</script>

<template>
  <section
    v-if="brands.length > 0"
    class="flex flex-col gap-5 py-6 border-t border-neutral-200/80 dark:border-neutral-800"
  >
    <h3 class="text-center font-black text-xs sm:text-sm text-neutral-400">
      اصیل‌ترین برندهای معتبر جهانی و ایرانی
    </h3>
    <div class="flex flex-wrap items-center justify-center gap-3 sm:gap-6">
      <NuxtLink
        v-for="brand in brands"
        :key="brand.slug"
        :to="`/products?brand=${brand.slug}`"
        class="flex items-center gap-2.5 px-5 py-3 rounded-2xl bg-white dark:bg-neutral-900 border border-neutral-200/70 dark:border-neutral-800 hover:border-primary/50 dark:hover:border-primary/50 transition-all duration-200 hover:shadow-md hover:-translate-y-0.5"
      >
        <UIcon
          :name="brand.icon"
          class="size-4.5 text-primary"
        />
        <span class="font-bold text-sm text-neutral-800 dark:text-neutral-200">{{ brand.name }}</span>
        <span
          v-if="brand.name_en"
          class="text-xs text-neutral-400 font-mono"
        >({{ brand.name_en }})</span>
      </NuxtLink>
    </div>
  </section>
</template>
