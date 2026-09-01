export default defineNuxtRouteMiddleware(() => {
  const auth = useAuthStore()
  return navigateTo(auth.isAuthenticated ? '/dashboard' : '/login')
})
