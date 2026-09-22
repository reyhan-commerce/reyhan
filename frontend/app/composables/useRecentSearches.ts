export const useRecentSearches = () => {
  const STORAGE_KEY = 'easyshop_recent_searches'
  const MAX_ITEMS = 8

  const recentSearches = ref<string[]>([])

  const loadFromStorage = () => {
    if (!import.meta.client) return
    try {
      const stored = localStorage.getItem(STORAGE_KEY)
      if (stored) {
        const parsed = JSON.parse(stored)
        if (Array.isArray(parsed)) {
          recentSearches.value = parsed.filter(item => typeof item === 'string' && item.trim().length > 0)
        }
      }
    } catch {
      recentSearches.value = []
    }
  }

  const saveToStorage = () => {
    if (!import.meta.client) return
    try {
      localStorage.setItem(STORAGE_KEY, JSON.stringify(recentSearches.value))
    } catch {
      // Ignore localStorage quotas or private mode errors
    }
  }

  const addRecentSearch = (term: string) => {
    const trimmed = term.trim()
    if (trimmed.length < 2) return

    // Remove if already present (case-insensitive deduplication)
    const existingIndex = recentSearches.value.findIndex(
      item => item.toLowerCase() === trimmed.toLowerCase()
    )
    if (existingIndex !== -1) {
      recentSearches.value.splice(existingIndex, 1)
    }

    // Prepend to top
    recentSearches.value.unshift(trimmed)

    // Cap to MAX_ITEMS
    if (recentSearches.value.length > MAX_ITEMS) {
      recentSearches.value = recentSearches.value.slice(0, MAX_ITEMS)
    }

    saveToStorage()
  }

  const removeRecentSearch = (term: string) => {
    recentSearches.value = recentSearches.value.filter(
      item => item.toLowerCase() !== term.toLowerCase()
    )
    saveToStorage()
  }

  const clearRecentSearches = () => {
    recentSearches.value = []
    if (import.meta.client) {
      try {
        localStorage.removeItem(STORAGE_KEY)
      } catch {
        // Ignore
      }
    }
  }

  onMounted(() => {
    loadFromStorage()
  })

  return {
    recentSearches,
    addRecentSearch,
    removeRecentSearch,
    clearRecentSearches
  }
}
