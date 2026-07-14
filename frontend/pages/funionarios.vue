<template>
    <LteAppContent title="Mi página">
      <!-- tu contenido -->
    </LteAppContent>
  </template>
  
  <script setup lang="ts">
  definePageMeta({
    layout: 'admin',
    middleware: 'auth',
  })
  </script>