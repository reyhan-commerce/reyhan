<script setup lang="ts">
import type { FilterMetadataResponse } from '~/types/product'
import { useCatalogService } from '~/services/catalogService'

const emit = defineEmits<{
  (e: 'applied'): void
}>()

const catalogStore = useCatalogStore()
const catalogService = useCatalogService()
const { formatPrice, toPersianDigits, toEnglishDigits } = usePersian()

const metadata = ref<FilterMetadataResponse | null>(null)
const isLoadingMetadata = ref(false)
const brandSearch = ref('')

function safeDecode(val: string): string {
  try {
    return decodeURIComponent(val)
  } catch {
    return val
  }
}

const filteredBrands = computed(() => {
  if (!metadata.value?.brands) return []
  if (!brandSearch.value.trim()) return metadata.value.brands
  const q = brandSearch.value.trim().toLowerCase()
  return metadata.value.brands.filter(b =>
    b.name.toLowerCase().includes(q) || (b.name_en && b.name_en.toLowerCase().includes(q))
  )
})

const minBound = computed(() => metadata.value?.price_bounds?.min ?? 0)
const maxBound = computed(() => metadata.value?.price_bounds?.max ?? 100000000)

// Helper: Format with Persian thousands separators
function formatDisplayPrice(val: number | null | undefined): string {
  if (val === null || val === undefined) return ''
  return toPersianDigits(Number(val).toLocaleString('en-US'))
}

// Helper: Parse string with commas/Persian digits to number
function parseInputPrice(val: string): number | null {
  const clean = toEnglishDigits(val).replace(/[^\d]/g, '')
  if (!clean) return null
  const num = Number(clean)
  return Number.isNaN(num) ? null : num
}

// Local price state
const localMinPrice = ref<number | null>(catalogStore.filters.min_price)
const localMaxPrice = ref<number | null>(catalogStore.filters.max_price)
const displayMinPrice = ref<string>(formatDisplayPrice(localMinPrice.value))
const displayMaxPrice = ref<string>(formatDisplayPrice(localMaxPrice.value))

// Range slider model: [min, max]
const priceRange = ref<number[]>([
  catalogStore.filters.min_price ?? minBound.value,
  catalogStore.filters.max_price ?? maxBound.value
])

// Keep slider and text inputs synchronized with store changes (e.g. from URL query or reset)
watch(() => [catalogStore.filters.min_price, catalogStore.filters.max_price], ([newMin, newMax]) => {
  localMinPrice.value = newMin ?? null
  localMaxPrice.value = newMax ?? null
  displayMinPrice.value = formatDisplayPrice(newMin)
  displayMaxPrice.value = formatDisplayPrice(newMax)
  priceRange.value = [
    newMin ?? minBound.value,
    newMax ?? maxBound.value
  ]
})

// Update slider bounds when metadata loads
watch(metadata, (newMeta) => {
  if (newMeta?.price_bounds) {
    if (catalogStore.filters.min_price === null && catalogStore.filters.max_price === null) {
      priceRange.value = [newMeta.price_bounds.min, newMeta.price_bounds.max]
    }
  }
})

async function loadMetadata() {
  isLoadingMetadata.value = true
  try {
    metadata.value = await catalogService.getFilterMetadata(catalogStore.filters.category)
  } catch {
    // ignore
  } finally {
    isLoadingMetadata.value = false
  }
}

onMounted(() => {
  loadMetadata()
})

watch(() => catalogStore.filters.category, () => {
  loadMetadata()
})

function isBrandSelected(slug: string): boolean {
  return catalogStore.filters.brand.some(b => b === slug || safeDecode(b) === slug)
}

function toggleBrand(slug: string) {
  const current = [...catalogStore.filters.brand]
  const index = current.findIndex(b => b === slug || safeDecode(b) === slug)
  if (index > -1) {
    current.splice(index, 1)
  } else {
    current.push(slug)
  }
  catalogStore.setFilter('brand', current)
  emit('applied')
}

function selectAllBrands() {
  catalogStore.setFilter('brand', [])
  emit('applied')
}

function isAttributeEmpty(attrSlug: string): boolean {
  const arr = catalogStore.filters.attributes[attrSlug]
  return !arr || arr.length === 0
}

function isAttributeSelected(attrSlug: string, value: string): boolean {
  const vals = catalogStore.filters.attributes[attrSlug]
  if (!vals || vals.length === 0) return false
  return vals.some(v => v === value || safeDecode(v) === value)
}

function toggleAttributeValue(attrSlug: string, value: string) {
  const currentAttrs = { ...catalogStore.filters.attributes }
  const currentVals = [...(currentAttrs[attrSlug] || [])]
  const index = currentVals.findIndex(v => v === value || safeDecode(v) === value)

  if (index > -1) {
    currentVals.splice(index, 1)
  } else {
    currentVals.push(value)
  }

  if (currentVals.length > 0) {
    currentAttrs[attrSlug] = currentVals
  } else {
    delete currentAttrs[attrSlug]
  }

  catalogStore.setFilter('attributes', currentAttrs)
  emit('applied')
}

