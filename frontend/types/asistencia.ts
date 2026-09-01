export type TipoFeriado = 'nacional' | 'regional' | 'comunal' | 'escolar' | 'otro'
export type TipoDiaCalendario = 'lectivo' | 'feriado' | 'vacacion' | 'suspension' | 'fin_semana' | 'fuera_periodo'
export type EstadoCalendario = 'activo' | 'inactivo'
export type EstadoVacacion = 'activa' | 'inactiva'

export interface Feriado {
  id: number
  periodo_escolar_id?: number | null
  nombre: string
  tipo: TipoFeriado
  tipo_label: string
  recurrente: boolean
  fecha?: string | null
  mes?: number | null
  dia?: number | null
  fecha_display: string
  descripcion?: string | null
  estado: EstadoCalendario
  estado_label: string
}

export interface Vacacion {
  id: number
  periodo_escolar_id: number
  nombre: string
  fecha_inicio: string
  fecha_fin: string
  descripcion?: string | null
  estado: EstadoVacacion
  estado_label: string
}

export interface SuspensionClases {
  id: number
  periodo_escolar_id: number
  curso_id?: number | null
  fecha: string
  nombre: string
  motivo?: string | null
  tipo: string
  tipo_label: string
  alcance: 'curso' | 'establecimiento'
  alcance_label: string
  estado: EstadoVacacion
  estado_label: string
  curso?: { id: number; nombre_display: string }
}

export interface EstadoAsistencia {
  id: number
  codigo: string
  nombre: string
  descripcion?: string | null
  cuenta_como_presente: boolean
  cuenta_como_ausencia: boolean
  cuenta_como_atraso: boolean
  cuenta_como_justificado: boolean
  color?: string | null
  orden: number
  activo: boolean
  es_sistema: boolean
}

export interface Asistencia {
  id: number
  matricula_id: number
  fecha: string
  estado_asistencia?: EstadoAsistencia
  observacion?: string | null
}

export interface DiaCalendario {
  fecha: string
  dia_semana: number
  tipo_dia: TipoDiaCalendario
  tipo_dia_label: string
  permite_registrar_asistencia: boolean
  motivo?: string | null
}

export interface ValidacionFecha {
  tipo_dia: TipoDiaCalendario
  tipo_dia_label: string
  permite_registrar_asistencia: boolean
  motivo?: string | null
}

export interface AlumnoAsistenciaFila {
  matricula: { id: number; alumno?: { persona?: { nombre_completo: string } } }
  asistencia?: Asistencia | null
  estado_asistencia?: EstadoAsistencia | null
}

export interface ResumenAsistencia {
  dias_lectivos: number
  presentes: number
  ausentes: number
  atrasos: number
  justificados: number
  porcentaje_asistencia: number
  detalle?: { fecha: string; estado: string; codigo: string; observacion?: string }[]
}
