<script setup lang="ts">
const {
  catalogos,
  periodoId,
  cursoId,
  cursos,
  loadingCursos,
} = useAsistenciaContext()

const { showFecha = false, showCurso = true } = defineProps<{
  showFecha?: boolean
  showCurso?: boolean
}>()

const fecha = defineModel<string>('fecha', { default: '' })
</script>

<template>
  <div class="row g-3 align-items-end">
    <div class="col-12 col-md-4">
      <LteSelect v-model="periodoId" label="Período escolar">
        <option value="">Seleccione período</option>
        <option v-for="p in catalogos.periodos" :key="p.id" :value="p.id">
          {{ p.anio }} — {{ p.nombre }}
          <template v-if="p.estado === 'activo'"> (activo)</template>
        </option>
      </LteSelect>
    </div>
    <div v-if="showCurso" class="col-12 col-md-4">
      <LteSelect
        v-model="cursoId"
        label="Curso"
        :disabled="!periodoId || loadingCursos"
      >
        <option value="">{{ loadingCursos ? 'Cargando cursos…' : 'Seleccione curso' }}</option>
        <option v-for="c in cursos" :key="c.id" :value="c.id">{{ c.nombre_display }}</option>
      </LteSelect>
      <div v-if="periodoId && !loadingCursos && !cursos.length" class="form-text text-warning">
        No hay cursos en este período.
      </div>
    </div>
    <div v-if="showFecha" class="col-12 col-md-4">
      <LteInput v-model="fecha" type="date" label="Fecha" />
    </div>
    <slot />
  </div>
</template>
