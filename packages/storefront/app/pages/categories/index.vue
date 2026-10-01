<script setup lang="ts">
const catalogStore = useCatalogStore()
const { toPersianDigits } = usePersian()

await useAsyncData('categories-tree-page', () => catalogStore.fetchCategoryTree().then(v => v.length > 0 ? v : null))

const iconMap: Record<string, string> = {
  skincare: 'i-lucide-sparkles',
  makeup: 'i-lucide-heart',
  haircare: 'i-lucide-scissors',
  fragrance: 'i-lucide-flame',
  bodycare: 'i-lucide-droplets',
  sunscreen: 'i-lucide-sun',
  tools: 'i-lucide-brush',
  men: 'i-lucide-user',
  mother_baby: 'i-lucide-baby',
  health: 'i-lucide-shield-plus',
  perfume: 'i-lucide-flame',
  cosmetics: 'i-lucide-heart'
}

// Active root category for mobile 2-column explorer
const activeRootId = ref<number>(catalogStore.categoryTree[0]?.id || 0)

// Expanded accordion subcategory (Level 2) on mobile
const expandedSubId = ref<number | null>(null)

function toggleAccordion(id: number) {
  expandedSubId.value = expandedSubId.value === id ? null : id
}

const activeRootCategory = computed(() => {
  if (!catalogStore.categoryTree || catalogStore.categoryTree.length === 0) return null
  return catalogStore.categoryTree.find(c => c.id === activeRootId.value) || catalogStore.categoryTree[0]
})

// Auto-expand the first Level 2 category when root changes (just like Digikala)
watch(activeRootCategory, (root) => {
  if (root?.children && root.children.length > 0) {
    expandedSubId.value = root.children[0]?.id || null
  } else {
    expandedSubId.value = null
  }
}, { immediate: true })

// Ensure initial active category is set once loaded
watch(() => catalogStore.categoryTree, (tree) => {
  if (tree.length > 0 && (!activeRootId.value || !tree.some(c => c.id === activeRootId.value))) {
    activeRootId.value = tree[0]?.id || 0
  }
}, { immediate: true })

useSeoMeta({
  title: 'دسته‌بندی‌های محصولات',
  description: 'مشاهده تمام دسته‌بندی‌های مراقبت پوست، آرایشی، مراقبت مو و عطر و ادکلن'
})
</script>

