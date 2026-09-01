<template>
  <div class="d-flex min-vh-100 align-items-center justify-content-center text-muted">
    Redirigiendo…
  </div>
</template>

<script setup lang="ts">
definePageMeta({
  layout: 'blank',
  middleware: 'root-redirect',
})
</script>
