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
  { type: 'item', text: 'Funcionarios', href: '/funionarios', icon: 'bi-people' },
  { type: 'item', text: 'Animales', href: '/animales', icon: 'bi-heart-pulse' },  
  { type: 'item', text: 'Animales Dos', href: '/animales-dos', icon: 'bi-heart' },
  { type: 'item', text: 'Peces (Services)', href: '/peces', icon: 'bi-water' },
  { type: 'item', text: 'Pokemon', href: '/pokemon', icon: 'bi-pokeball' },
  { type: 'item', text: 'Marca', href: '/marca', icon: 'bi-pokeball' },
  { type: 'item', text: 'Marca Dos', href: '/marca-dos', icon: 'bi-pokeball' },
  { type: 'item', text: 'Productos', href: '/productos', icon: 'bi-pokeball' },
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
  role: auth.user?.role?.name ?? auth.user?.email ?? '',
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
