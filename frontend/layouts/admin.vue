<script setup lang="ts">
import type { MenuNode, TopbarUser } from '@adminlte/vue'

const route = useRoute()
const router = useRouter()
const auth = useAuthStore()
const NuxtLink = resolveComponent('NuxtLink')

const menu: MenuNode[] = [
  { type: 'header', text: 'Principal' },
  { type: 'item', text: 'Dashboard', href: '/dashboard', icon: 'bi-speedometer2' },
  { type: 'item', text: 'Herramientas', href: '/herramientas', icon: 'bi-tools' },
  { type: 'item', text: 'Funionarios', href: '/funionarios', icon: 'bi-person' },
  { type: 'header', text: 'Configuración' },
  { type: 'item', text: 'Períodos escolares', href: '/configuracion/periodos-escolares', icon: 'bi-calendar-range' },
  { type: 'item', text: 'Calendario escolar', href: '/configuracion/calendario', icon: 'bi-calendar3' },
  { type: 'item', text: 'Feriados', href: '/configuracion/calendario/feriados', icon: 'bi-calendar-x' },
  { type: 'item', text: 'Vacaciones', href: '/configuracion/calendario/vacaciones', icon: 'bi-sun' },
  { type: 'item', text: 'Suspensiones', href: '/configuracion/calendario/suspensiones', icon: 'bi-slash-circle' },
  { type: 'item', text: 'Estados asistencia', href: '/configuracion/asistencia/estados', icon: 'bi-list-check' },
  { type: 'header', text: 'Comunidad educativa' },
  { type: 'item', text: 'Personas', href: '/comunidad/personas', icon: 'bi-people' },
  { type: 'item', text: 'Alumnos', href: '/comunidad/alumnos', icon: 'bi-mortarboard' },
  { type: 'item', text: 'Profesores', href: '/comunidad/profesores', icon: 'bi-person-workspace' },
  { type: 'header', text: 'Académico' },
  { type: 'item', text: 'Asignaturas', href: '/academico/asignaturas', icon: 'bi-book' },
  { type: 'item', text: 'Cursos', href: '/academico/cursos', icon: 'bi-journal-bookmark' },
  { type: 'item', text: 'Matrículas', href: '/academico/matriculas', icon: 'bi-card-checklist' },
  { type: 'header', text: 'Asistencia' },
  { type: 'item', text: 'Inicio asistencia', href: '/asistencia', icon: 'bi-house-door' },
  { type: 'item', text: 'Registrar asistencia', href: '/asistencia/registrar', icon: 'bi-clipboard-check' },
  { type: 'item', text: 'Resumen por alumno', href: '/asistencia/resumen-mensual', icon: 'bi-person-lines-fill' },
  { type: 'item', text: 'Resumen del curso', href: '/asistencia/resumen-curso', icon: 'bi-people-fill' },
  {
    type: 'group',
    text: 'Cuenta',
    icon: 'bi-person-circle',
    children: [
      { type: 'item', text: 'Perfil', href: '/dashboard', icon: 'bi-person' },
    ],
  },
]

const user = computed<TopbarUser>(() => ({
  name: auth.user?.name ?? 'Usuario',
  role: auth.user?.email ?? '',
  image: `https://ui-avatars.com/api/?name=${encodeURIComponent(auth.user?.name || 'U')}&background=0d6efd&color=fff`,
}))

async function handleLogout() {
  await auth.logout()
  await navigateTo('/login')
}

function handleProfile() {
  router.push('/dashboard')
}

function navigate(href: string) {
  router.push(href)
}
</script>

<template>
  <LteDashboardLayout
    brand-text="Andrea"
    :menu-items="menu"
    :current-path="route.path"
    :link-component="NuxtLink"
    :user="user"
    :navigate="navigate"
    @logout="handleLogout"
    @profile="handleProfile"
  >
    <slot />

    <template #footer>
      <strong>Andrea</strong>
      &nbsp;· AdminLTE 4
    </template>
  </LteDashboardLayout>
</template>
