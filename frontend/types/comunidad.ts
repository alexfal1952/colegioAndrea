export type TipoDocumento = 'rut' | 'pasaporte' | 'dni' | 'otro'
export type EstadoMatricula = 'activa' | 'retirada' | 'traslado' | 'egresado'
export type EstadoAsignatura = 'activa' | 'inactiva'
export type EstadoCurso = 'activo' | 'inactivo' | 'cerrado'
export type JornadaCurso = 'manana' | 'tarde' | 'completa'
export type RolAsignacionDocente = 'titular' | 'reemplazante' | 'apoyo' | 'co-docente'
export type EstadoAsignacionDocente = 'activa' | 'inactiva' | 'finalizada'

export interface Pais {
  codigo: string
  nombre: string
}

export interface Nivel {
  id: number
  nombre: string
  codigo: string
  orden: number
}

export interface Persona {
  id: number
  tipo_documento: TipoDocumento
  tipo_documento_label: string
  numero_documento: string
  numero_documento_formateado: string
  pais_documento?: string | null
  pais?: { codigo: string; nombre: string } | null
  nombres: string
  apellido_paterno: string
  apellido_materno?: string | null
  nombre_completo: string
  fecha_nacimiento?: string | null
  email?: string | null
  telefono?: string | null
  activo: boolean
}

export interface PersonaPayload {
  tipo_documento: TipoDocumento
  numero_documento: string
  pais_documento?: string | null
  nombres: string
  apellido_paterno: string
  apellido_materno?: string | null
  fecha_nacimiento?: string | null
  email?: string | null
  telefono?: string | null
  activo?: boolean
}

export interface Alumno {
  id: number
  codigo_interno?: string | null
  observaciones?: string | null
  activo: boolean
  persona?: Persona
  matriculas?: Matricula[]
}

export interface Profesor {
  id: number
  codigo_interno?: string | null
  observaciones?: string | null
  activo: boolean
  persona?: Persona
}

export interface Curso {
  id: number
  periodo_escolar_id: number
  nivel_id: number
  paralelo: string
  nombre?: string | null
  nombre_display: string
  jornada?: JornadaCurso | null
  jornada_label?: string | null
  estado: EstadoCurso
  estado_label: string
  cupo_maximo?: number | null
  profesor_jefe_id?: number | null
  nivel?: { id: number; nombre: string; codigo: string }
  periodo_escolar?: { id: number; nombre: string; anio: number }
  profesor_jefe?: Profesor
  matriculas_count?: number
  matriculas?: Matricula[]
  asignaturas?: CursoAsignatura[]
}

export interface Asignatura {
  id: number
  codigo: string
  nombre: string
  descripcion?: string | null
  estado: EstadoAsignatura
  estado_label: string
}

export interface AsignacionDocente {
  id: number
  profesor_id: number
  rol: RolAsignacionDocente
  rol_label: string
  estado: EstadoAsignacionDocente
  estado_label: string
  fecha_inicio: string
  fecha_termino?: string | null
  observaciones?: string | null
  profesor?: Profesor
}

export interface CursoAsignatura {
  id: number
  curso_id: number
  asignatura_id: number
  estado: EstadoAsignatura
  estado_label: string
  horas_semanales?: number | null
  asignatura?: Asignatura
  docentes?: AsignacionDocente[]
  profesores_label?: string
}

export interface Matricula {
  id: number
  periodo_escolar_id: number
  curso_id: number
  alumno_id: number
  numero_matricula?: string | null
  fecha_matricula: string
  estado: EstadoMatricula
  estado_label: string
  observaciones?: string | null
  alumno?: Alumno
  curso?: Curso
  periodo_escolar?: { id: number; nombre: string; anio: number }
}

export interface PaginatedResponse<T> {
  data: T[]
  meta?: { current_page: number; last_page: number; total: number }
}

export interface PeriodoEscolarOption {
  id: number
  nombre: string
  anio: number
  estado: string
  estado_label: string
}
