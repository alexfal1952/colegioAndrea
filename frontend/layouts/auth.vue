<script setup lang="ts">
const route = useRoute()

const authType = computed(() => (route.path.includes('register') ? 'register' : 'login'))
</script>

<template>
  <LteAuthLayout :auth-type="authType" variant="v2" logo-href="/login">
    <template #logo>
      <span class="fw-bold fs-3 text-primary">Andrea</span>
    </template>
    <slot />
  </LteAuthLayout>
</template>
