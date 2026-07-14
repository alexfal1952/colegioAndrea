<template>
  <LteAppContent title="Dashboard" :breadcrumbs="[{ label: 'Inicio', href: '/dashboard' }, { label: 'Dashboard' }]">
    <div class="row">
      <div class="col-lg-3 col-6">
        <LteSmallBox title="1" text="Usuario activo" theme="primary" icon="bi-person-check" url="/dashboard" />
      </div>
      <div class="col-lg-3 col-6">
        <LteSmallBox title="API" text="Laravel Sanctum" theme="success" icon="bi-shield-check" url="#" />
      </div>
      <div class="col-lg-3 col-6">
        <LteSmallBox title="Nuxt 3" text="Frontend" theme="warning" icon="bi-box" url="#" />
      </div>
      <div class="col-lg-3 col-6">
        <LteSmallBox title="AdminLTE" text="Plantilla" theme="danger" icon="bi-palette" url="#" />
      </div>
    </div>

    <div class="row">
      <div class="col-md-6">
        <LteCard title="Tu perfil" collapsible>
          <dl class="row mb-0">
            <dt class="col-sm-4">ID</dt>
            <dd class="col-sm-8">{{ auth.user?.id }}</dd>
            <dt class="col-sm-4">Nombre</dt>
            <dd class="col-sm-8">{{ auth.user?.name }}</dd>
            <dt class="col-sm-4">Email</dt>
            <dd class="col-sm-8">{{ auth.user?.email }}</dd>
          </dl>
        </LteCard>
      </div>

      <div class="col-md-6">
        <LteCard title="Empezar a trabajar" collapsible>
          <p class="mb-2">
            Este layout (`layouts/admin.vue`) ya incluye sidebar, topbar, dark mode y menú.
          </p>
          <ul class="mb-0">
            <li>Añade rutas en <code>frontend/pages/</code></li>
            <li>Enlázalas en el menú de <code>layouts/admin.vue</code></li>
            <li>Usa <code>definePageMeta({ layout: 'admin', middleware: 'auth' })</code></li>
          </ul>
        </LteCard>
      </div>
    </div>
  </LteAppContent>
</template>

<script setup lang="ts">
definePageMeta({
  layout: 'admin',
  middleware: 'auth',
})

const auth = useAuthStore()
</script>
