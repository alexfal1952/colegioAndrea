<template>
  <div>
    <p class="login-box-msg">Accede a tu cuenta</p>

    <form @submit.prevent="handleLogin">
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

      <div class="input-group mb-3">
        <input
          v-model="password"
          type="password"
          class="form-control"
          placeholder="Contraseña"
          required
          autocomplete="current-password"
        >
        <div class="input-group-text">
          <span class="bi bi-lock-fill" />
        </div>
      </div>

      <div v-if="auth.error" class="alert alert-danger py-2" role="alert">
        {{ auth.error }}
      </div>

      <div class="row mb-3">
        <div class="col-8">
          <NuxtLink to="/forgot-password">¿Olvidaste tu contraseña?</NuxtLink>
        </div>
        <div class="col-4">
          <button type="submit" class="btn btn-primary btn-block w-100" :disabled="auth.loading">
            {{ auth.loading ? '...' : 'Entrar' }}
          </button>
        </div>
      </div>
    </form>

    <p class="mb-0 text-center">
      ¿No tienes cuenta?
      <NuxtLink to="/register" class="fw-semibold">Regístrate</NuxtLink>
    </p>
  </div>
</template>

<script setup lang="ts">
definePageMeta({
  layout: 'auth',
  middleware: 'guest',
})

const auth = useAuthStore()
const route = useRoute()

const email = ref('admin@andrea.com')
const password = ref('password123')

async function handleLogin() {
  try {
    await auth.login({ email: email.value, password: password.value })
    const redirect = typeof route.query.redirect === 'string' ? route.query.redirect : '/dashboard'
    await navigateTo(redirect)
  } catch {
    // El error ya está en el store
  }
}
</script>
