const MESES = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'] as const

const DIAS = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'] as const

export function nombreMes(m: number): string {
  return MESES[m - 1] ?? ''
}

export function formatFechaLarga(fecha: string): string {
  const d = new Date(`${fecha}T12:00:00`)
  if (Number.isNaN(d.getTime())) return fecha
  return `${DIAS[d.getDay()]}, ${d.getDate()} ${nombreMes(d.getMonth() + 1)} ${d.getFullYear()}`
}

export function formatFechaCorta(fecha: string): string {
  const [y, m, day] = fecha.split('-')
  return `${day}/${m}/${y}`
}

export function addDays(fecha: string, delta: number): string {
  const d = new Date(`${fecha}T12:00:00`)
  d.setDate(d.getDate() + delta)
  return d.toISOString().slice(0, 10)
}

export function colorPorcentajeAsistencia(p: number): string {
  if (p >= 90) return 'success'
  if (p >= 75) return 'warning'
  return 'danger'
}

export function temaDiaCalendario(tipo: string): 'success' | 'danger' | 'info' | 'warning' | 'secondary' | 'light' {
  const map: Record<string, 'success' | 'danger' | 'info' | 'warning' | 'secondary' | 'light'> = {
    lectivo: 'success',
    feriado: 'danger',
    vacacion: 'info',
    suspension: 'warning',
    fin_semana: 'secondary',
    fuera_periodo: 'light',
  }
  return map[tipo] ?? 'light'
}

export { MESES, DIAS }
