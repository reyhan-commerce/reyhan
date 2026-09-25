import { describe, it, expect } from 'vitest'
import { usePersian } from '~/composables/usePersian'

describe('usePersian Composable', () => {
  const { toPersianDigits, toEnglishDigits, formatPrice, formatDiscount } = usePersian()

  describe('toPersianDigits', () => {
    it('converts English digits to Persian digits correctly', () => {
      expect(toPersianDigits('1234567890')).toBe('۱۲۳۴۵۶۷۸۹۰')
      expect(toPersianDigits(123)).toBe('۱۲۳')
    })

    it('converts Arabic digits to Persian digits', () => {
      expect(toPersianDigits('١٢٣٤٥')).toBe('۱۲۳۴۵')
    })

    it('returns empty string for null or undefined', () => {
      expect(toPersianDigits(null)).toBe('')
      expect(toPersianDigits(undefined)).toBe('')
    })
  })

  describe('toEnglishDigits', () => {
    it('converts Persian digits to standard English ASCII digits', () => {
      expect(toEnglishDigits('۱۲۳۴۵۶۷۸۹۰')).toBe('1234567890')
    })

    it('converts mixed Persian and Arabic digits to English', () => {
      expect(toEnglishDigits('۰۹۱۲٣٤٥۶۷۸۹')).toBe('09123456789')
    })
  })

  describe('formatPrice', () => {
    it('converts Rial to Toman and adds commas and Persian digits', () => {
      // 5,000,000 Rial = 500,000 Toman
      expect(formatPrice(5000000)).toBe('۵۰۰,۰۰۰ تومان')
    })

    it('formats price without unit when showUnit is false', () => {
      expect(formatPrice(1000000, { showUnit: false })).toBe('۱۰۰,۰۰۰')
    })

    it('returns dash for null or undefined price', () => {
      expect(formatPrice(null)).toBe('—')
      expect(formatPrice(undefined)).toBe('—')
    })
  })

  describe('formatDiscount', () => {
    it('computes correct discount percentage with Persian sign', () => {
      // 1000 -> 800 (20% discount)
      expect(formatDiscount(800, 1000)).toBe('۲۰٪')
    })

    it('returns null if there is no discount or price is higher', () => {
      expect(formatDiscount(1000, 1000)).toBeNull()
      expect(formatDiscount(1200, 1000)).toBeNull()
      expect(formatDiscount(1000, null)).toBeNull()
    })
  })
})
