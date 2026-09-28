export function useMediaUrl() {
  const config = useRuntimeConfig()

  function getMediaUrl(url: string | null | undefined): string {
    if (!url) return ''

    // Already absolute or data URI
    if (
      url.startsWith('http://')
      || url.startsWith('https://')
      || url.startsWith('data:')
      || url.startsWith('blob:')
    ) {
      return url
    }

    // Extract base URL / origin from apiBase (e.g. "http://localhost:8000/api/v1" -> "http://localhost:8000")
    let origin = 'http://localhost:8000'
    try {
      const apiBase = config.public.apiBase || 'http://localhost:8000/api/v1'
      if (apiBase.startsWith('http')) {
        const parsed = new URL(apiBase)
        origin = parsed.origin
      }
    } catch {
      // ignore
    }

    const cleanPath = url.startsWith('/') ? url : `/${url}`
    return `${origin}${cleanPath}`
  }

  return {
    getMediaUrl
  }
}
