export type TipoAgua = 'dulce' | 'salada'
export type EstadoPez = 'activo' | 'inactivo'

export interface Pez {
  id: number
  codigo: string
  nombre: string
  especie: string
  tipo_agua: TipoAgua
  longitud_cm: number | null
  estado: EstadoPez
  created_at: string
  updated_at: string
}

export interface PezPayload {
  codigo: string
  nombre: string
  especie: string
  tipo_agua: TipoAgua
  longitud_cm: number | null
  estado: EstadoPez
}