<template>
  <div>
    <!-- ========================================== -->
    <!-- 1. MOBILE DIGIKALA-STYLE 2-COLUMN EXPLORER (<lg) -->
    <!-- ========================================== -->
    <div class="lg:hidden flex flex-col h-[calc(100dvh-125px)] -mx-4 -mt-4 -mb-16 overflow-hidden bg-white dark:bg-neutral-950">
      <!-- Mobile Top Header Bar -->
      <div class="px-4 py-2.5 bg-white dark:bg-neutral-900 border-b border-neutral-200/80 dark:border-neutral-800 flex items-center justify-between shrink-0">
        <div class="flex items-center gap-2">
          <div class="size-8 rounded-xl bg-primary/10 text-primary flex items-center justify-center">
            <UIcon
              name="i-lucide-layout-grid"
              class="size-4.5"
            />
          </div>
          <div>
            <h1 class="text-sm font-black text-neutral-900 dark:text-white">
              دسته‌بندی کالاها
            </h1>
            <p class="text-[10px] text-neutral-400">
              انتخاب سریع گروه کالایی
            </p>
          </div>
        </div>

        <NuxtLink
          to="/products"
          class="text-xs font-bold text-primary flex items-center gap-1 hover:underline"
        >
          <span>همه محصولات</span>
          <UIcon
            name="i-lucide-chevron-left"
            class="size-3.5"
          />
        </NuxtLink>
      </div>

      <!-- Main 2-Column Split Body -->
      <div class="flex flex-1 overflow-hidden">
        <!-- Right Column (Level 1): Vertical Root Categories Rail -->
        <aside
          class="w-22 sm:w-24 shrink-0 bg-neutral-100/80 dark:bg-neutral-900/90 border-l border-neutral-200/80 dark:border-neutral-800/80 overflow-y-auto scrollbar-none py-1 flex flex-col gap-0.5"
          aria-label="دسته‌های اصلی"
        >
          <button
            v-for="rootCat in catalogStore.categoryTree"
            :key="rootCat.id"
            type="button"
            class="w-full flex flex-col items-center justify-center py-3.5 px-1 gap-1.5 transition-all text-center relative cursor-pointer"
            :class="[
              activeRootId === rootCat.id
                ? 'bg-white dark:bg-neutral-950 text-primary font-black shadow-xs'
                : 'text-neutral-500 dark:text-neutral-400 hover:bg-neutral-200/60 dark:hover:bg-neutral-800/60 font-medium'
            ]"
            @click="activeRootId = rootCat.id"
          >
            <!-- Active Indicator Bar on the Right (RTL) -->
            <div
              v-if="activeRootId === rootCat.id"
              class="absolute right-0 inset-y-1.5 w-1 rounded-l-full bg-primary"
            />

            <div
              class="size-9 rounded-2xl flex items-center justify-center transition-transform"
              :class="activeRootId === rootCat.id ? 'bg-primary/10 text-primary scale-105' : 'bg-neutral-200/60 dark:bg-neutral-800 text-neutral-600 dark:text-neutral-300'"
            >
              <UIcon
                :name="iconMap[rootCat.slug] || rootCat.icon || 'i-lucide-sparkles'"
                class="size-5"
              />
            </div>

            <span class="text-[10px] leading-tight line-clamp-2 px-1">
              {{ rootCat.name }}
            </span>
          </button>
        </aside>

        <!-- Left Column (Level 2 & Level 3): Accordion Groups & Visual Items -->
        <main
          v-if="activeRootCategory"
          class="flex-1 overflow-y-auto p-3.5 pb-28 space-y-4 bg-white dark:bg-neutral-950"
        >
          <!-- 1. Header Link: View All Products of this Category -->
          <NuxtLink
            :to="`/categories/${activeRootCategory.slug}`"
            class="flex items-center justify-between p-3 rounded-2xl bg-neutral-50 dark:bg-neutral-900 border border-neutral-100 dark:border-neutral-800/80 text-xs font-black text-neutral-900 dark:text-white hover:text-primary transition-colors group cursor-pointer shadow-xs"
          >
            <span class="text-primary font-bold">همه محصولات {{ activeRootCategory.name }}</span>
            <UIcon
              name="i-lucide-chevron-left"
              class="size-4 text-neutral-400 group-hover:text-primary transition-transform group-hover:-translate-x-0.5"
            />
          </NuxtLink>

          <!-- Section Title (e.g. انتخاب موبایل / انتخاب مراقبت پوست) -->
          <div class="px-1 pt-1">
            <h2 class="text-xs font-black text-neutral-900 dark:text-neutral-100">
              انتخاب {{ activeRootCategory.name }}
            </h2>
          </div>

          <!-- 2. List of Level 2 Accordions -->
          <div
            v-if="activeRootCategory.children && activeRootCategory.children.length > 0"
            class="divide-y divide-neutral-100 dark:divide-neutral-800/80 border-t border-b border-neutral-100 dark:border-neutral-800/80"
          >
            <div
              v-for="subCat in activeRootCategory.children"
              :key="'sub-' + subCat.id"
              class="py-1"
            >
              <!-- Accordion Header (Level 2) -->
              <button
                type="button"
                class="w-full flex items-center justify-between py-3 px-1.5 text-start transition-colors cursor-pointer group"
                @click="toggleAccordion(subCat.id)"
              >
                <span
                  class="text-xs sm:text-sm transition-colors"
                  :class="expandedSubId === subCat.id ? 'font-black text-neutral-900 dark:text-white' : 'font-bold text-neutral-800 dark:text-neutral-200'"
                >
                  {{ subCat.name }}
                </span>

                <UIcon
                  name="i-lucide-chevron-down"
                  class="size-4 text-neutral-400 group-hover:text-neutral-700 dark:group-hover:text-neutral-200 transition-transform duration-200 shrink-0"
                  :class="{ 'rotate-180 text-primary': expandedSubId === subCat.id }"
                />
              </button>

              <!-- Accordion Body (Level 3 Visual Cards Grid) -->
              <div
                v-show="expandedSubId === subCat.id"
                class="pt-2 pb-4 px-1"
              >
                <!-- Case A: Subcategory has Level 3 Children -->
                <div
                  v-if="subCat.children && subCat.children.length > 0"
                  class="grid grid-cols-3 gap-2.5"
                >
                  <!-- Level 3 Visual Cards -->
                  <NuxtLink
                    v-for="deepChild in subCat.children"
                    :key="'deep-' + deepChild.id"
                    :to="`/categories/${deepChild.slug}`"
                    class="flex flex-col items-center gap-1.5 p-2 rounded-2xl bg-neutral-50/80 dark:bg-neutral-900/60 hover:bg-primary-50/30 dark:hover:bg-primary-950/20 border border-neutral-100/80 dark:border-neutral-800/70 text-center transition-all group cursor-pointer"
                  >
                    <div class="size-14 sm:size-16 rounded-2xl bg-white dark:bg-neutral-800 shadow-xs flex items-center justify-center text-primary group-hover:scale-105 transition-transform overflow-hidden border border-neutral-100 dark:border-neutral-700/60 p-1">
                      <NuxtImg
                        v-if="deepChild.image"
                        :src="deepChild.image"
                        :alt="deepChild.name"
                        class="size-full object-contain"
                        loading="lazy"
                      />
                      <UIcon
                        v-else
                        :name="iconMap[deepChild.slug] || deepChild.icon || 'i-lucide-sparkles'"
                        class="size-6 text-primary"
                      />
                    </div>
                    <span class="text-[10px] sm:text-[11px] font-bold text-neutral-800 dark:text-neutral-200 line-clamp-2 leading-tight">
                      {{ deepChild.name }}
                    </span>
                  </NuxtLink>

                  <!-- "همه کالاها" Card -->
                  <NuxtLink
                    :to="`/categories/${subCat.slug}`"
                    class="flex flex-col items-center gap-1.5 p-2 rounded-2xl bg-neutral-50/80 dark:bg-neutral-900/60 hover:bg-primary-50/30 dark:hover:bg-primary-950/20 border border-neutral-100/80 dark:border-neutral-800/70 text-center transition-all group cursor-pointer"
                  >
                    <div class="size-14 sm:size-16 rounded-2xl bg-white dark:bg-neutral-800 shadow-xs flex items-center justify-center text-neutral-500 group-hover:text-primary group-hover:scale-105 transition-transform border border-neutral-100 dark:border-neutral-700/60">
                      <UIcon
                        name="i-lucide-layout-grid"
                        class="size-6"
                      />
                    </div>
                    <span class="text-[10px] sm:text-[11px] font-bold text-neutral-800 dark:text-neutral-200 line-clamp-2 leading-tight">
                      همه کالاها
                    </span>
                  </NuxtLink>
                </div>

                <!-- Case B: Subcategory is a leaf (Direct Level 2) -->
                <div
                  v-else
                  class="grid grid-cols-3 gap-2.5"
                >
                  <!-- Main Category Card -->
                  <NuxtLink
                    :to="`/categories/${subCat.slug}`"
                    class="flex flex-col items-center gap-1.5 p-2 rounded-2xl bg-neutral-50/80 dark:bg-neutral-900/60 hover:bg-primary-50/30 dark:hover:bg-primary-950/20 border border-neutral-100/80 dark:border-neutral-800/70 text-center transition-all group cursor-pointer"
                  >
                    <div class="size-14 sm:size-16 rounded-2xl bg-white dark:bg-neutral-800 shadow-xs flex items-center justify-center text-primary group-hover:scale-105 transition-transform overflow-hidden border border-neutral-100 dark:border-neutral-700/60 p-1">
                      <NuxtImg
                        v-if="subCat.image"
                        :src="subCat.image"
                        :alt="subCat.name"
                        class="size-full object-contain"
                        loading="lazy"
                      />
                      <UIcon
                        v-else
                        :name="iconMap[subCat.slug] || subCat.icon || 'i-lucide-sparkles'"
                        class="size-6 text-primary"
                      />
                    </div>
                    <span class="text-[10px] sm:text-[11px] font-bold text-neutral-800 dark:text-neutral-200 line-clamp-2 leading-tight">
                      {{ subCat.name }}
                    </span>
                  </NuxtLink>

                  <!-- "همه کالاها" Card -->
                  <NuxtLink
                    :to="`/categories/${subCat.slug}`"
                    class="flex flex-col items-center gap-1.5 p-2 rounded-2xl bg-neutral-50/80 dark:bg-neutral-900/60 hover:bg-primary-50/30 dark:hover:bg-primary-950/20 border border-neutral-100/80 dark:border-neutral-800/70 text-center transition-all group cursor-pointer"
                  >
                    <div class="size-14 sm:size-16 rounded-2xl bg-white dark:bg-neutral-800 shadow-xs flex items-center justify-center text-neutral-500 group-hover:text-primary group-hover:scale-105 transition-transform border border-neutral-100 dark:border-neutral-700/60">
                      <UIcon
                        name="i-lucide-layout-grid"
                        class="size-6"
                      />
                    </div>
                    <span class="text-[10px] sm:text-[11px] font-bold text-neutral-800 dark:text-neutral-200 line-clamp-2 leading-tight">
                      همه کالاها
                    </span>
                  </NuxtLink>
                </div>
              </div>
            </div>
          </div>

          <!-- Empty State -->
          <div
            v-else
            class="py-12 text-center text-xs text-neutral-400"
          >
            زیردسته‌ای برای این گروه یافت نشد.
          </div>
        </main>
      </div>
    </div>

    <!-- ========================================== -->
    <!-- 2. DESKTOP RICH GRID VIEW (lg+) -->
    <!-- ========================================== -->
    <div class="hidden lg:flex flex-col gap-8 py-6 pb-16">
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
          محصولات تخصصی بر اساس کاربرد و گروه تفکیک شده‌اند؛ برای دسترسی سریع گروه مورد نظر خود را انتخاب نمایید.
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
  </div>
</template>
