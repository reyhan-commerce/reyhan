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
  const filters = ref<CatalogFilterState>({
    category: '',
    brand: [],
    min_price: null,
    max_price: null,
    in_stock: false,
    search: '',
    sort: 'latest',
    page: 1
  })

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

  function setFilter<K extends keyof CatalogFilterState>(key: K, value: CatalogFilterState[K]): void {
    filters.value[key] = value
    filters.value.page = 1
    fetchProducts()
  }

  function resetFilters(): void {
    filters.value = {
      category: '',
      brand: [],
      min_price: null,
      max_price: null,
      in_stock: false,
      search: '',
      sort: 'latest',
      page: 1
    }
    fetchProducts()
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
    resetFilters
  }
})
