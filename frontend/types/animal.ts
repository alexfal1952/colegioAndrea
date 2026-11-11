export type SexoAnimal =
  | 'macho'
  | 'hembra'
  | 'desconocido'

export type EstadoAnimal =
  | 'activo'
  | 'adoptado'
  | 'en_tratamiento'
  | 'inactivo'

export interface Animal {
  id: number
  codigo: string
  nombre: string
  especie: string
  raza: string | null
  sexo: SexoAnimal
  fecha_nacimiento: string | null
  peso_kg: number | null
  estado: EstadoAnimal
  observaciones: string | null
  created_at: string
  updated_at: string
}

export interface AnimalPayload {
  codigo: string
  nombre: string
  especie: string
  raza: string | null
  sexo: SexoAnimal
  fecha_nacimiento: string | null
  peso_kg: number | null
  estado: EstadoAnimal
  observaciones: string | null
}