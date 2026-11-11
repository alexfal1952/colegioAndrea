import type { Pez, PezPayload } from '~/types/pez'

function requestOptions(method?: 'POST' | 'PUT' | 'DELETE', body?: PezPayload) {
  const auth = useAuthStore()

  return {
    method,
    body,
    headers: {
      Authorization: `Bearer ${auth.token}`,
      Accept: 'application/json',
    },
  }
}

function endpoint(path = '') {
  const config = useRuntimeConfig()

  return `${config.public.apiBase}/peces${path}`
}

export const pezService = {
  listar(): Promise<Pez[]> {
    return $fetch<Pez[]>(endpoint(), requestOptions())
  },

  crear(payload: PezPayload): Promise<Pez> {
    return $fetch<Pez>(endpoint(), requestOptions('POST', payload))
  },

  actualizar(id: number, payload: PezPayload): Promise<Pez> {
    return $fetch<Pez>(endpoint(`/${id}`), requestOptions('PUT', payload))
  },

  eliminar(id: number): Promise<void> {
    return $fetch<void>(endpoint(`/${id}`), requestOptions('DELETE'))
  },
}
