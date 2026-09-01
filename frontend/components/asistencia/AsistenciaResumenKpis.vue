<script setup lang="ts">
import { colorPorcentajeAsistencia } from '~/utils/asistencia'

defineProps<{
  diasLectivos?: number
  presentes?: number
  ausentes?: number
  atrasos?: number
  porcentaje?: number
  totalAlumnos?: number
  promedio?: number
}>()
</script>

<template>
  <div class="row g-3 mb-4">
    <div v-if="porcentaje !== undefined" class="col-6 col-md-3">
      <div class="border rounded p-3 h-100 text-center">
        <div class="text-muted small">Asistencia</div>
        <div :class="`fs-3 fw-bold text-${colorPorcentajeAsistencia(porcentaje)}`">{{ porcentaje }}%</div>
      </div>
    </div>
    <div v-if="promedio !== undefined" class="col-6 col-md-3">
      <div class="border rounded p-3 h-100 text-center">
        <div class="text-muted small">Promedio curso</div>
        <div :class="`fs-3 fw-bold text-${colorPorcentajeAsistencia(promedio)}`">{{ promedio }}%</div>
      </div>
    </div>
    <div v-if="diasLectivos !== undefined" class="col-6 col-md-2">
      <div class="border rounded p-3 h-100 text-center">
        <div class="text-muted small">Días lectivos</div>
        <div class="fs-4 fw-semibold">{{ diasLectivos }}</div>
      </div>
    </div>
    <div v-if="presentes !== undefined" class="col-6 col-md-2">
      <div class="border rounded p-3 h-100 text-center border-success bg-success-subtle">
        <div class="text-muted small">Presentes</div>
        <div class="fs-4 fw-semibold text-success">{{ presentes }}</div>
      </div>
    </div>
    <div v-if="ausentes !== undefined" class="col-6 col-md-2">
      <div class="border rounded p-3 h-100 text-center border-danger bg-danger-subtle">
        <div class="text-muted small">Ausentes</div>
        <div class="fs-4 fw-semibold text-danger">{{ ausentes }}</div>
      </div>
    </div>
    <div v-if="atrasos !== undefined" class="col-6 col-md-2">
      <div class="border rounded p-3 h-100 text-center border-warning bg-warning-subtle">
        <div class="text-muted small">Atrasos</div>
        <div class="fs-4 fw-semibold text-warning">{{ atrasos }}</div>
      </div>
    </div>
    <div v-if="totalAlumnos !== undefined" class="col-6 col-md-2">
      <div class="border rounded p-3 h-100 text-center">
        <div class="text-muted small">Alumnos</div>
        <div class="fs-4 fw-semibold">{{ totalAlumnos }}</div>
      </div>
    </div>
  </div>
</template>
