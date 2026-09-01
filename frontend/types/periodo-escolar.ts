export type TipoPeriodoEscolar = 'anual' | 'semestral' | 'trimestral'
export type EstadoPeriodoEscolar = 'borrador' | 'activo' | 'cerrado'

export interface SubperiodoInput {
  nombre: string
  numero: number
  fecha_inicio: string
  fecha_termino: string
}

export interface PeriodoEscolarAuditoria {
  created_by?: { id: number; name: string; email?: string | null }
  updated_by?: { id: number; name: string; email?: string | null } | null
  created_at?: string
  updated_at?: string
  closed_at?: string | null
}

export interface PeriodoEscolar {
  id: number
  nombre: string
  anio: number
  tipo: TipoPeriodoEscolar
  tipo_label: string
  subtipo?: string | null
  numero_subperiodo?: number | null
  fecha_inicio: string
  fecha_termino: string
  estado: EstadoPeriodoEscolar
  estado_label: string
  descripcion?: string | null
  subperiodos?: PeriodoEscolar[]
  subperiodos_count?: number
  auditoria?: PeriodoEscolarAuditoria
}

export interface PeriodoEscolarPayload {
  nombre: string
  anio: number
  tipo: TipoPeriodoEscolar
  fecha_inicio: string
  fecha_termino: string
  estado?: EstadoPeriodoEscolar
  descripcion?: string | null
  subperiodos?: SubperiodoInput[]
}

export interface PeriodoEscolarFilters {
  anio?: number | ''
  tipo?: TipoPeriodoEscolar | ''
  estado?: EstadoPeriodoEscolar | ''
  page?: number
}

export interface PaginatedResponse<T> {
  data: T[]
  meta?: {
    current_page: number
    last_page: number
    per_page: number
    total: number
  }
  links?: Record<string, string | null>
}

export interface ValidationErrors {
  message?: string
  errors?: Record<string, string[]>
}
