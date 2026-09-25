import { describe, it, expect } from 'vitest'
import { otpRequestSchema, otpVerifySchema, addressSchema } from '~/utils/schemas'

describe('Zod Validation Schemas', () => {
  describe('otpRequestSchema', () => {
    it('accepts valid 11-digit Iranian mobile numbers starting with 09', () => {
      const valid = otpRequestSchema.safeParse({
        mobile: '09123456789',
        captcha: 'valid_captcha_token'
      })
      expect(valid.success).toBe(true)
    })

    it('accepts Persian digits and normalizes them', () => {
      const valid = otpRequestSchema.safeParse({
        mobile: '۰۹۱۲۳۴۵۶۷۸۹',
        captcha: 'token'
      })
      expect(valid.success).toBe(true)
      if (valid.success) {
        expect(valid.data.mobile).toBe('09123456789')
      }
    })

    it('rejects invalid mobile formats', () => {
      const invalid = otpRequestSchema.safeParse({
        mobile: '02188889999',
        captcha: 'token'
      })
      expect(invalid.success).toBe(false)
    })
  })

  describe('otpVerifySchema', () => {
    it('accepts 6-digit OTP codes', () => {
      const valid = otpVerifySchema.safeParse({ code: '123456' })
      expect(valid.success).toBe(true)
    })

    it('accepts Persian digits and converts to English digits', () => {
      const valid = otpVerifySchema.safeParse({ code: '۱۲۳۴۵۶' })
      expect(valid.success).toBe(true)
      if (valid.success) {
        expect(valid.data.code).toBe('123456')
      }
    })

    it('rejects codes that are not 6 digits', () => {
      expect(otpVerifySchema.safeParse({ code: '12345' }).success).toBe(false)
      expect(otpVerifySchema.safeParse({ code: '1234567' }).success).toBe(false)
    })
  })

  describe('addressSchema', () => {
    it('accepts a valid postal address payload', () => {
      const valid = addressSchema.safeParse({
        title: 'منزل',
        recipient_name: 'فرشید جعفری',
        recipient_mobile: '09123456789',
        province_id: 8,
        city_id: 301,
        address: 'تهران، خیابان ولیعصر، کوچه نمونه، پلاک ۱۰',
        postal_code: '1234567890'
      })
      expect(valid.success).toBe(true)
    })

    it('rejects postal code with invalid length', () => {
      const invalid = addressSchema.safeParse({
        title: 'منزل',
        recipient_name: 'فرشید جعفری',
        recipient_mobile: '09123456789',
        province_id: 8,
        city_id: 301,
        address: 'تهران، خیابان ولیعصر، کوچه نمونه، پلاک ۱۰',
        postal_code: '12345'
      })
      expect(invalid.success).toBe(false)
    })
  })
})
