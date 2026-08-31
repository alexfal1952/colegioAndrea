import { defineStore } from 'pinia'
import type {
  Funcionario,
  FuncionarioCreateResponse,
  FuncionarioPayload,
  PasswordResetResponse,
  Role,
} from '~/types/funcionario'

export const useFuncionariosStore = defineStore('funcionarios', {
  state: () => ({
    funcionarios: [] as Funcionario[],
    roles: [] as Role[],
    loading: false,
    saving: false,
    resettingId: null as number | null,
    error: null as string | null,
    success: null as string | null,
    resetUrl: null as string | null,
  }),

  actions: {
    authHeaders() {
      const auth = useAuthStore()
      return {
        Authorization: `Bearer ${auth.token}`,
        Accept: 'application/json',
      }
    },

    clearMessages() {
      this.error = null
      this.success = null
      this.resetUrl = null
    },

    async fetchAll() {
      const config = useRuntimeConfig()
      this.loading = true
      this.error = null

      try {
        this.funcionarios = await $fetch<Funcionario[]>(`${config.public.apiBase}/funcionarios`, {
          headers: this.authHeaders(),
        })
      } catch (error: unknown) {
        this.error = this.extractErrorMessage(error)
        throw error
      } finally {
        this.loading = false
      }
    },

    async fetchRoles() {
      const config = useRuntimeConfig()

      try {
        this.roles = await $fetch<Role[]>(`${config.public.apiBase}/roles`, {
          headers: this.authHeaders(),
        })
      } catch (error: unknown) {
        this.error = this.extractErrorMessage(error)
        throw error
      }
    },

    async create(payload: FuncionarioPayload) {
      const config = useRuntimeConfig()
      this.saving = true
      this.clearMessages()

      try {
        const response = await $fetch<FuncionarioCreateResponse>(`${config.public.apiBase}/funcionarios`, {
          method: 'POST',
          body: payload,
          headers: this.authHeaders(),
        })

        this.funcionarios.push(response.funcionario)
        this.funcionarios.sort((a, b) => a.name.localeCompare(b.name))
        this.success = response.message
        this.resetUrl = response.reset_url ?? null

        return response
      } catch (error: unknown) {
        this.error = this.extractErrorMessage(error)
        throw error
      } finally {
        this.saving = false
      }
    },

    async update(id: number, payload: FuncionarioPayload) {
      const config = useRuntimeConfig()
      this.saving = true
      this.clearMessages()

      try {
        const updated = await $fetch<Funcionario>(`${config.public.apiBase}/funcionarios/${id}`, {
          method: 'PUT',
          body: {
            name: payload.name,
            email: payload.email,
            role_id: payload.role_id,
          },
          headers: this.authHeaders(),
        })

        const index = this.funcionarios.findIndex(item => item.id === id)
        if (index !== -1) {
          this.funcionarios[index] = updated
        }

        this.success = 'Funcionario actualizado correctamente.'
        return updated
      } catch (error: unknown) {
        this.error = this.extractErrorMessage(error)
        throw error
      } finally {
        this.saving = false
      }
    },

    async remove(id: number) {
      const config = useRuntimeConfig()
      this.saving = true
      this.clearMessages()

      try {
        await $fetch(`${config.public.apiBase}/funcionarios/${id}`, {
          method: 'DELETE',
          headers: this.authHeaders(),
        })
        this.funcionarios = this.funcionarios.filter(item => item.id !== id)
        this.success = 'Funcionario eliminado correctamente.'
      } catch (error: unknown) {
        this.error = this.extractErrorMessage(error)
        throw error
      } finally {
        this.saving = false
      }
    },

    async sendPasswordReset(id: number) {
      const config = useRuntimeConfig()
      this.resettingId = id
      this.clearMessages()

      try {
        const response = await $fetch<PasswordResetResponse>(
          `${config.public.apiBase}/funcionarios/${id}/reset-password`,
          {
            method: 'POST',
            headers: this.authHeaders(),
          },
        )
        this.success = response.message
        this.resetUrl = response.reset_url ?? null
        return response
      } catch (error: unknown) {
        this.error = this.extractErrorMessage(error)
        throw error
      } finally {
        this.resettingId = null
      }
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
