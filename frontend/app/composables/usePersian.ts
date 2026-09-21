export function usePersian() {
  const persianDigits = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹']
  const arabicDigits = ['٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩']

  /**
   * Convert any Latin or Arabic digits to Persian digits.
   */
  function toPersianDigits(value: string | number | null | undefined): string {
    if (value === null || value === undefined) return ''
    let str = String(value)
    for (let i = 0; i < 10; i++) {
      str = str.replace(new RegExp(String(i), 'g'), persianDigits[i]!)
      str = str.replace(new RegExp(arabicDigits[i]!, 'g'), persianDigits[i]!)
    }
    return str
  }

  /**
   * Format prices: Converts Rial to Toman (divides by 10) with thousands separator and Persian digits.
   * e.g. 8500000 Rial -> "۸۵۰٬۰۰۰ تومان"
   */
  function formatPrice(rial: number | null | undefined, options: { showUnit?: boolean } = {}): string {
    if (rial === null || rial === undefined) return '—'
    const showUnit = options.showUnit ?? true
    const toman = Math.floor(rial / 10)
    const formattedNumber = toPersianDigits(toman.toLocaleString('en-US'))
    return showUnit ? `${formattedNumber} تومان` : formattedNumber
  }

  /**
   * Format percentage discount with Persian digits and percent sign.
   */
  function formatDiscount(price: number, compareAtPrice: number | null | undefined): string | null {
    if (!compareAtPrice || compareAtPrice <= price) return null
    const discount = Math.round(((compareAtPrice - price) / compareAtPrice) * 100)
    return `${toPersianDigits(discount)}٪`
  }

  return {
    toPersianDigits,
    formatPrice,
    formatDiscount
  }
}
