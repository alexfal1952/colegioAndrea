import type { ValidationErrors } from '~/types/periodo-escolar'

export function useApi() {
  const config = useRuntimeConfig()
  const auth = useAuthStore()

  async function api<T>(path: string, options: Parameters<typeof $fetch>[1] = {}): Promise<T> {
    return $fetch<T>(`${config.public.apiBase}${path}`, {
      ...options,
      headers: {
        Accept: 'application/json',
        ...(auth.token ? { Authorization: `Bearer ${auth.token}` } : {}),
        ...(options.headers as Record<string, string> | undefined),
      },
    })
  }

  function extractValidationErrors(error: unknown): Record<string, string> {
    if (typeof error === 'object' && error !== null && 'data' in error) {
      const data = (error as { data?: ValidationErrors }).data
      if (data?.errors) {
        const mapped: Record<string, string> = {}
        for (const [key, messages] of Object.entries(data.errors)) {
          if (messages[0]) {
            mapped[key] = messages[0]
          }
        }
        return mapped
      }
    }
    return {}
  }

  function extractErrorMessage(error: unknown, fallback = 'Ocurrió un error inesperado'): string {
    if (typeof error === 'object' && error !== null && 'data' in error) {
      const data = (error as { data?: ValidationErrors }).data
      if (data?.errors) {
        const first = Object.values(data.errors)[0]
        if (first?.[0]) {
          return first[0]
        }
      }
      if (data?.message) {
        return data.message
      }
    }
    return fallback
  }

  return { api, extractValidationErrors, extractErrorMessage }
}
