import { defineStore } from 'pinia'
import type {
  PaginatedResponse,
  PeriodoEscolar,
  PeriodoEscolarFilters,
  PeriodoEscolarPayload,
} from '~/types/periodo-escolar'

export const usePeriodosEscolaresStore = defineStore('periodosEscolares', {
  state: () => ({
    periodos: [] as PeriodoEscolar[],
    periodoActual: null as PeriodoEscolar | null,
    meta: null as PaginatedResponse<PeriodoEscolar>['meta'] | null,
    loading: false,
    saving: false,
    error: null as string | null,
    fieldErrors: {} as Record<string, string>,
  }),

  actions: {
    async fetchPeriodos(filters: PeriodoEscolarFilters = {}) {
      const { api, extractErrorMessage } = useApi()
      this.loading = true
      this.error = null

      try {
        const query = new URLSearchParams()
        if (filters.anio) query.set('anio', String(filters.anio))
        if (filters.tipo) query.set('tipo', filters.tipo)
        if (filters.estado) query.set('estado', filters.estado)
        if (filters.page) query.set('page', String(filters.page))

        const suffix = query.toString() ? `?${query.toString()}` : ''
        const response = await api<PaginatedResponse<PeriodoEscolar>>(`/periodos-escolares${suffix}`)

        this.periodos = response.data
        this.meta = response.meta ?? null
      } catch (error) {
        this.error = extractErrorMessage(error)
        throw error
      } finally {
        this.loading = false
      }
    },

    async fetchPeriodo(id: number) {
      const { api, extractErrorMessage } = useApi()
      this.loading = true
      this.error = null

      try {
        const response = await api<{ data: PeriodoEscolar }>(`/periodos-escolares/${id}`)
        this.periodoActual = response.data
        return response.data
      } catch (error) {
        this.error = extractErrorMessage(error)
        throw error
      } finally {
        this.loading = false
      }
    },

    async createPeriodo(payload: PeriodoEscolarPayload) {
      const { api, extractErrorMessage, extractValidationErrors } = useApi()
      this.saving = true
      this.error = null
      this.fieldErrors = {}

      try {
        const response = await api<{ data: PeriodoEscolar }>('/periodos-escolares', {
          method: 'POST',
          body: payload,
        })
        return response.data
      } catch (error) {
        this.fieldErrors = extractValidationErrors(error)
        this.error = extractErrorMessage(error)
        throw error
      } finally {
        this.saving = false
      }
    },

    async updatePeriodo(id: number, payload: Partial<PeriodoEscolarPayload>) {
      const { api, extractErrorMessage, extractValidationErrors } = useApi()
      this.saving = true
      this.error = null
      this.fieldErrors = {}

      try {
        const response = await api<{ data: PeriodoEscolar }>(`/periodos-escolares/${id}`, {
          method: 'PUT',
          body: payload,
        })
        return response.data
      } catch (error) {
        this.fieldErrors = extractValidationErrors(error)
        this.error = extractErrorMessage(error)
        throw error
      } finally {
        this.saving = false
      }
    },

    async activarPeriodo(id: number) {
      const { api, extractErrorMessage } = useApi()
      this.saving = true
      this.error = null

      try {
        const response = await api<{ data: PeriodoEscolar }>(`/periodos-escolares/${id}/activar`, {
          method: 'PATCH',
        })
        return response.data
      } catch (error) {
        this.error = extractErrorMessage(error)
        throw error
      } finally {
        this.saving = false
      }
    },

    async cerrarPeriodo(id: number) {
      const { api, extractErrorMessage } = useApi()
      this.saving = true
      this.error = null

      try {
        const response = await api<{ data: PeriodoEscolar }>(`/periodos-escolares/${id}/cerrar`, {
          method: 'PATCH',
        })
        return response.data
      } catch (error) {
        this.error = extractErrorMessage(error)
        throw error
      } finally {
        this.saving = false
      }
    },

    async deletePeriodo(id: number) {
      const { api, extractErrorMessage } = useApi()
      this.saving = true
      this.error = null

      try {
        await api(`/periodos-escolares/${id}`, { method: 'DELETE' })
      } catch (error) {
        this.error = extractErrorMessage(error)
        throw error
      } finally {
        this.saving = false
      }
    },
  },
})
