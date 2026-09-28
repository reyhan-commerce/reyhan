import { describe, it, expect } from 'vitest'

describe('Phase 5 Marketing Engine & Referral Program', () => {
  it('correctly filters all 4 banner positions (home_slider, home_middle, home_grid, sidebar)', () => {
    const banners = [
      { id: 1, title: 'اسلایدر هدر ۱', position: 'home_slider', order: 1 },
      { id: 2, title: 'تخفیف شگفت‌انگیز پاییز', position: 'home_middle', order: 2 },
      { id: 3, title: 'پیشنهاد روز لوازم دیجیتال', position: 'home_grid', order: 3 },
      { id: 4, title: 'سایدبار کاتالوگ', position: 'sidebar', order: 4 },
      { id: 5, title: 'اسلایدر هدر ۲', position: 'home_slider', order: 2 }
    ]

    const sliderBanners = banners.filter(b => b.position === 'home_slider').sort((a, b) => a.order - b.order)
    const middleBanners = banners.filter(b => b.position === 'home_middle').sort((a, b) => a.order - b.order)
    const gridBanners = banners.filter(b => b.position === 'home_grid').sort((a, b) => a.order - b.order)
    const sidebarBanners = banners.filter(b => b.position === 'sidebar').sort((a, b) => a.order - b.order)

    expect(sliderBanners).toHaveLength(2)
    expect(sliderBanners.map(b => b.title)).toEqual(['اسلایدر هدر ۱', 'اسلایدر هدر ۲'])

    expect(middleBanners).toHaveLength(1)
    expect(middleBanners[0].title).toBe('تخفیف شگفت‌انگیز پاییز')

    expect(gridBanners).toHaveLength(1)
    expect(gridBanners[0].title).toBe('پیشنهاد روز لوازم دیجیتال')

    expect(sidebarBanners).toHaveLength(1)
    expect(sidebarBanners[0].title).toBe('سایدبار کاتالوگ')
  })

  it('masks referred user mobile phone numbers for privacy', () => {
    const maskMobile = (mobile: string): string => {
      if (mobile.length >= 11) {
        return mobile.substring(0, 4) + '***' + mobile.substring(mobile.length - 4)
      }
      return mobile
    }

    expect(maskMobile('09123456789')).toBe('0912***6789')
    expect(maskMobile('09351112233')).toBe('0935***2233')
    expect(maskMobile('09109998877')).toBe('0910***8877')
  })

  it('calculates referral dashboard stats correctly', () => {
    const referrals = [
      { id: 1, status: 'completed', reward_amount: 500000 },
      { id: 2, status: 'completed', reward_amount: 500000 },
      { id: 3, status: 'pending', reward_amount: 500000 }
    ]

    const totalCount = referrals.length
    const completedList = referrals.filter(r => r.status === 'completed')
    const completedCount = completedList.length
    const totalEarnedRial = completedList.reduce((sum, r) => sum + r.reward_amount, 0)
    const totalEarnedToman = Math.floor(totalEarnedRial / 10)

    expect(totalCount).toBe(3)
    expect(completedCount).toBe(2)
    expect(totalEarnedRial).toBe(1000000)
    expect(totalEarnedToman).toBe(100000)
  })
})
