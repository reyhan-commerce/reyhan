export default defineNuxtRouteMiddleware((to) => {
  const token = useCookie<string | null>('auth_token')

  if (!token.value) {
    // If running in client context, trigger auth modal
    if (import.meta.client) {
      const authStore = useAuthStore()
      authStore.openAuthModal()
    }

    return navigateTo({
      path: '/',
      query: { redirect: to.fullPath }
    })
  }
})
