<script setup lang="ts">
const {
  periodoActivo,
  cursoActivo,
  initContext,
} = useAsistenciaContext()

onMounted(() => initContext())
</script>

<template>
  <LteAppContent
    title="Asistencia"
    :breadcrumbs="[{ label: 'Inicio', href: '/dashboard' }, { label: 'Asistencia' }]"
  >
    <p class="text-muted mb-4">
      Registra la asistencia diaria, consulta resúmenes y revisa el calendario escolar.
    </p>

    <div class="row g-4">
      <div class="col-md-6 col-lg-4">
        <NuxtLink to="/asistencia/registrar" class="text-decoration-none">
          <div class="card h-100 border-primary shadow-sm hover-lift">
            <div class="card-body">
              <div class="d-flex align-items-center gap-3 mb-2">
                <span class="rounded-circle bg-primary-subtle p-3">
                  <i class="bi bi-clipboard-check fs-4 text-primary" />
                </span>
                <div>
                  <h5 class="card-title mb-0 text-body">Registrar asistencia</h5>
                  <small class="text-muted">Acción principal</small>
                </div>
              </div>
              <p class="card-text small text-muted mb-0">
                Marca presente, ausente o atrasado para todo el curso en una fecha.
              </p>
            </div>
          </div>
        </NuxtLink>
      </div>

      <div class="col-md-6 col-lg-4">
        <NuxtLink to="/asistencia/resumen-curso" class="text-decoration-none">
          <div class="card h-100 shadow-sm hover-lift">
            <div class="card-body">
              <div class="d-flex align-items-center gap-3 mb-2">
                <span class="rounded-circle bg-info-subtle p-3">
                  <i class="bi bi-people-fill fs-4 text-info" />
                </span>
                <h5 class="card-title mb-0 text-body">Resumen del curso</h5>
              </div>
              <p class="card-text small text-muted mb-0">
                Promedio mensual y alumnos con baja asistencia.
              </p>
            </div>
          </div>
        </NuxtLink>
      </div>

      <div class="col-md-6 col-lg-4">
        <NuxtLink to="/asistencia/resumen-mensual" class="text-decoration-none">
          <div class="card h-100 shadow-sm hover-lift">
            <div class="card-body">
              <div class="d-flex align-items-center gap-3 mb-2">
                <span class="rounded-circle bg-success-subtle p-3">
                  <i class="bi bi-person-lines-fill fs-4 text-success" />
                </span>
                <h5 class="card-title mb-0 text-body">Resumen por alumno</h5>
              </div>
              <p class="card-text small text-muted mb-0">
                Detalle día a día y porcentaje mensual de un estudiante.
              </p>
            </div>
          </div>
        </NuxtLink>
      </div>

      <div class="col-md-6 col-lg-4">
        <NuxtLink to="/configuracion/calendario" class="text-decoration-none">
          <div class="card h-100 shadow-sm hover-lift">
            <div class="card-body">
              <div class="d-flex align-items-center gap-3 mb-2">
                <span class="rounded-circle bg-warning-subtle p-3">
                  <i class="bi bi-calendar3 fs-4 text-warning" />
                </span>
                <h5 class="card-title mb-0 text-body">Calendario escolar</h5>
              </div>
              <p class="card-text small text-muted mb-0">
                Feriados, vacaciones y días sin clases.
              </p>
            </div>
          </div>
        </NuxtLink>
      </div>
    </div>

    <LteCard v-if="periodoActivo" title="Contexto actual" icon="bi-info-circle" theme="secondary" variant="outline" class="mt-4">
      <p class="mb-0 small">
        Período activo: <strong>{{ periodoActivo.anio }} — {{ periodoActivo.nombre }}</strong>
        <span v-if="cursoActivo"> · Curso: <strong>{{ cursoActivo.nombre_display }}</strong></span>
      </p>
    </LteCard>
  </LteAppContent>
</template>

<style scoped>
.hover-lift {
  transition: transform 0.15s ease, box-shadow 0.15s ease;
}
.hover-lift:hover {
  transform: translateY(-2px);
  box-shadow: 0 0.5rem 1rem rgb(0 0 0 / 12%) !important;
}
</style>
