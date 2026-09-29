export default defineNuxtRouteMiddleware((to) => {
  const features = useFeatures()

  // Feature to route prefix mapping
  const routeFeatureMap: Record<string, string> = {
    '/blog': 'blog',
    '/profile/wallet': 'wallet',
    '/profile/club': 'loyalty',
    '/loyalty': 'loyalty',
    '/profile/wishlist': 'wishlist',
    '/wishlist': 'wishlist',
    '/profile/referral': 'referral',
    '/profile/returns': 'returns',
    '/profile/tickets': 'tickets',
    '/compare': 'comparison',
    '/faq': 'faq'
  }

  // Check if current route starts with any protected feature path
  for (const [routePrefix, featureName] of Object.entries(routeFeatureMap)) {
    if (to.path === routePrefix || to.path.startsWith(`${routePrefix}/`)) {
      if (!features.hasFeature(featureName)) {
        // Feature is disabled - redirect to safe fallback
        if (to.path.startsWith('/profile')) {
          return navigateTo('/profile', { replace: true })
        }
        return navigateTo('/', { replace: true })
      }
    }
  }
})
