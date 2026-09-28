import { describe, it, expect } from 'vitest'

describe('Product Comparison Logic & Matrix Building', () => {
  function createCompareManager(initial: string[] = []) {
    let slugs = [...initial]

    return {
      getSlugs: () => slugs,
      add: (slug: string) => {
        if (slugs.includes(slug)) return { success: false, reason: 'duplicate' }
        if (slugs.length >= 4) return { success: false, reason: 'limit_reached' }
        slugs.push(slug)
        return { success: true }
      },
      remove: (slug: string) => {
        slugs = slugs.filter(s => s !== slug)
      },
      clear: () => {
        slugs = []
      },
      isInCompare: (slug: string) => slugs.includes(slug),
    }
  }

  it('adds products to compare list up to a maximum of 4 items', () => {
    const manager = createCompareManager()
    expect(manager.getSlugs()).toHaveLength(0)

    expect(manager.add('iphone-15').success).toBe(true)
    expect(manager.add('galaxy-s24').success).toBe(true)
    expect(manager.add('xiaomi-14').success).toBe(true)
    expect(manager.add('pixel-8').success).toBe(true)
    expect(manager.getSlugs()).toHaveLength(4)

    // Attempting to add 5th item fails with limit_reached
    const overflow = manager.add('sony-xperia')
    expect(overflow.success).toBe(false)
    expect(overflow.reason).toBe('limit_reached')
    expect(manager.getSlugs()).toHaveLength(4)
  })

  it('prevents adding duplicate products to compare list', () => {
    const manager = createCompareManager(['iphone-15'])
    const res = manager.add('iphone-15')
    expect(res.success).toBe(false)
    expect(res.reason).toBe('duplicate')
    expect(manager.getSlugs()).toHaveLength(1)
  })

  it('removes and clears products accurately', () => {
    const manager = createCompareManager(['iphone-15', 'galaxy-s24'])
    manager.remove('iphone-15')
    expect(manager.getSlugs()).toEqual(['galaxy-s24'])
    expect(manager.isInCompare('galaxy-s24')).toBe(true)
    expect(manager.isInCompare('iphone-15')).toBe(false)

    manager.clear()
    expect(manager.getSlugs()).toHaveLength(0)
  })
})
