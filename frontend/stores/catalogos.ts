import { defineStore } from 'pinia'
import type { Nivel, Pais, PeriodoEscolarOption } from '~/types/comunidad'
import type { PeriodoEscolar } from '~/types/periodo-escolar'

export const useCatalogosStore = defineStore('catalogos', {
  state: () => ({
    paises: [] as Pais[],
    niveles: [] as Nivel[],
    periodos: [] as PeriodoEscolarOption[],
    loaded: false,
  }),

  actions: {
    async load() {
      if (this.loaded) return
      const { api } = useApi()

      const [paisesRes, nivelesRes, periodosRes] = await Promise.all([
        api<{ data: Pais[] }>('/catalogos/paises'),
        api<{ data: Nivel[] }>('/catalogos/niveles'),
        api<{ data: PeriodoEscolar[] }>('/periodos-escolares?per_page=100'),
      ])

      this.paises = paisesRes.data
      this.niveles = nivelesRes.data
      this.periodos = periodosRes.data.map((p) => ({
        id: p.id,
        nombre: p.nombre,
        anio: p.anio,
        estado: p.estado,
        estado_label: p.estado_label,
      }))
      this.loaded = true
    },
  },
})
