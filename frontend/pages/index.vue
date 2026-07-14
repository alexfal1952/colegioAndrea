<template>
  <div />
</template>

<script setup lang="ts">
const auth = useAuthStore()

if (auth.isAuthenticated) {
  await navigateTo('/dashboard')
} else {
  await navigateTo('/login')
}
</script>
