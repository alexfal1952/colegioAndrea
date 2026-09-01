<script setup lang="ts">
import type { DiaCalendario } from '~/types/asistencia'

definePageMeta({ layout: 'admin', middleware: 'auth' })

const { api } = useApi()
const catalogos = useCatalogosStore()

const periodoId = ref<number | ''>('')
const anio = ref(new Date().getFullYear())
const mes = ref(new Date().getMonth() + 1)
const dias = ref<DiaCalendario[]>([])

const nombresMes = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre']

function claseDia(tipo: string): string {
  const map: Record<string, string> = {
    lectivo: 'bg-success-subtle',
    feriado: 'bg-danger-subtle',
    vacacion: 'bg-info-subtle',
    suspension: 'bg-warning-subtle',
    fin_semana: 'bg-secondary-subtle',
    fuera_periodo: 'bg-light text-muted',
  }
  return map[tipo] || ''
}

async function load() {
  if (!periodoId.value) { dias.value = []; return }
  const res = await api<{ data: DiaCalendario[] }>(
    `/calendario-escolar?periodo_escolar_id=${periodoId.value}&anio=${anio.value}&mes=${mes.value}`
  )
  dias.value = res.data
}

onMounted(async () => {
  await catalogos.load()
  const activo = catalogos.periodos.find((p) => p.estado === 'activo')
  if (activo) { periodoId.value = activo.id; anio.value = activo.anio }
  await load()
})
watch([periodoId, anio, mes], load)
</script>

<template>
  <LteAppContent title="Calendario escolar" :breadcrumbs="[{ label: 'Inicio', href: '/dashboard' }, { label: 'Calendario escolar' }]">
    <LteCard title="Vista mensual" icon="bi-calendar3" theme="primary" variant="outline">
      <div class="row g-3 mb-4">
        <div class="col-md-4">
          <LteSelect v-model="periodoId" label="Período">
            <option v-for="p in catalogos.periodos" :key="p.id" :value="p.id">{{ p.anio }} — {{ p.nombre }}</option>
          </LteSelect>
        </div>
        <div class="col-md-2"><LteInput v-model="anio" type="number" label="Año" /></div>
        <div class="col-md-3">
          <LteSelect v-model="mes" label="Mes">
            <option v-for="(n, i) in nombresMes" :key="i" :value="i + 1">{{ n }}</option>
          </LteSelect>
        </div>
      </div>
      <h5 class="mb-3">{{ nombresMes[mes - 1] }} {{ anio }}</h5>
      <div class="d-flex flex-wrap gap-2">
        <div
          v-for="d in dias"
          :key="d.fecha"
          class="border rounded p-2 text-center"
          style="width: 72px"
          :class="claseDia(d.tipo_dia)"
          :title="d.motivo || d.tipo_dia_label"
        >
          <div class="fw-bold">{{ d.fecha.slice(-2) }}</div>
          <small class="d-block text-truncate" style="font-size: 0.65rem">{{ d.tipo_dia_label }}</small>
        </div>
      </div>
      <div class="mt-3 d-flex flex-wrap gap-2">
        <span class="badge bg-success-subtle text-dark">Lectivo</span>
        <span class="badge bg-danger-subtle text-dark">Feriado</span>
        <span class="badge bg-info-subtle text-dark">Vacaciones</span>
        <span class="badge bg-warning-subtle text-dark">Suspensión</span>
        <span class="badge bg-secondary-subtle text-dark">Fin de semana</span>
      </div>
    </LteCard>
  </LteAppContent>
</template>
