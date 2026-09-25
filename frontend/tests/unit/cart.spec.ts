import { describe, it, expect } from 'vitest'
import type { CartPricing } from '~/types/cart'

describe('Cart Financial Calculations & Thresholds', () => {
  function calculatePricing(params: {
    items: Array<{ price: number, compare_at_price?: number | null, quantity: number }>
    freeShippingThreshold: number
    standardShippingFee: number
    coupon?: { type: 'fixed' | 'percent', value: number } | null
  }): CartPricing {
    let originalSubtotal = 0
    let itemsSubtotal = 0

    for (const item of params.items) {
      const origUnit = item.compare_at_price && item.compare_at_price > item.price ? item.compare_at_price : item.price
      originalSubtotal += origUnit * item.quantity
      itemsSubtotal += item.price * item.quantity
    }

    const catalogDiscount = originalSubtotal - itemsSubtotal

    let couponDiscount = 0
    if (params.coupon) {
      if (params.coupon.type === 'percent') {
        couponDiscount = Math.round((itemsSubtotal * params.coupon.value) / 100)
      } else {
        couponDiscount = Math.min(itemsSubtotal, params.coupon.value)
      }
    }

    const totalDiscount = catalogDiscount + couponDiscount
    const isFreeShipping = itemsSubtotal >= params.freeShippingThreshold
    const shippingFee = isFreeShipping ? 0 : params.standardShippingFee
    const remainingForFreeShipping = Math.max(0, params.freeShippingThreshold - itemsSubtotal)
    const progress = Math.min(100, Math.round((itemsSubtotal / params.freeShippingThreshold) * 100))
    const finalPayable = Math.max(0, itemsSubtotal - couponDiscount + shippingFee)

    return {
      original_items_subtotal: originalSubtotal,
      items_subtotal: itemsSubtotal,
      catalog_discount: catalogDiscount,
      coupon_discount: couponDiscount,
      total_discount: totalDiscount,
      shipping_fee: shippingFee,
      is_free_shipping: isFreeShipping,
      free_shipping_threshold: params.freeShippingThreshold,
      remaining_for_free_shipping: remainingForFreeShipping,
      free_shipping_progress: progress,
      final_payable: finalPayable,
      total_items_count: params.items.reduce((acc, i) => acc + i.quantity, 0),
      total_weight_grams: 500,
      applied_coupon: params.coupon ? { code: 'TEST', title: 'تخفیف', type: params.coupon.type, value: params.coupon.value } : null
    }
  }

  it('calculates subtotals and catalog discounts correctly', () => {
    const result = calculatePricing({
      items: [
        { price: 800000, compare_at_price: 1000000, quantity: 2 }, // 1.6M paid, 2M orig
        { price: 500000, compare_at_price: null, quantity: 1 } // 0.5M paid, 0.5M orig
      ],
      freeShippingThreshold: 5000000, // 5M threshold
      standardShippingFee: 650000
    })

    expect(result.original_items_subtotal).toBe(2500000)
    expect(result.items_subtotal).toBe(2100000)
    expect(result.catalog_discount).toBe(400000)
    expect(result.is_free_shipping).toBe(false)
    expect(result.shipping_fee).toBe(650000)
    expect(result.remaining_for_free_shipping).toBe(2900000)
    expect(result.final_payable).toBe(2100000 + 650000)
  })

  it('qualifies for free shipping when exceeding threshold', () => {
    const result = calculatePricing({
      items: [
        { price: 6000000, compare_at_price: null, quantity: 1 }
      ],
      freeShippingThreshold: 5000000,
      standardShippingFee: 650000
    })

    expect(result.is_free_shipping).toBe(true)
    expect(result.shipping_fee).toBe(0)
    expect(result.remaining_for_free_shipping).toBe(0)
    expect(result.free_shipping_progress).toBe(100)
    expect(result.final_payable).toBe(6000000)
  })

  it('applies percentage coupon discounts accurately', () => {
    const result = calculatePricing({
      items: [
        { price: 1000000, compare_at_price: null, quantity: 2 } // 2M
      ],
      freeShippingThreshold: 5000000,
      standardShippingFee: 500000,
      coupon: { type: 'percent', value: 10 } // 10% of 2M = 200k
    })

    expect(result.coupon_discount).toBe(200000)
    expect(result.final_payable).toBe(2000000 - 200000 + 500000)
  })

  it('caps fixed coupon at items subtotal to avoid negative amounts', () => {
    const result = calculatePricing({
      items: [
        { price: 300000, compare_at_price: null, quantity: 1 }
      ],
      freeShippingThreshold: 5000000,
      standardShippingFee: 500000,
      coupon: { type: 'fixed', value: 1000000 } // 1M coupon on 300k item
    })

    expect(result.coupon_discount).toBe(300000)
    expect(result.final_payable).toBe(500000) // only shipping fee remains
  })
})
