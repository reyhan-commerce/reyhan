<script setup lang="ts">
const catalogStore = useCatalogStore()

const availableBrands = [
  { slug: 'loreal', name: 'لورآل (L\'Oréal)' },
  { slug: 'nivea', name: 'نیوآ (Nivea)' },
  { slug: 'my', name: 'مای (My)' },
  { slug: 'cinere', name: 'سینره (Cinere)' },
  { slug: 'isadora', name: 'ایزادورا (IsaDora)' }
]

function toggleBrand(slug: string) {
  const current = [...catalogStore.filters.brand]
  const index = current.indexOf(slug)
  if (index > -1) {
    current.splice(index, 1)
  } else {
    current.push(slug)
  }
  catalogStore.setFilter('brand', current)
}

function toggleInStock(val: boolean) {
  catalogStore.setFilter('in_stock', val)
}

function selectCategory(slug: string) {
  catalogStore.setFilter('category', catalogStore.filters.category === slug ? '' : slug)
}
</script>

<template>
  <div class="flex flex-col gap-5">
    <!-- Header / Reset -->
    <div class="flex items-center justify-between pb-3 border-b border-neutral-200 dark:border-neutral-800">
      <div class="flex items-center gap-2 font-bold text-base text-neutral-900 dark:text-white">
        <UIcon
          name="i-lucide-filter"
          class="size-5 text-primary"
        />
        <span>فیلترهای کاتالوگ</span>
      </div>

      <UButton
        color="neutral"
        variant="ghost"
        size="xs"
        class="text-neutral-500 hover:text-error"
        @click="catalogStore.resetFilters"
      >
        پاک‌کردن فیلترها
      </UButton>
    </div>

    <!-- In Stock Only Switch -->
    <div class="flex items-center justify-between p-3.5 rounded-xl bg-neutral-50 dark:bg-neutral-800/50 border border-neutral-200/60 dark:border-neutral-700/60">
      <span class="text-sm font-medium text-neutral-800 dark:text-neutral-200">
        فقط کالاهای موجود
      </span>
      <USwitch
        :model-value="catalogStore.filters.in_stock"
        @update:model-value="toggleInStock"
      />
    </div>

    <!-- Category Filter -->
    <div
      v-if="catalogStore.categoryTree.length > 0"
      class="flex flex-col gap-2.5"
    >
      <h4 class="font-bold text-sm text-neutral-900 dark:text-white">
        دسته‌بندی‌ها
      </h4>
      <div class="flex flex-col gap-1 max-h-60 overflow-y-auto pe-1">
        <div
          v-for="cat in catalogStore.categoryTree"
          :key="cat.id"
          class="flex flex-col"
        >
          <button
            type="button"
            class="flex items-center justify-between py-2 px-2.5 rounded-lg text-sm text-start transition-colors min-h-10"
            :class="catalogStore.filters.category === cat.slug ? 'bg-primary/10 text-primary font-bold' : 'text-neutral-700 dark:text-neutral-300 hover:bg-neutral-100 dark:hover:bg-neutral-800'"
            @click="selectCategory(cat.slug)"
          >
            <span>{{ cat.name }}</span>
            <UIcon
              v-if="catalogStore.filters.category === cat.slug"
              name="i-lucide-check"
              class="size-4"
            />
          </button>

          <!-- Subcategories -->
          <div
            v-if="cat.children && cat.children.length > 0"
            class="ms-3 ps-2 border-s border-neutral-200 dark:border-neutral-800 flex flex-col gap-0.5 mt-0.5"
          >
            <button
              v-for="subCat in cat.children"
              :key="subCat.id"
              type="button"
              class="py-1.5 px-2 rounded-md text-xs text-start transition-colors min-h-8"
              :class="catalogStore.filters.category === subCat.slug ? 'bg-primary/10 text-primary font-bold' : 'text-neutral-600 dark:text-neutral-400 hover:bg-neutral-100 dark:hover:bg-neutral-800'"
              @click="selectCategory(subCat.slug)"
            >
              {{ subCat.name }}
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Brand Filter -->
    <div class="flex flex-col gap-2.5">
      <h4 class="font-bold text-sm text-neutral-900 dark:text-white">
        برندها
      </h4>
      <div class="flex flex-col gap-2 max-h-52 overflow-y-auto pe-1">
        <label
          v-for="brand in availableBrands"
          :key="brand.slug"
          class="flex items-center gap-2.5 py-1.5 px-1 cursor-pointer min-h-10 select-none"
        >
          <UCheckbox
            :model-value="catalogStore.filters.brand.includes(brand.slug)"
            @update:model-value="toggleBrand(brand.slug)"
          />
          <span class="text-sm text-neutral-700 dark:text-neutral-300">
            {{ brand.name }}
          </span>
        </label>
      </div>
    </div>
  </div>
</template>
