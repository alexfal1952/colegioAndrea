export default defineNuxtPlugin(async () => {
  const auth = useAuthStore()
  auth.init()

  if (auth.token && !auth.user) {
    await auth.fetchUser()
  }
})
