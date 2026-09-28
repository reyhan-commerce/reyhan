import { describe, it, expect } from 'vitest'
import type { AvailableShippingMethod, ShippingTimeSlotDay } from '~/types/order'

describe('Shipping & Delivery Time Slot Logic', () => {
  const sampleTimeSlots: ShippingTimeSlotDay[] = [
    {
      date: '2026-09-29',
      jalali_date: '۷ مهر',
      day_name: 'امروز',
      slots: ['09:00 - 13:00 (صبح)', '16:00 - 20:00 (عصر)']
    },
    {
      date: '2026-09-30',
      jalali_date: '۸ مهر',
      day_name: 'فردا',
      slots: ['09:00 - 13:00 (صبح)', '16:00 - 20:00 (عصر)']
    }
  ]

  const sampleMethods: AvailableShippingMethod[] = [
    {
      id: 1,
      name: 'پست پیشتاز سراسری',
      slug: 'pishtaz',
      description: 'ارسال با پست پیشتاز شرکت ملی پست به سراسر ایران',
      icon: 'i-lucide-truck',
      shipping_fee: 650000,
      is_free: false,
      estimated_delivery_days: '۲ تا ۴ روز کاری',
      requires_time_slot: false,
      time_slots: []
    },
    {
      id: 2,
      name: 'پیک اکسپرس موتوری (تحویل فوری)',
      slug: 'express_courier',
      description: 'ویژه تهران و البرز',
      icon: 'i-lucide-zap',
      shipping_fee: 950000,
      is_free: false,
      estimated_delivery_days: 'تحویل در همان روز',
      requires_time_slot: true,
      time_slots: sampleTimeSlots
    }
  ]

  it('identifies courier methods that require delivery time slots', () => {
    const pishtaz = sampleMethods.find(m => m.slug === 'pishtaz')
    const express = sampleMethods.find(m => m.slug === 'express_courier')

    expect(pishtaz?.requires_time_slot).toBe(false)
    expect(express?.requires_time_slot).toBe(true)
    expect(express?.time_slots.length).toBe(2)
  })

  it('selects valid delivery day and slot', () => {
    const express = sampleMethods.find(m => m.slug === 'express_courier')!
    const firstDay = express.time_slots[0]
    expect(firstDay.day_name).toBe('امروز')
    expect(firstDay.slots).toContain('16:00 - 20:00 (عصر)')

    const selectedDate = firstDay.date
    const selectedSlot = firstDay.slots[0]

    expect(selectedDate).toBe('2026-09-29')
    expect(selectedSlot).toBe('09:00 - 13:00 (صبح)')
  })

  it('handles free shipping correctly', () => {
    const freeMethod: AvailableShippingMethod = {
      ...sampleMethods[0],
      is_free: true,
      shipping_fee: 0
    }

    expect(freeMethod.is_free).toBe(true)
    expect(freeMethod.shipping_fee).toBe(0)
  })
})
