import { describe, it, expect } from 'vitest'

describe('Phase 3 Financials & Corporate Tax Invoice Logic', () => {
  it('calculates 10% Iranian standard VAT on taxable items', () => {
    const items = [
      { id: 1, product_name: 'لپ‌تاپ مهندسی', total_price: 50000000 },
      { id: 2, product_name: 'ماوس بی‌سیم', total_price: 5000000 }
    ]

    const vatRate = 0.10
    const taxableItems = items.map((item) => {
      const vat = Math.round(item.total_price * vatRate)
      return {
        ...item,
        vat,
        grandTotal: item.total_price + vat
      }
    })

    expect(taxableItems[0].vat).toBe(5000000)
    expect(taxableItems[0].grandTotal).toBe(55000000)
    expect(taxableItems[1].vat).toBe(500000)
    expect(taxableItems[1].grandTotal).toBe(5500000)

    const totalVat = taxableItems.reduce((acc, curr) => acc + curr.vat, 0)
    expect(totalVat).toBe(5500000)
  })

  it('calculates partial and full wallet balance deductions accurately', () => {
    const orderTotal = 1500000 // 150,000 Toman

    // Scenario A: Insufficient wallet balance (partial deduction)
    const walletBalanceA = 500000
    const useWalletA = true
    const deductionA = useWalletA ? Math.min(walletBalanceA, orderTotal) : 0
    const payableA = Math.max(0, orderTotal - (useWalletA ? walletBalanceA : 0))
    expect(deductionA).toBe(500000)
    expect(payableA).toBe(1000000)

    // Scenario B: Full wallet balance (covers 100%)
    const walletBalanceB = 2500000
    const useWalletB = true
    const deductionB = useWalletB ? Math.min(walletBalanceB, orderTotal) : 0
    const payableB = Math.max(0, orderTotal - (useWalletB ? walletBalanceB : 0))
    expect(deductionB).toBe(1500000)
    expect(payableB).toBe(0)
    expect(walletBalanceB >= orderTotal).toBe(true)

    // Scenario C: Wallet toggle off
    const useWalletC = false
    const deductionC = useWalletC ? Math.min(walletBalanceA, orderTotal) : 0
    const payableC = Math.max(0, orderTotal - (useWalletC ? walletBalanceA : 0))
    expect(deductionC).toBe(0)
    expect(payableC).toBe(1500000)
  })

  it('correctly handles both paginated and flat array wallet transactions payloads', () => {
    // Laravel LengthAwarePaginator structure
    const paginatedResponse = {
      current_page: 1,
      data: [
        { id: 1, type: 'deposit', amount: 500000, balance_after: 500000, description: 'شارژ آنلاین' }
      ],
      first_page_url: 'http://localhost/api/v1/wallet?page=1',
      last_page: 1,
      next_page_url: null,
      prev_page_url: null,
      total: 1
    }

    const extractTransactions = (tx: any) => {
      if (!tx) return []
      if (Array.isArray(tx)) return tx
      if (Array.isArray(tx?.data)) return tx.data
      return []
    }

    const listFromPaginator = extractTransactions(paginatedResponse)
    expect(listFromPaginator).toHaveLength(1)
    expect(listFromPaginator[0].type).toBe('deposit')

    const flatResponse = [
      { id: 2, type: 'withdraw', amount: 100000, balance_after: 400000, description: 'خرید' }
    ]
    const listFromFlat = extractTransactions(flatResponse)
    expect(listFromFlat).toHaveLength(1)
    expect(listFromFlat[0].type).toBe('withdraw')

    const emptyPaginator = {
      current_page: 1,
      data: [],
      next_page_url: null,
      total: 0
    }
    const listFromEmpty = extractTransactions(emptyPaginator)
    expect(listFromEmpty).toHaveLength(0)
  })
})
