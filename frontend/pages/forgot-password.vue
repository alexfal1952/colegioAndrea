<template>
  <div>
    <p class="login-box-msg">Te enviaremos un enlace para restablecerla</p>

    <div v-if="success" class="alert alert-success" role="alert">
      {{ success }}
    </div>

    <form v-else @submit.prevent="handleSubmit">
      <div class="input-group mb-3">
        <input
          v-model="email"
          type="email"
          class="form-control"
          placeholder="Email"
          required
          autocomplete="email"
        >
        <div class="input-group-text">
          <span class="bi bi-envelope" />
        </div>
      </div>

      <div v-if="auth.error" class="alert alert-danger py-2" role="alert">
        {{ auth.error }}
      </div>

      <div class="row mb-3">
        <div class="col-12">
          <button type="submit" class="btn btn-primary w-100" :disabled="auth.loading">
            {{ auth.loading ? 'Enviando...' : 'Enviar enlace' }}
          </button>
        </div>
      </div>
    </form>

    <p class="mb-0 text-center">
      <NuxtLink to="/login">Volver al inicio de sesión</NuxtLink>
    </p>
  </div>
</template>

<script setup lang="ts">
definePageMeta({
  layout: 'auth',
  middleware: 'guest',
})

const auth = useAuthStore()
const email = ref('')
const success = ref('')

async function handleSubmit() {
  try {
    const response = await auth.forgotPassword({ email: email.value })
    success.value = response.message
  } catch {
    // El error ya está en el store
  }
}
</script>
