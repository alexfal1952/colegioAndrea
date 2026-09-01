import type { Curso } from '~/types/comunidad'

function normalizeId(value: unknown): number | '' {
  if (value === '' || value === null || value === undefined) return ''
  const n = Number(value)
  return Number.isFinite(n) ? n : ''
}

let watchersReady = false

export function useAsistenciaContext() {
  const catalogos = useCatalogosStore()
  const { api } = useApi()

  const periodoId = useState<number | ''>('asistencia-periodo-id', () => '')
  const cursoId = useState<number | ''>('asistencia-curso-id', () => '')
  const cursos = useState<Curso[]>('asistencia-cursos', () => [])
  const loadingCursos = ref(false)
  const ready = ref(false)

  const periodoActivo = computed(() => {
    const id = normalizeId(periodoId.value)
    return catalogos.periodos.find((p) => p.id === id) ?? null
  })

  const cursoActivo = computed(() => {
    const id = normalizeId(cursoId.value)
    return cursos.value.find((c) => c.id === id) ?? null
  })

  async function loadCursos() {
    const pid = normalizeId(periodoId.value)
    if (pid !== periodoId.value) {
      periodoId.value = pid
    }

    if (!pid) {
      cursos.value = []
      cursoId.value = ''
      return
    }

    loadingCursos.value = true
    try {
      const res = await api<{ data: Curso[] }>(`/cursos?periodo_escolar_id=${pid}`)
      cursos.value = res.data
      const cid = normalizeId(cursoId.value)
      if (cid && !cursos.value.some((c) => c.id === cid)) {
        cursoId.value = ''
      } else if (cid !== cursoId.value) {
        cursoId.value = cid
      }
    } finally {
      loadingCursos.value = false
    }
  }

  async function initContext() {
    await catalogos.load()
    if (!normalizeId(periodoId.value)) {
      const activo = catalogos.periodos.find((p) => p.estado === 'activo')
      if (activo) periodoId.value = activo.id
    }
    await loadCursos()
    ready.value = true
  }

  if (import.meta.client && !watchersReady) {
    watchersReady = true
    watch(periodoId, loadCursos)
    watch(cursoId, (value) => {
      const normalized = normalizeId(value)
      if (value !== normalized) cursoId.value = normalized
    })
  }

  return {
    catalogos,
    periodoId,
    cursoId,
    cursos,
    loadingCursos,
    ready,
    periodoActivo,
    cursoActivo,
    initContext,
    loadCursos,
    normalizeId,
  }
}
