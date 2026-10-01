export interface BlogCategory {
  id: number
  name: string
  slug: string
  description: string | null
  order: number
  posts_count?: number
}

export interface BlogAuthor {
  name: string
  avatar: string | null
}

export interface BlogPost {
  id: number
  title: string
  slug: string
  summary: string | null
  content?: string
  featured_image: string | null
  reading_time: number
  views_count: number
  is_featured: boolean
  published_at: string | null
  created_at: string | null
  tags: string[]
  category?: BlogCategory
  author?: BlogAuthor
  meta_title?: string | null
  meta_description?: string | null
}

export interface BlogPaginationMeta {
  current_page: number
  last_page: number
  per_page: number
  total: number
}

export interface BlogPostDetailResponse {
  data: BlogPost
  related: BlogPost[]
}
