import { defineStore } from 'pinia'
import type { CompareResponse } from '~/types/product'

export const useCompareStore = defineStore('compare', () => {
  const api = useApi()
  const toast = useToast()

  const selectedSlugs = ref<string[]>([])
  const compareData = ref<CompareResponse | null>(null)
  const isLoading = ref(false)

  // Load from localStorage on client side
  if (import.meta.client) {
    try {
      const saved = localStorage.getItem('app_compare_items')
      if (saved) {
        selectedSlugs.value = JSON.parse(saved)
      }
    } catch {
      // ignore
    }
  }

  function persist() {
    if (import.meta.client) {
      try {
        localStorage.setItem('app_compare_items', JSON.stringify(selectedSlugs.value))
      } catch {
        // ignore
      }
    }
  }

  function addToCompare(slug: string, name?: string) {
    if (selectedSlugs.value.includes(slug)) {
      toast.add({
        title: 'قبلاً اضافه شده',
        description: 'این کالا قبلاً در لیست مقایسه شما قرار دارد.',
        color: 'neutral'
      })
      return
    }

    if (selectedSlugs.value.length >= 4) {
      toast.add({
        title: 'سقف مقایسه',
        description: 'حداکثر می‌توانید ۴ کالا را به طور همزمان مقایسه نمایید.',
        color: 'warning'
      })
      return
    }

    selectedSlugs.value.push(slug)
    persist()

    toast.add({
      title: 'افزوده شد به مقایسه',
      description: name ? `کالای «${name}» به لیست مقایسه اضافه شد.` : 'کالا به لیست مقایسه افزوده شد.',
      color: 'success'
    })
  }

  function removeFromCompare(slug: string) {
    selectedSlugs.value = selectedSlugs.value.filter(s => s !== slug)
    persist()

    if (compareData.value) {
      compareData.value.products = compareData.value.products.filter(p => p.slug !== slug)
    }
  }

  function clearCompare() {
    selectedSlugs.value = []
    compareData.value = null
    persist()
  }

  function isInCompare(slug: string): boolean {
    return selectedSlugs.value.includes(slug)
  }

  async function fetchComparison(): Promise<CompareResponse | null> {
    if (selectedSlugs.value.length === 0) {
      compareData.value = null
      return null
    }

    isLoading.value = true
    try {
      const res = await api<{ success: boolean, data: CompareResponse }>('/products/compare', {
        method: 'POST',
        body: { products: selectedSlugs.value }
      })

      if (res?.data) {
        compareData.value = res.data
        return res.data
      }
      return null
    } catch {
      return null
    } finally {
      isLoading.value = false
    }
  }

  return {
    selectedSlugs,
    compareData,
    isLoading,
    addToCompare,
    removeFromCompare,
    clearCompare,
    isInCompare,
    fetchComparison
  }
})
