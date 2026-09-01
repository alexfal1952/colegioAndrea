<script setup lang="ts">
import type { EstadoAsistencia } from '~/types/asistencia'

defineProps<{
  estados: EstadoAsistencia[]
  modelValue: number
  compact?: boolean
}>()

defineEmits<{
  'update:modelValue': [value: number]
}>()
</script>

<template>
  <div class="d-flex flex-wrap gap-1">
    <button
      v-for="e in estados"
      :key="e.id"
      type="button"
      class="btn btn-sm border asistencia-estado-chip"
      :class="modelValue === e.id ? 'fw-bold shadow-sm' : 'btn-outline-secondary'"
      :style="modelValue === e.id ? { backgroundColor: e.color || '#6c757d', borderColor: e.color || '#6c757d', color: '#fff' } : {}"
      :title="e.nombre"
      @click="$emit('update:modelValue', e.id)"
    >
      <span class="d-none d-md-inline">{{ e.nombre }}</span>
      <span class="d-md-none">{{ e.nombre.slice(0, 3) }}</span>
    </button>
  </div>
</template>
