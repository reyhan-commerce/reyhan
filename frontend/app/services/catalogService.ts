import type {
  CategoryItem,
  ProductCardItem,
  ProductDetailItem,
  CatalogFilterState
} from '~/types/product'
import type { ApiResponse, LaravelPaginatedResponse, PaginationMeta } from '~/types/api'

export interface ProductListResult {
  items: ProductCardItem[]
  meta?: PaginationMeta
  total: number
}

export function useCatalogService() {
  const api = useApi()

  async function getCategoryTree(): Promise<CategoryItem[]> {
    const res = await api<ApiResponse<CategoryItem[]>>('/categories/tree')
    return res.data || []
  }

  async function getProducts(filters?: Partial<CatalogFilterState>): Promise<ProductListResult> {
    const params: Record<string, string | number | boolean> = {}

    if (filters?.category) params.category = filters.category
    if (filters?.brand && filters.brand.length > 0) params.brand = filters.brand.join(',')
    if (filters?.min_price !== null && filters?.min_price !== undefined) params.min_price = filters.min_price
    if (filters?.max_price !== null && filters?.max_price !== undefined) params.max_price = filters.max_price
    if (filters?.in_stock) params.in_stock = true
    if (filters?.search) params.search = filters.search
    if (filters?.sort) params.sort = filters.sort
    if (filters?.page) params.page = filters.page

    const res = await api<LaravelPaginatedResponse<ProductCardItem>>('/products', { params })
    const items = res.data || []
    return {
      items,
      meta: res.meta,
      total: res.meta?.total || items.length
    }
  }

  async function getProductBySlug(slug: string): Promise<ProductDetailItem | null> {
    const res = await api<ApiResponse<ProductDetailItem>>(`/products/${encodeURIComponent(slug)}`)
    return res.data || null
  }

  async function getSuggestions(query: string): Promise<{
    products: ProductCardItem[]
    categories: { id: number, name: string, slug: string }[]
    brands: { id: number, name: string, slug: string }[]
  }> {
    const res = await api<ApiResponse<{
      products: ProductCardItem[]
      categories: { id: number, name: string, slug: string }[]
      brands: { id: number, name: string, slug: string }[]
    }>>('/search/suggestions', {
      params: { q: query }
    })

    return {
      products: res.data?.products || [],
      categories: res.data?.categories || [],
      brands: res.data?.brands || []
    }
  }

  return {
    getCategoryTree,
    getProducts,
    getProductBySlug,
    getSuggestions
  }
}
