import { describe, it, expect } from 'vitest'
import { getDefaultCatalogFilters } from '~/stores/catalog'
import type { CatalogFilterState } from '~/types/product'

describe('Catalog Filter State & Query Serialization', () => {
  it('provides clean default filters', () => {
    const defaults = getDefaultCatalogFilters()
    expect(defaults.category).toBe('')
    expect(defaults.brand).toEqual([])
    expect(defaults.min_price).toBeNull()
    expect(defaults.max_price).toBeNull()
    expect(defaults.in_stock).toBe(false)
    expect(defaults.has_discount).toBe(false)
    expect(defaults.sort).toBe('latest')
    expect(defaults.page).toBe(1)
    expect(defaults.attributes).toEqual({})
  })

  function parseQuery(query: Record<string, unknown>): CatalogFilterState {
    const next = getDefaultCatalogFilters()

    if (query.category) next.category = String(query.category)

    if (query.brand) {
      if (Array.isArray(query.brand)) {
        next.brand = query.brand.map(String).filter(Boolean)
      } else {
        next.brand = String(query.brand).split(',').map(s => s.trim()).filter(Boolean)
      }
    }

    if (query.min_price !== undefined && query.min_price !== null && query.min_price !== '') {
      const parsed = Number(query.min_price)
      if (!Number.isNaN(parsed)) next.min_price = parsed
    }

    if (query.max_price !== undefined && query.max_price !== null && query.max_price !== '') {
      const parsed = Number(query.max_price)
      if (!Number.isNaN(parsed)) next.max_price = parsed
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
      if (!Number.isNaN(p) && p >= 1) next.page = p
    }

    if (query.search && typeof query.search === 'string') {
      next.search = query.search
    }

    const parsedAttributes: Record<string, string[]> = {}
    for (const [rawKey, rawVal] of Object.entries(query)) {
      if (!rawVal) continue
      let attrSlug = ''
      if (rawKey.startsWith('attr_')) {
        attrSlug = rawKey.slice(5)
      } else {
        const match = rawKey.match(/^attributes\[(.+?)\]$/)
        if (match && match[1]) attrSlug = match[1]
      }

      if (attrSlug) {
        if (Array.isArray(rawVal)) {
          parsedAttributes[attrSlug] = rawVal.map(String).filter(Boolean)
        } else {
          parsedAttributes[attrSlug] = String(rawVal).split(',').map(s => s.trim()).filter(Boolean)
        }
      }
    }
    next.attributes = parsedAttributes

    return next
  }

  function serializeFilters(filters: CatalogFilterState): Record<string, string | number> {
    const q: Record<string, string | number> = {}

    if (filters.category) q.category = filters.category
    if (filters.brand && filters.brand.length > 0) q.brand = filters.brand.join(',')
    if (filters.min_price !== null && filters.min_price !== undefined) q.min_price = filters.min_price
    if (filters.max_price !== null && filters.max_price !== undefined) q.max_price = filters.max_price
    if (filters.in_stock) q.in_stock = '1'
    if (filters.has_discount) q.has_discount = '1'
    if (filters.sort && filters.sort !== 'latest') q.sort = filters.sort
    if (filters.page && filters.page > 1) q.page = filters.page
    if (filters.search && filters.search.trim()) q.search = filters.search.trim()

    if (filters.attributes) {
      for (const [attrSlug, vals] of Object.entries(filters.attributes)) {
        if (vals && vals.length > 0) {
          q[`attr_${attrSlug}`] = vals.join(',')
        }
      }
    }

    return q
  }

  it('correctly parses URL query parameters into filter state', () => {
    const query = {
      category: 'skincare',
      brand: 'cerave,the-ordinary',
      min_price: '200000',
      max_price: '1500000',
      in_stock: '1',
      has_discount: '1',
      sort: 'cheapest',
      page: '3',
      search: 'آبرسان',
      attr_skin_type: 'dry,sensitive'
    }

    const parsed = parseQuery(query)

    expect(parsed.category).toBe('skincare')
    expect(parsed.brand).toEqual(['cerave', 'the-ordinary'])
    expect(parsed.min_price).toBe(200000)
    expect(parsed.max_price).toBe(1500000)
    expect(parsed.in_stock).toBe(true)
    expect(parsed.has_discount).toBe(true)
    expect(parsed.sort).toBe('cheapest')
    expect(parsed.page).toBe(3)
    expect(parsed.search).toBe('آبرسان')
    expect(parsed.attributes).toEqual({ skin_type: ['dry', 'sensitive'] })
  })

  it('serializes active filters into clean URL query params', () => {
    const filters: CatalogFilterState = {
      category: 'makeup',
      brand: ['mac', 'huda-beauty'],
      min_price: 350000,
      max_price: 2000000,
      in_stock: true,
      has_discount: false,
      attributes: { finish: ['matte', 'satin'] },
      search: 'کرم پودر',
      sort: 'popular',
      page: 2
    }

    const query = serializeFilters(filters)

    expect(query).toEqual({
      category: 'makeup',
      brand: 'mac,huda-beauty',
      min_price: 350000,
      max_price: 2000000,
      in_stock: '1',
      sort: 'popular',
      page: 2,
      search: 'کرم پودر',
      attr_finish: 'matte,satin'
    })
    expect(query.has_discount).toBeUndefined()
  })

  it('omits defaults from query to keep URL clean', () => {
    const defaults = getDefaultCatalogFilters()
    const query = serializeFilters(defaults)
    expect(Object.keys(query).length).toBe(0)
  })
})
