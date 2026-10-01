import { usePersian } from './usePersian'

export function useNormalizeDigits() {
  const { toEnglishDigits, toPersianDigits } = usePersian()

  /**
   * Helper method to handle input events and normalize Persian digits to ASCII
   */
  function normalizeInput(val: string | null | undefined): string {
    return toEnglishDigits(val)
  }

  /**
   * Input event handler for numeric inputs
   */
  function onInputNormalize(event: Event): string {
    const target = event.target as HTMLInputElement | null
    if (!target) return ''
    const normalized = toEnglishDigits(target.value)
    target.value = normalized
    return normalized
  }

  return {
    toEnglishDigits,
    toPersianDigits,
    normalizeInput,
    onInputNormalize
  }
}
