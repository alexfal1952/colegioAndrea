import { defineStore } from 'pinia'
import type { AuthResponse, ForgotPasswordPayload, LoginCredentials, MessageResponse, RegisterCredentials, ResetPasswordPayload, User } from '~/types/auth'

const TOKEN_KEY = 'auth_token'

export const useAuthStore = defineStore('auth', {
  state: () => ({
    user: null as User | null,
    token: null as string | null,
    loading: false,
    error: null as string | null,
  }),

  getters: {
    isAuthenticated: (state) => !!state.token && !!state.user,
  },

  actions: {
    init() {
      if (import.meta.client) {
        const savedToken = localStorage.getItem(TOKEN_KEY)
        if (savedToken) {
          this.token = savedToken
        }
      }
    },

    setToken(token: string | null) {
      this.token = token
      if (import.meta.client) {
        if (token) {
          localStorage.setItem(TOKEN_KEY, token)
        } else {
          localStorage.removeItem(TOKEN_KEY)
        }
      }
    },

    async fetchUser() {
      if (!this.token) {
        return
      }

      const config = useRuntimeConfig()

      try {
        this.user = await $fetch<User>(`${config.public.apiBase}/user`, {
          headers: {
            Authorization: `Bearer ${this.token}`,
            Accept: 'application/json',
          },
        })
      } catch {
        this.logout()
      }
    },

    async login(credentials: LoginCredentials) {
      const config = useRuntimeConfig()
      this.loading = true
      this.error = null

      try {
        const response = await $fetch<AuthResponse>(`${config.public.apiBase}/login`, {
          method: 'POST',
          body: credentials,
          headers: {
            Accept: 'application/json',
          },
        })

        this.user = response.user
        this.setToken(response.token)
      } catch (error: unknown) {
        this.error = this.extractErrorMessage(error)
        throw error
      } finally {
        this.loading = false
      }
    },

    async register(credentials: RegisterCredentials) {
      const config = useRuntimeConfig()
      this.loading = true
      this.error = null

      try {
        const response = await $fetch<AuthResponse>(`${config.public.apiBase}/register`, {
          method: 'POST',
          body: credentials,
          headers: {
            Accept: 'application/json',
          },
        })

        this.user = response.user
        this.setToken(response.token)
      } catch (error: unknown) {
        this.error = this.extractErrorMessage(error)
        throw error
      } finally {
        this.loading = false
      }
    },

    async forgotPassword(payload: ForgotPasswordPayload) {
      const config = useRuntimeConfig()
      this.loading = true
      this.error = null

      try {
        return await $fetch<MessageResponse>(`${config.public.apiBase}/forgot-password`, {
          method: 'POST',
          body: payload,
          headers: {
            Accept: 'application/json',
          },
        })
      } catch (error: unknown) {
        this.error = this.extractErrorMessage(error)
        throw error
      } finally {
        this.loading = false
      }
    },

    async resetPassword(payload: ResetPasswordPayload) {
      const config = useRuntimeConfig()
      this.loading = true
      this.error = null

      try {
        const response = await $fetch<AuthResponse & MessageResponse>(`${config.public.apiBase}/reset-password`, {
          method: 'POST',
          body: payload,
          headers: {
            Accept: 'application/json',
          },
        })

        this.user = response.user
        this.setToken(response.token)

        return response
      } catch (error: unknown) {
        this.error = this.extractErrorMessage(error)
        throw error
      } finally {
        this.loading = false
      }
    },

    async logout() {
      const config = useRuntimeConfig()

      if (this.token) {
        try {
          await $fetch(`${config.public.apiBase}/logout`, {
            method: 'POST',
            headers: {
              Authorization: `Bearer ${this.token}`,
              Accept: 'application/json',
            },
          })
        } catch {
          // Ignorar errores al cerrar sesión
        }
      }

      this.user = null
      this.setToken(null)
      this.error = null
    },

    extractErrorMessage(error: unknown): string {
      if (typeof error === 'object' && error !== null && 'data' in error) {
        const data = (error as { data?: { message?: string; errors?: Record<string, string[]> } }).data
        if (data?.errors) {
          const firstError = Object.values(data.errors)[0]
          if (firstError?.[0]) {
            return firstError[0]
          }
        }
        if (data?.message) {
          return data.message
        }
      }

      return 'Ocurrió un error inesperado'
    },
  },
})