function selectAllAttributeValues(attrSlug: string) {
  const currentAttrs = { ...catalogStore.filters.attributes }
  delete currentAttrs[attrSlug]
  catalogStore.setFilter('attributes', currentAttrs)
  emit('applied')
}

// Range slider drag handler - instantaneous thumb movement
function onSliderDrag(val: number[] | undefined) {
  if (Array.isArray(val) && val.length === 2) {
    priceRange.value = val
    localMinPrice.value = val[0] ?? null
    localMaxPrice.value = val[1] ?? null
    displayMinPrice.value = formatDisplayPrice(val[0])
    displayMaxPrice.value = formatDisplayPrice(val[1])
  }
}

// Text input handlers with 3-digit comma masking
function onMinPriceInput(val: string | number) {
  const num = parseInputPrice(String(val))
  localMinPrice.value = num
  displayMinPrice.value = formatDisplayPrice(num)
  priceRange.value = [num ?? minBound.value, priceRange.value[1] ?? maxBound.value]
}

function onMaxPriceInput(val: string | number) {
  const num = parseInputPrice(String(val))
  localMaxPrice.value = num
  displayMaxPrice.value = formatDisplayPrice(num)
  priceRange.value = [priceRange.value[0] ?? minBound.value, num ?? maxBound.value]
}

function applyPriceFilter() {
  catalogStore.filters.min_price = localMinPrice.value
  catalogStore.setFilter('max_price', localMaxPrice.value)
  emit('applied')
}

function toggleInStock(val: boolean) {
  catalogStore.setFilter('in_stock', val)
  emit('applied')
}

function toggleHasDiscount(val: boolean) {
  catalogStore.setFilter('has_discount', val)
  emit('applied')
}

function selectCategory(slug: string) {
  catalogStore.setFilter('category', catalogStore.filters.category === slug ? '' : slug)
  emit('applied')
}

function onResetAllFilters() {
  catalogStore.resetFilters()
  emit('applied')
}

