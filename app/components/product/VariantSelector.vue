<script setup lang="ts">
import type { AttributeMatrixItem, ProductDetailItem, ProductVariantItem } from '~/stores/catalog'

const props = defineProps<{
  product: ProductDetailItem
  modelValue?: ProductVariantItem | null
}>()

const emit = defineEmits<{
  (e: 'update:modelValue', variant: ProductVariantItem | null): void
  (e: 'colorChange', colorValue: string): void
}>()

const { formatPrice, formatDiscount } = usePersian()
const toast = useToast()

// Selected attribute values mapped by attribute ID: { [attrId: number]: valueId }
const selectedAttributes = ref<Record<number, number>>({})

// Initialize default selection with the first active in-stock variant, or first variant
onMounted(() => {
  const defaultVariant = props.product.variants.find(v => v.is_in_stock) || props.product.variants[0]
  if (defaultVariant && defaultVariant.attributes) {
    for (const attr of defaultVariant.attributes) {
      selectedAttributes.value[attr.attribute_id] = attr.value_id
    }
    emit('update:modelValue', defaultVariant)
  }
})

// Find active matching variant based on currently selected attribute values
const currentVariant = computed<ProductVariantItem | null>(() => {
  if (!props.product.variants.length) return null

  // If product has only 1 variant without attributes
  if (props.product.variants.length === 1 && (!props.product.variants_matrix || props.product.variants_matrix.length === 0)) {
    return props.product.variants[0] || null
  }

  return props.product.variants.find((variant) => {
    if (!variant.attributes || variant.attributes.length === 0) return false
    return variant.attributes.every((attr) => {
      const selectedValId = selectedAttributes.value[attr.attribute_id]
      return selectedValId === attr.value_id
    })
  }) || null
})

// Update modelValue when matched variant changes
watch(currentVariant, (newVar) => {
  emit('update:modelValue', newVar)
})

/**
 * Check if a candidate attribute value is part of an existing valid variant combination.
 */
function isCombinationValid(attrId: number, valueId: number): boolean {
  // Test combination: current selections + candidate value
  const candidateSelection = { ...selectedAttributes.value, [attrId]: valueId }

  return props.product.variants.some((variant) => {
    if (!variant.attributes) return false
    // Check if variant satisfies all attributes in candidateSelection
    return Object.entries(candidateSelection).every(([key, val]) => {
      const targetAttrId = Number(key)
      return variant.attributes?.some(a => a.attribute_id === targetAttrId && a.value_id === val)
    })
  })
}

/**
 * Check if candidate combination is currently in stock.
 */
function isCombinationInStock(attrId: number, valueId: number): boolean {
  const candidateSelection = { ...selectedAttributes.value, [attrId]: valueId }

  const matchingVariant = props.product.variants.find((variant) => {
    if (!variant.attributes) return false
    return Object.entries(candidateSelection).every(([key, val]) => {
      const targetAttrId = Number(key)
      return variant.attributes?.some(a => a.attribute_id === targetAttrId && a.value_id === val)
    })
  })

  return Boolean(matchingVariant && matchingVariant.stock > 0)
}

function selectValue(attr: AttributeMatrixItem['attribute'], valueId: number, valueStr: string) {
  selectedAttributes.value[attr.id] = valueId

  if (attr.type === 'color') {
    emit('colorChange', valueStr)
  }
}

function notifyMe() {
  toast.add({
    title: 'ثبت درخواست اطلاع‌رسانی',
    description: 'به محض موجود شدن مجدد این مدل در انبار، از طریق پیامک به شما اطلاع داده خواهد شد.',
    color: 'success',
    icon: 'i-lucide-bell'
  })
}
</script>

