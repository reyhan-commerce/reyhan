export interface CategoryItem {
  id: number
  name: string
  slug: string
  icon?: string | null
  image?: string | null
  order?: number
  children?: CategoryItem[]
}

export interface BrandItem {
  id: number
  name: string
  slug: string
  name_en?: string | null
  logo?: string | null
}

export interface ProductAttributeValue {
  id: number
  value: string
  label?: string | null
  hex_code?: string | null
  available?: boolean
}

export interface ProductAttribute {
  id: number
  name: string
  slug: string
  type: 'text' | 'color' | 'number' | 'select'
}

export interface AttributeMatrixItem {
  attribute: ProductAttribute
  values: ProductAttributeValue[]
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

export interface ProductGalleryItem {
  id: number
  url: string
  name: string
  file_name: string
}

export interface ProductBreadcrumb {
  id: number
  name: string
  slug: string
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
  brand?: BrandItem | null
  category?: { id: number, name: string, slug: string } | null
  variants_count: number
}

export interface ProductDetailItem {
  id: number
  name: string
  slug: string
  description?: string | null
  short_description?: string | null
  meta_title?: string | null
  meta_description?: string | null
  gallery: ProductGalleryItem[]
  price_range: { min: number | null, max: number | null }
  is_featured: boolean
  brand?: BrandItem | null
  category?: { id: number, name: string, slug: string } | null
  breadcrumbs: ProductBreadcrumb[]
  variants: ProductVariantItem[]
  variants_matrix: AttributeMatrixItem[]
}

export interface CatalogFilterState {
  category: string
  brand: string[]
  min_price: number | null
  max_price: number | null
  in_stock: boolean
  search: string
  sort: string
  page: number
}