// Find active category display name
const activeCategoryName = computed(() => {
  if (!catalogStore.filters.category) return null
  function findName(cats: typeof catalogStore.categoryTree): string | null {
    for (const c of cats) {
      if (c.slug === catalogStore.filters.category) return c.name
      if (c.children?.length) {
        const found = findName(c.children)
        if (found) return found
      }
    }
    return null
  }
  return findName(catalogStore.categoryTree) || catalogStore.filters.category
})
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
        @click="onResetAllFilters"
      >
        پاک‌کردن فیلترها
      </UButton>
    </div>

    <!-- Switches Container -->
    <div class="flex flex-col gap-2.5">
      <!-- In Stock Only Switch -->
      <div class="flex items-center justify-between p-3 rounded-xl bg-neutral-50 dark:bg-neutral-800/50 border border-neutral-200/60 dark:border-neutral-700/60">
        <span class="text-xs sm:text-sm font-medium text-neutral-800 dark:text-neutral-200">
          فقط کالاهای موجود
        </span>
        <USwitch
          :model-value="catalogStore.filters.in_stock"
          @update:model-value="toggleInStock"
        />
      </div>

      <!-- Has Discount Switch -->
      <div class="flex items-center justify-between p-3 rounded-xl bg-neutral-50 dark:bg-neutral-800/50 border border-neutral-200/60 dark:border-neutral-700/60">
        <span class="text-xs sm:text-sm font-medium text-neutral-800 dark:text-neutral-200">
          فقط کالاهای تخفیف‌دار
        </span>
        <USwitch
          :model-value="catalogStore.filters.has_discount"
          @update:model-value="toggleHasDiscount"
        />
      </div>
    </div>

    <!-- Price Range Filter (Reactive Range Slider + 3-Digit Masked Inputs) -->
    <div class="flex flex-col gap-3.5 p-3.5 rounded-2xl bg-neutral-50/70 dark:bg-neutral-800/40 border border-neutral-200/60 dark:border-neutral-700/60">
      <div class="flex items-center justify-between">
        <h4 class="font-bold text-xs sm:text-sm text-neutral-900 dark:text-white">
          محدوده قیمت (تومان)
        </h4>
      </div>

      <!-- Nuxt UI Range Slider with dynamic dragging -->
      <div class="px-2 py-2">
        <USlider
          v-model="priceRange"
          :min="minBound"
          :max="maxBound"
          :step="50000"
          color="primary"
          size="sm"
          @update:model-value="onSliderDrag"
          @change="applyPriceFilter"
        />
      </div>

      <!-- Masked Text inputs with 3-digit comma formatting -->
      <div class="grid grid-cols-2 gap-2 text-xs">
        <div>
          <label class="text-[10px] text-neutral-500 block mb-1">از قیمت (تومان)</label>
          <UInput
            :model-value="displayMinPrice"
            type="text"
            placeholder="مثلاً ۱۰۰,۰۰۰"
            size="xs"
            class="w-full text-center font-mono font-medium"
            @update:model-value="onMinPriceInput"
            @blur="applyPriceFilter"
          />
        </div>
        <div>
          <label class="text-[10px] text-neutral-500 block mb-1">تا قیمت (تومان)</label>
          <UInput
            :model-value="displayMaxPrice"
            type="text"
            placeholder="مثلاً ۲,۰۰۰,۰۰۰"
            size="xs"
            class="w-full text-center font-mono font-medium"
            @update:model-value="onMaxPriceInput"
            @blur="applyPriceFilter"
          />
        </div>
      </div>

      <UButton
        color="neutral"
        variant="outline"
        size="xs"
        class="w-full justify-center font-bold"
        @click="applyPriceFilter"
      >
        اعمال قیمت
      </UButton>
    </div>

    <!-- Dynamic Brand Filter -->
    <div
      v-if="metadata && metadata.brands && metadata.brands.length > 0"
      class="flex flex-col gap-2.5"
    >
      <div class="flex items-center justify-between">
        <h4 class="font-bold text-sm text-neutral-900 dark:text-white">
          برندها
        </h4>
        <span
          v-if="catalogStore.filters.brand.length > 0"
          class="text-[11px] text-primary cursor-pointer hover:underline"
          @click="selectAllBrands"
        >
          پاک‌کردن انتخاب
        </span>
      </div>

      <!-- Quick Brand Search if many brands -->
      <div
        v-if="metadata.brands.length > 6"
        class="pb-1"
      >
        <UInput
          v-model="brandSearch"
          icon="i-lucide-search"
          placeholder="جستجوی برند..."
          size="xs"
          class="w-full"
        />
      </div>

      <div class="flex flex-col gap-1 max-h-52 overflow-y-auto pe-1">
        <!-- 'All Brands' Checkbox -->
        <UCheckbox
          :model-value="catalogStore.filters.brand.length === 0"
          label="همه برندها"
          color="primary"
          class="w-full cursor-pointer py-1 px-1 rounded-lg hover:bg-neutral-50 dark:hover:bg-neutral-800/40 transition-colors"
          @update:model-value="selectAllBrands"
        />

        <!-- Brand items with native Checkbox -->
        <UCheckbox
          v-for="brand in filteredBrands"
          :key="brand.slug"
          :model-value="isBrandSelected(brand.slug)"
          :label="brand.name_en ? `${brand.name} (${brand.name_en})` : brand.name"
          color="primary"
          class="w-full cursor-pointer py-1 px-1 rounded-lg hover:bg-neutral-50 dark:hover:bg-neutral-800/40 transition-colors"
          @update:model-value="toggleBrand(brand.slug)"
        />
      </div>
    </div>

    <!-- Category Filter -->
    <div
      v-if="catalogStore.categoryTree.length > 0"
      class="flex flex-col gap-2.5"
    >
      <div class="flex items-center justify-between">
        <h4 class="font-bold text-sm text-neutral-900 dark:text-white">
          دسته‌بندی‌ها
        </h4>
        <span
          v-if="catalogStore.filters.category"
          class="text-[11px] text-primary cursor-pointer hover:underline"
          @click="selectCategory('')"
        >
          پاک‌کردن
        </span>
      </div>

      <!-- Active Category Indicator Badge -->
      <div
        v-if="activeCategoryName && catalogStore.filters.category"
        class="flex items-center justify-between p-2 rounded-xl bg-primary/10 border border-primary/20 text-xs font-bold text-primary"
      >
        <div class="flex items-center gap-1.5 truncate">
          <UIcon name="i-lucide-check-circle" class="size-4 shrink-0" />
          <span class="truncate">{{ activeCategoryName }}</span>
        </div>
        <button
          type="button"
          class="text-[11px] hover:underline shrink-0 text-neutral-500 hover:text-error"
          @click="selectCategory('')"
        >
          حذف
        </button>
      </div>

      <div class="flex flex-col gap-1 max-h-56 overflow-y-auto pe-1">
        <!-- All Categories Option -->
        <button
          type="button"
          class="flex items-center justify-between py-1.5 px-2.5 rounded-lg text-xs sm:text-sm text-start transition-colors min-h-9"
          :class="!catalogStore.filters.category ? 'bg-primary/10 text-primary font-bold' : 'text-neutral-700 dark:text-neutral-300 hover:bg-neutral-100 dark:hover:bg-neutral-800'"
          @click="selectCategory('')"
        >
          <span>همه دسته‌بندی‌ها</span>
          <UIcon
            v-if="!catalogStore.filters.category"
            name="i-lucide-check"
            class="size-4"
          />
        </button>

        <div
          v-for="cat in catalogStore.categoryTree"
          :key="cat.id"
          class="flex flex-col"
        >
          <button
            type="button"
            class="flex items-center justify-between py-1.5 px-2.5 rounded-lg text-xs sm:text-sm text-start transition-colors min-h-9"
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
              class="py-1 px-2 rounded-md text-xs text-start transition-colors min-h-7 flex items-center justify-between"
              :class="catalogStore.filters.category === subCat.slug ? 'bg-primary/10 text-primary font-bold' : 'text-neutral-600 dark:text-neutral-400 hover:bg-neutral-100 dark:hover:bg-neutral-800'"
              @click="selectCategory(subCat.slug)"
            >
              <span>{{ subCat.name }}</span>
              <UIcon
                v-if="catalogStore.filters.category === subCat.slug"
                name="i-lucide-check"
                class="size-3.5"
              />
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Dynamic Attribute Filters -->
    <template v-if="metadata && metadata.attributes && metadata.attributes.length > 0">
      <div
        v-for="attr in metadata.attributes"
        :key="attr.id"
        class="flex flex-col gap-2.5 pt-2 border-t border-neutral-200 dark:border-neutral-800"
      >
        <div class="flex items-center justify-between">
          <h4 class="font-bold text-xs sm:text-sm text-neutral-900 dark:text-white">
            {{ attr.name }}
          </h4>
          <span
            v-if="!isAttributeEmpty(attr.slug)"
            class="text-[11px] text-primary cursor-pointer hover:underline"
            @click="selectAllAttributeValues(attr.slug)"
          >
            پاک‌کردن
          </span>
        </div>

        <!-- Color attribute swatches with 'All' toggle -->
        <div
          v-if="attr.type === 'color'"
          class="flex flex-wrap gap-2"
        >
          <!-- All colors option -->
          <button
            type="button"
            class="flex items-center gap-1.5 p-1.5 rounded-lg border text-xs transition-all cursor-pointer"
            :class="[
              isAttributeEmpty(attr.slug)
                ? 'border-primary ring-2 ring-primary/20 bg-primary/5 font-bold text-primary'
                : 'border-neutral-200 dark:border-neutral-700 hover:border-neutral-300 text-neutral-600 dark:text-neutral-400'
            ]"
            @click="selectAllAttributeValues(attr.slug)"
          >
            <UIcon name="i-lucide-palette" class="size-3.5" />
            <span class="text-[11px]">همه رنگ‌ها</span>
          </button>

          <button
            v-for="val in attr.values"
            :key="val.id"
            type="button"
            class="flex items-center gap-1.5 p-1.5 rounded-lg border text-xs transition-all cursor-pointer"
            :class="[
              isAttributeSelected(attr.slug, val.value)
                ? 'border-primary ring-2 ring-primary/20 bg-primary/5 font-bold text-primary'
                : 'border-neutral-200 dark:border-neutral-700 hover:border-neutral-300 text-neutral-700 dark:text-neutral-300'
            ]"
            @click="toggleAttributeValue(attr.slug, val.value)"
          >
            <span
              class="size-4 rounded-full border border-neutral-300 dark:border-neutral-600 shadow-2xs shrink-0"
              :style="{ backgroundColor: val.hex_code || val.value }"
            />
            <span class="text-[11px]">
              {{ val.label || val.value }}
            </span>
          </button>
        </div>

        <!-- Regular select / text attribute checkbox list -->
        <div
          v-else
          class="flex flex-col gap-1 max-h-48 overflow-y-auto pe-1"
        >
          <!-- 'All' Checkbox for this attribute -->
          <UCheckbox
            :model-value="isAttributeEmpty(attr.slug)"
            label="همه موارد"
            color="primary"
            class="w-full cursor-pointer py-1 px-1 rounded-lg hover:bg-neutral-50 dark:hover:bg-neutral-800/40 transition-colors"
            @update:model-value="selectAllAttributeValues(attr.slug)"
          />

          <!-- Native Checkbox for each value -->
          <UCheckbox
            v-for="val in attr.values"
            :key="val.id"
            :model-value="isAttributeSelected(attr.slug, val.value)"
            :label="val.label || val.value"
            color="primary"
            class="w-full cursor-pointer py-1 px-1 rounded-lg hover:bg-neutral-50 dark:hover:bg-neutral-800/40 transition-colors"
            @update:model-value="toggleAttributeValue(attr.slug, val.value)"
          />
        </div>
      </div>
    </template>
  </div>
</template>