<template>
  <div class="flex flex-col gap-5 py-3">
    <!-- Render Attribute Groups from Matrix -->
    <div
      v-for="group in product.variants_matrix"
      :key="group.attribute.id"
      class="flex flex-col gap-2.5"
    >
      <div class="flex items-center justify-between text-sm">
        <span class="font-bold text-neutral-800 dark:text-neutral-200">
          {{ group.attribute.name }}:
        </span>
        <span class="text-xs text-neutral-500">
          {{ group.values.find(v => v.id === selectedAttributes[group.attribute.id])?.label || group.values.find(v => v.id === selectedAttributes[group.attribute.id])?.value }}
        </span>
      </div>

      <!-- Color Swatches -->
      <div
        v-if="group.attribute.type === 'color'"
        class="flex flex-wrap items-center gap-3"
      >
        <button
          v-for="val in group.values"
          :key="val.id"
          type="button"
          :disabled="!isCombinationValid(group.attribute.id, val.id)"
          :title="val.label || val.value"
          class="relative size-11 rounded-full flex items-center justify-center transition-all min-h-12 min-w-12 border-2"
          :class="[
            selectedAttributes[group.attribute.id] === val.id
              ? 'border-primary ring-2 ring-primary/30 scale-105'
              : 'border-neutral-200 dark:border-neutral-700 hover:border-neutral-400',
            !isCombinationValid(group.attribute.id, val.id) ? 'opacity-30 cursor-not-allowed' : 'cursor-pointer'
          ]"
          @click="selectValue(group.attribute, val.id, val.value)"
        >
          <!-- Hex Color Circle -->
          <span
            class="size-8 rounded-full shadow-inner block"
            :style="{ backgroundColor: val.hex_code || '#cccccc' }"
          />

          <!-- Out of stock cross-line -->
          <span
            v-if="!isCombinationInStock(group.attribute.id, val.id) && isCombinationValid(group.attribute.id, val.id)"
            class="absolute inset-x-1 top-1/2 h-0.5 bg-neutral-500/80 -rotate-45 pointer-events-none"
          />
        </button>
      </div>

      <!-- Text / Number / Select Pills -->
      <div
        v-else
        class="flex flex-wrap items-center gap-2"
      >
        <button
          v-for="val in group.values"
          :key="val.id"
          type="button"
          :disabled="!isCombinationValid(group.attribute.id, val.id)"
          class="min-h-11 px-4 py-2 rounded-xl text-sm font-medium transition-all flex items-center gap-1.5 border"
          :class="[
            selectedAttributes[group.attribute.id] === val.id
              ? 'bg-primary text-white border-primary shadow-xs'
              : 'bg-white dark:bg-neutral-800 text-neutral-800 dark:text-neutral-200 border-neutral-200 dark:border-neutral-700 hover:bg-neutral-50 dark:hover:bg-neutral-700/50',
            !isCombinationValid(group.attribute.id, val.id) ? 'opacity-30 cursor-not-allowed line-through' : 'cursor-pointer',
            !isCombinationInStock(group.attribute.id, val.id) && isCombinationValid(group.attribute.id, val.id) ? 'border-dashed border-neutral-400 text-neutral-400' : ''
          ]"
          @click="selectValue(group.attribute, val.id, val.value)"
        >
          <span>{{ val.label || val.value }}</span>
          <span
            v-if="!isCombinationInStock(group.attribute.id, val.id) && isCombinationValid(group.attribute.id, val.id)"
            class="text-[10px] text-neutral-400"
          >
            (ناموجود)
          </span>
        </button>
      </div>
    </div>

    <!-- Active Variant Pricing & Stock Status Display -->
    <div
      v-if="currentVariant"
      class="mt-2 p-4 rounded-2xl bg-neutral-50 dark:bg-neutral-800/60 border border-neutral-200/80 dark:border-neutral-700/80 flex flex-col gap-3"
    >
      <div class="flex items-center justify-between">
        <!-- Price -->
        <div class="flex items-baseline gap-2">
          <span class="text-2xl font-black text-neutral-900 dark:text-white">
            {{ formatPrice(currentVariant.price) }}
          </span>
          <span
            v-if="currentVariant.compare_at_price && currentVariant.compare_at_price > currentVariant.price"
            class="text-sm text-neutral-400 line-through"
          >
            {{ formatPrice(currentVariant.compare_at_price) }}
          </span>
          <UBadge
            v-if="currentVariant.compare_at_price && currentVariant.compare_at_price > currentVariant.price"
            color="error"
            variant="solid"
            size="sm"
            class="rounded-full font-bold"
          >
            {{ formatDiscount(currentVariant.price, currentVariant.compare_at_price) }}
          </UBadge>
        </div>

        <!-- Stock Status Badge -->
        <div>
          <UBadge
            v-if="currentVariant.stock > 5"
            color="success"
            variant="subtle"
            size="md"
            icon="i-lucide-check-circle"
          >
            موجود در انبار
          </UBadge>
          <UBadge
            v-else-if="currentVariant.stock > 0"
            color="warning"
            variant="subtle"
            size="md"
            icon="i-lucide-alert-triangle"
          >
            تنها {{ currentVariant.stock }} عدد باقی‌مانده
          </UBadge>
          <UBadge
            v-else
            color="neutral"
            variant="solid"
            size="md"
            icon="i-lucide-x-circle"
          >
            اتمام موجودی
          </UBadge>
        </div>
      </div>

      <!-- SKU & Meta details -->
      <div class="flex items-center gap-4 text-xs text-neutral-400 pt-2 border-t border-neutral-200/60 dark:border-neutral-700/60">
        <span>کد کالا (SKU): <strong class="text-neutral-600 dark:text-neutral-300 font-mono">{{ currentVariant.sku }}</strong></span>
        <span v-if="currentVariant.weight">وزن: {{ currentVariant.weight }} گرم</span>
      </div>

      <!-- Notify Me CTA when Out of Stock -->
      <div
        v-if="currentVariant.stock === 0"
        class="pt-1"
      >
        <UButton
          color="neutral"
          variant="outline"
          icon="i-lucide-bell"
          block
          size="lg"
          class="min-h-12 text-sm font-bold"
          @click="notifyMe"
        >
          موجود شد به من اطلاع بده
        </UButton>
      </div>
    </div>
  </div>
</template>
