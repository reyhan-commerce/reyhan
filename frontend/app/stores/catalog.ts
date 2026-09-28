import { defineStore } from 'pinia'
import type {
  CategoryItem,
  ProductCardItem,
  ProductDetailItem,
  CatalogFilterState
} from '~/types/product'
import { useCatalogService } from '~/services/catalogService'

// Re-export types for backward compatibility
export type CategoryTreeItem = CategoryItem
export type FilterState = CatalogFilterState
export type { ProductCardItem, ProductDetailItem, ProductVariantItem, AttributeMatrixItem } from '~/types/product'

export function getDefaultCatalogFilters(): CatalogFilterState {
  return {
    category: '',
    brand: [],
    min_price: null,
    max_price: null,
    in_stock: false,
    has_discount: false,
    attributes: {},
    search: '',
    sort: 'latest',
    page: 1
  }
}

export const useCatalogStore = defineStore('catalog', () => {
  const catalogService = useCatalogService()

  // State
  const categoryTree = ref<CategoryItem[]>([])
  const products = ref<ProductCardItem[]>([])
  const currentProduct = ref<ProductDetailItem | null>(null)
  const totalProducts = ref<number>(0)
  const currentPage = ref<number>(1)
  const lastPage = ref<number>(1)
  const perPage = ref<number>(12)
  const loading = ref<boolean>(false)

  // Filters
  const filters = ref<CatalogFilterState>(getDefaultCatalogFilters())

  // Actions
  async function fetchCategoryTree(): Promise<CategoryItem[]> {
    if (categoryTree.value.length > 0) {
      return categoryTree.value
    }

    try {
      categoryTree.value = await catalogService.getCategoryTree()
      return categoryTree.value
    } catch {
      return []
    }
  }

  async function fetchProducts(customFilters?: Partial<CatalogFilterState>): Promise<void> {
    loading.value = true
    const activeFilters = { ...filters.value, ...customFilters }

    try {
      const result = await catalogService.getProducts(activeFilters)
      products.value = result.items
      totalProducts.value = result.total
      if (result.meta) {
        currentPage.value = result.meta.current_page
        lastPage.value = result.meta.last_page
        perPage.value = result.meta.per_page
      }
    } finally {
      loading.value = false
    }
  }

  async function fetchProduct(slug: string): Promise<ProductDetailItem | null> {
    loading.value = true
    currentProduct.value = null

    try {
      currentProduct.value = await catalogService.getProductBySlug(slug)
      return currentProduct.value
    } finally {
      loading.value = false
    }
  }

  function setFilter<K extends keyof CatalogFilterState>(key: K, value: CatalogFilterState[K], autoFetch = true): void {
    filters.value[key] = value
    if (key !== 'page') {
      filters.value.page = 1
    }
    if (autoFetch) {
      fetchProducts()
    }
  }

  function resetFilters(autoFetch = true): void {
    filters.value = getDefaultCatalogFilters()
    if (autoFetch) {
      fetchProducts()
    }
  }

  function safeDecode(val: string): string {
    try {
      return decodeURIComponent(val)
    } catch {
      return val
    }
  }

  function applyFiltersFromQuery(query: Record<string, unknown>): void {
    const next = getDefaultCatalogFilters()

    if (query.category) {
      next.category = safeDecode(String(query.category))
    }

    if (query.brand) {
      if (Array.isArray(query.brand)) {
        next.brand = query.brand.map(b => safeDecode(String(b))).filter(Boolean)
      } else {
        next.brand = String(query.brand)
          .split(',')
          .map(s => safeDecode(s.trim()))
          .filter(Boolean)
      }
    }

    if (query.min_price !== undefined && query.min_price !== null && query.min_price !== '') {
      const parsed = Number(query.min_price)
      if (!Number.isNaN(parsed)) {
        next.min_price = parsed
      }
    }

    if (query.max_price !== undefined && query.max_price !== null && query.max_price !== '') {
      const parsed = Number(query.max_price)
      if (!Number.isNaN(parsed)) {
        next.max_price = parsed
      }
    }

    if (query.in_stock !== undefined) {
      next.in_stock = query.in_stock === 'true' || query.in_stock === '1' || query.in_stock === 1 || query.in_stock === true
    }

    if (query.has_discount !== undefined) {
      next.has_discount = query.has_discount === 'true' || query.has_discount === '1' || query.has_discount === 1 || query.has_discount === true
    }

    if (query.sort && typeof query.sort === 'string') {
      next.sort = query.sort
    }

    if (query.page) {
      const p = Number(query.page)
      if (!Number.isNaN(p) && p >= 1) {
        next.page = p
      }
    }

    if (query.search && typeof query.search === 'string') {
      next.search = safeDecode(query.search)
    }

    // Dynamic attributes parsing: attr_<slug>=v1,v2 or attributes[<slug>]=v1,v2
    const parsedAttributes: Record<string, string[]> = {}
    for (const [rawKey, rawVal] of Object.entries(query)) {
      if (!rawVal) continue
      let attrSlug = ''
      if (rawKey.startsWith('attr_')) {
        attrSlug = rawKey.slice(5)
      } else {
        const match = rawKey.match(/^attributes\[(.+?)\]$/)
        if (match && match[1]) {
          attrSlug = match[1]
        }
      }

      if (attrSlug) {
        if (Array.isArray(rawVal)) {
          parsedAttributes[attrSlug] = rawVal.map(v => safeDecode(String(v))).filter(Boolean)
        } else {
          parsedAttributes[attrSlug] = String(rawVal)
            .split(',')
            .map(s => safeDecode(s.trim()))
            .filter(Boolean)
        }
      }
    }
    next.attributes = parsedAttributes

    filters.value = next
  }

  function filtersToQuery(): Record<string, string | number> {
    const q: Record<string, string | number> = {}

    if (filters.value.category) {
      q.category = filters.value.category
    }

    if (filters.value.brand && filters.value.brand.length > 0) {
      q.brand = filters.value.brand.join(',')
    }

    if (filters.value.min_price !== null && filters.value.min_price !== undefined) {
      q.min_price = filters.value.min_price
    }

    if (filters.value.max_price !== null && filters.value.max_price !== undefined) {
      q.max_price = filters.value.max_price
    }

    if (filters.value.in_stock) {
      q.in_stock = '1'
    }

    if (filters.value.has_discount) {
      q.has_discount = '1'
    }

    if (filters.value.sort && filters.value.sort !== 'latest') {
      q.sort = filters.value.sort
    }

    if (filters.value.page && filters.value.page > 1) {
      q.page = filters.value.page
    }

    if (filters.value.search && filters.value.search.trim()) {
      q.search = filters.value.search.trim()
    }

    if (filters.value.attributes) {
      for (const [attrSlug, vals] of Object.entries(filters.value.attributes)) {
        if (vals && vals.length > 0) {
          q[`attr_${attrSlug}`] = vals.join(',')
        }
      }
    }

    return q
  }

  return {
    categoryTree,
    products,
    currentProduct,
    totalProducts,
    currentPage,
    lastPage,
    perPage,
    loading,
    filters,
    fetchCategoryTree,
    fetchProducts,
    fetchProduct,
    setFilter,
    resetFilters,
    applyFiltersFromQuery,
    filtersToQuery
  }
})
