<script setup lang="ts">
const catalogStore = useCatalogStore()
const isOpen = ref(false)
const selectedCategoryIndex = ref(0)

const iconMap: Record<string, string> = {
  skincare: 'i-lucide-sparkles',
  makeup: 'i-lucide-heart',
  haircare: 'i-lucide-scissors',
  fragrance: 'i-lucide-flame'
}

const activeCategory = computed(() => {
  if (!catalogStore.categoryTree || catalogStore.categoryTree.length === 0) return null
  return catalogStore.categoryTree[selectedCategoryIndex.value] || catalogStore.categoryTree[0]
})

function closeMenu() {
  isOpen.value = false
}

// Close on outside click
const menuContainerRef = ref<HTMLElement | null>(null)
onClickOutside(menuContainerRef, () => {
  isOpen.value = false
})
</script>

<template>
  <div
    ref="menuContainerRef"
    class="relative"
  >
    <!-- Trigger Button -->
    <button
      type="button"
      class="flex items-center gap-2 px-3.5 py-2 rounded-xl text-sm font-bold transition-all duration-200"
      :class="isOpen ? 'bg-primary text-white shadow-md shadow-primary/20' : 'text-neutral-700 dark:text-neutral-200 hover:bg-neutral-100 dark:hover:bg-neutral-800'"
      @click="isOpen = !isOpen"
    >
      <UIcon
        name="i-lucide-layout-grid"
        class="size-4.5"
      />
      <span>دسته‌بندی کالاها</span>
      <UIcon
        name="i-lucide-chevron-down"
        class="size-4 transition-transform duration-200"
        :class="{ 'rotate-180': isOpen }"
      />
    </button>

    <!-- Mega Menu Dropdown -->
    <Transition
      enter-active-class="transition duration-200 ease-out"
      enter-from-class="opacity-0 translate-y-2 scale-95"
      enter-to-class="opacity-100 translate-y-0 scale-100"
      leave-active-class="transition duration-150 ease-in"
      leave-from-class="opacity-100 translate-y-0 scale-100"
      leave-to-class="opacity-0 translate-y-2 scale-95"
    >
      <div
        v-if="isOpen"
        class="absolute right-0 top-full mt-2 w-[680px] rounded-3xl bg-white dark:bg-neutral-900 border border-neutral-200/80 dark:border-neutral-800 shadow-2xl z-50 overflow-hidden flex"
      >
        <!-- Categories List (Right Sidebar in RTL) -->
        <div class="w-56 bg-neutral-50 dark:bg-neutral-950/60 border-l border-neutral-100 dark:border-neutral-800/80 p-3 flex flex-col gap-1 shrink-0">
          <button
            v-for="(category, idx) in catalogStore.categoryTree"
            :key="category.id"
            type="button"
            class="w-full flex items-center justify-between px-3.5 py-2.5 rounded-xl text-xs font-bold transition-all text-right"
            :class="selectedCategoryIndex === idx ? 'bg-white dark:bg-neutral-800 text-primary shadow-xs' : 'text-neutral-600 dark:text-neutral-300 hover:bg-white/60 dark:hover:bg-neutral-800/50'"
            @mouseenter="selectedCategoryIndex = idx"
          >
            <div class="flex items-center gap-2.5">
              <UIcon
                :name="iconMap[category.slug] || category.icon || 'i-lucide-tag'"
                class="size-4.5 shrink-0"
                :class="selectedCategoryIndex === idx ? 'text-primary' : 'text-neutral-400'"
              />
              <span class="truncate">{{ category.name }}</span>
            </div>
            <UIcon
              name="i-lucide-chevron-left"
              class="size-3.5 text-neutral-400"
            />
          </button>

          <div class="mt-auto pt-3 border-t border-neutral-200/60 dark:border-neutral-800">
            <NuxtLink
              to="/categories"
              class="w-full flex items-center justify-center gap-1.5 py-2 text-xs font-bold text-primary hover:underline"
              @click="closeMenu"
            >
              <span>مشاهده همه دسته‌ها</span>
              <UIcon
                name="i-lucide-arrow-left"
                class="size-3.5"
              />
            </NuxtLink>
          </div>
        </div>

        <!-- Subcategories Details Area (Left in RTL) -->
        <div class="flex-1 p-5 flex flex-col justify-between">
          <div v-if="activeCategory">
            <!-- Active Category Header -->
            <div class="flex items-center justify-between pb-3.5 mb-4 border-b border-neutral-100 dark:border-neutral-800">
              <div class="flex items-center gap-2.5">
                <div class="size-8 rounded-lg bg-primary/10 text-primary flex items-center justify-center">
                  <UIcon
                    :name="iconMap[activeCategory.slug] || activeCategory.icon || 'i-lucide-tag'"
                    class="size-4.5"
                  />
                </div>
                <div>
                  <h4 class="font-black text-sm text-neutral-900 dark:text-white">
                    {{ activeCategory.name }}
                  </h4>
                  <p class="text-[11px] text-neutral-400">
                    محصولات برگزیده و زیرمجموعه‌ها
                  </p>
                </div>
              </div>

              <NuxtLink
                :to="`/categories/${activeCategory.slug}`"
                class="text-xs font-bold text-primary hover:underline flex items-center gap-1"
                @click="closeMenu"
              >
                <span>مشاهده همه</span>
                <UIcon
                  name="i-lucide-chevron-left"
                  class="size-3.5"
                />
              </NuxtLink>
            </div>

            <!-- Subcategories Grid -->
            <div
              v-if="activeCategory.children && activeCategory.children.length > 0"
              class="grid grid-cols-2 gap-2.5"
            >
              <NuxtLink
                v-for="sub in activeCategory.children"
                :key="sub.id"
                :to="`/categories/${sub.slug}`"
                class="flex items-center justify-between p-2.5 rounded-xl hover:bg-neutral-100 dark:hover:bg-neutral-800/70 transition-colors group"
                @click="closeMenu"
              >
                <span class="text-xs font-medium text-neutral-700 dark:text-neutral-200 group-hover:text-primary transition-colors">
                  {{ sub.name }}
                </span>
                <UIcon
                  name="i-lucide-arrow-left"
                  class="size-3 text-neutral-400 group-hover:text-primary transition-colors opacity-0 group-hover:opacity-100"
                />
              </NuxtLink>
            </div>

            <!-- Fallback if no subcategories -->
            <div
              v-else
              class="flex flex-col items-center justify-center py-10 text-center"
            >
              <p class="text-xs text-neutral-400 mb-3">
                کالاهای اختصاصی گروه {{ activeCategory.name }}
              </p>
              <NuxtLink
                :to="`/categories/${activeCategory.slug}`"
                class="px-4 py-2 rounded-xl bg-primary/10 text-primary text-xs font-bold hover:bg-primary hover:text-white transition-colors"
                @click="closeMenu"
              >
                مشاهده محصولات این دسته
              </NuxtLink>
            </div>
          </div>
        </div>
      </div>
    </Transition>
  </div>
</template>
