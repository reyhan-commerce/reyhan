import { defineStore } from 'pinia'

export interface CategoryTreeItem {
  id: number
  name: string
  slug: string
  icon?: string | null
  image?: string | null
  order: number
  children?: CategoryTreeItem[]
}

export interface ProductCardItem {
  id: number
  name: string
  slug: string
  short_description?: string | null
  thumbnail?: string | null
  price_range: { min: number | null, max: number | null }
  primary_price?: number | null
  primary_compare_at_price?: number | null
  has_discount: boolean
  is_in_stock: boolean
  is_featured: boolean
  brand?: { id: number, name: string, slug: string, name_en?: string | null, logo?: string | null } | null
  category?: { id: number, name: string, slug: string } | null
  variants_count: number
}

export interface AttributeMatrixItem {
  attribute: {
    id: number
    name: string
    slug: string
    type: 'text' | 'color' | 'number' | 'select'
  }
  values: Array<{
    id: number
    value: string
    label?: string | null
    hex_code?: string | null
    available: boolean
  }>
}

export interface ProductVariantItem {
  id: number
  sku: string
  barcode?: string | null
  title: string
  price: number
  compare_at_price?: number | null
  has_discount: boolean
  stock: number
  is_in_stock: boolean
  is_low_stock: boolean
  weight?: number | null
  attributes?: Array<{
    attribute_id: number
    attribute_name?: string
    attribute_slug?: string
    value_id: number
    value: string
    label?: string | null
    hex_code?: string | null
  }>
}

export interface ProductDetailItem {
  id: number
  name: string
  slug: string
  description?: string | null
  short_description?: string | null
  meta_title?: string | null
  meta_description?: string | null
  gallery: Array<{ id: number, url: string, name: string, file_name: string }>
  price_range: { min: number | null, max: number | null }
  is_featured: boolean
  brand?: { id: number, name: string, slug: string, name_en?: string | null, logo?: string | null } | null
  category?: { id: number, name: string, slug: string } | null
  breadcrumbs: Array<{ id: number, name: string, slug: string }>
  variants: ProductVariantItem[]
  variants_matrix: AttributeMatrixItem[]
}

export interface FilterState {
  category: string
  brand: string[]
  min_price: number | null
  max_price: number | null
  in_stock: boolean
  search: string
  sort: string
  page: number
}

export const useCatalogStore = defineStore('catalog', () => {
  const api = useApi()

  // State
  const categoryTree = ref<CategoryTreeItem[]>([])
  const products = ref<ProductCardItem[]>([])
  const currentProduct = ref<ProductDetailItem | null>(null)
  const totalProducts = ref<number>(0)
  const currentPage = ref<number>(1)
  const lastPage = ref<number>(1)
  const perPage = ref<number>(12)
  const loading = ref<boolean>(false)

  // Filters
  const filters = ref<FilterState>({
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
  async function fetchCategoryTree(): Promise<CategoryTreeItem[]> {
    if (categoryTree.value.length > 0) {
      return categoryTree.value
    }

    try {
      const res = await api<ApiResponse<CategoryTreeItem[]>>('/categories/tree')
      if (res.data) {
        categoryTree.value = res.data
      }
      return categoryTree.value
    } catch {
      return []
    }
  }

  async function fetchProducts(customFilters?: Partial<FilterState>): Promise<void> {
    loading.value = true
    const activeFilters = { ...filters.value, ...customFilters }

    try {
      const params: Record<string, string | number | boolean> = {}

      if (activeFilters.category) params.category = activeFilters.category
      if (activeFilters.brand.length > 0) params.brand = activeFilters.brand.join(',')
      if (activeFilters.min_price) params.min_price = activeFilters.min_price
      if (activeFilters.max_price) params.max_price = activeFilters.max_price
      if (activeFilters.in_stock) params.in_stock = true
      if (activeFilters.search) params.search = activeFilters.search
      if (activeFilters.sort) params.sort = activeFilters.sort
      params.page = activeFilters.page || 1
      params.per_page = perPage.value

      const res = await api<{
        data: ProductCardItem[]
        meta?: { total: number, current_page: number, last_page: number }
      }>('/products', { params })

      if (res.data) {
        products.value = res.data
        if (res.meta) {
          totalProducts.value = res.meta.total
          currentPage.value = res.meta.current_page
          lastPage.value = res.meta.last_page
        }
      }
    } finally {
      loading.value = false
    }
  }

  async function fetchProduct(slug: string): Promise<ProductDetailItem | null> {
    loading.value = true
    currentProduct.value = null

    try {
      const res = await api<ApiResponse<ProductDetailItem>>(`/products/${encodeURIComponent(slug)}`)
      if (res.data) {
        currentProduct.value = res.data
      }
      return currentProduct.value
    } finally {
      loading.value = false
    }
  }

  function setFilter<K extends keyof FilterState>(key: K, value: FilterState[K]): void {
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
