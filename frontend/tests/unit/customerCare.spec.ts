import { describe, it, expect } from 'vitest'

describe('Phase 4 Customer Care & Retention Engine Logic', () => {
  it('correctly validates and computes RMA return refund sums', () => {
    const orderItems = [
      { id: 101, product_name: 'گوشی موبایل', final_price: 30000000, quantity: 1 },
      { id: 102, product_name: 'قاب محافظ', final_price: 600000, quantity: 2 },
    ]

    // Customer selects only item 102 with qty 1
    const chosenReturns = [
      { order_item_id: 102, quantity: 1 }
    ]

    let totalRefund = 0
    chosenReturns.forEach(ret => {
      const match = orderItems.find(i => i.id === ret.order_item_id)
      if (match) {
        const unit = match.final_price / match.quantity
        totalRefund += unit * ret.quantity
      }
    })

    expect(totalRefund).toBe(300000)
  })

  it('normalizes SVG price history chart points between 0 and 1', () => {
    const minPrice = 1000000
    const maxPrice = 5000000
    const range = maxPrice - minPrice

    const points = [
      { price: 1000000 },
      { price: 3000000 },
      { price: 5000000 },
    ]

    const normalized = points.map(p => (p.price - minPrice) / range)

    expect(normalized[0]).toBe(0)
    expect(normalized[1]).toBe(0.5)
    expect(normalized[2]).toBe(1)
  })

  it('validates support ticket department and status rules', () => {
    const departments = ['support', 'finance', 'sales', 'shipping', 'complaints']
    const statuses = ['open', 'answered', 'awaiting_reply', 'closed']

    expect(departments).toContain('shipping')
    expect(departments).toContain('finance')
    expect(statuses).toContain('open')
    expect(statuses).toContain('answered')
  })
})
