export interface FaqItem {
  id: number
  question: string
  answer: string
  category: string | null
  order: number
}

export interface FaqResponse {
  items: FaqItem[]
  categories: string[]
}

export interface CmsPageStat {
  value: string
  label: string
}

export interface CmsPageFeature {
  title: string
  desc: string
  icon: string
  color?: string
}

export interface CmsPageMetadata {
  badge?: string
  heading?: string
  stats?: CmsPageStat[]
  features?: CmsPageFeature[]
  [key: string]: unknown
}

export interface CmsPage {
  id: number
  title: string
  slug: string
  content: string | null
  metadata?: CmsPageMetadata | null
  meta_title?: string | null
  meta_description?: string | null
  updated_at?: string
}
