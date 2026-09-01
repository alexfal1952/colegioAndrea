<script setup lang="ts">
import type { ValidacionFecha } from '~/types/asistencia'
import { formatFechaLarga, temaDiaCalendario } from '~/utils/asistencia'

defineProps<{
  validacion: ValidacionFecha | null
  fecha?: string
}>()
</script>

<template>
  <div v-if="validacion" class="mb-3">
    <div
      class="d-flex align-items-start gap-3 p-3 rounded border"
      :class="validacion.permite_registrar_asistencia ? 'border-success bg-success-subtle' : `border-${temaDiaCalendario(validacion.tipo_dia)} bg-${temaDiaCalendario(validacion.tipo_dia)}-subtle`"
    >
      <i
        class="bi fs-4"
        :class="validacion.permite_registrar_asistencia ? 'bi-check-circle-fill text-success' : 'bi-exclamation-triangle-fill'"
      />
      <div>
        <div class="fw-semibold">
          {{ fecha ? formatFechaLarga(fecha) : 'Fecha seleccionada' }}
        </div>
        <div class="small">{{ validacion.tipo_dia_label }}</div>
        <div v-if="validacion.motivo" class="small text-muted mt-1">{{ validacion.motivo }}</div>
        <div v-if="!validacion.permite_registrar_asistencia" class="small fw-semibold mt-2">
          No corresponde registrar asistencia en este día.
        </div>
        <div v-else class="small text-success mt-1">Puedes registrar la asistencia del curso.</div>
      </div>
    </div>
  </div>
</template>
