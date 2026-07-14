<template>
  <div>
    <p class="login-box-msg">Regístrate para continuar</p>

    <form @submit.prevent="handleRegister">
      <div class="input-group mb-3">
        <input
          v-model="name"
          type="text"
          class="form-control"
          placeholder="Nombre"
          required
          autocomplete="name"
        >
        <div class="input-group-text">
          <span class="bi bi-person" />
        </div>
      </div>

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
          autocomplete="new-password"
        >
        <div class="input-group-text">
          <span class="bi bi-lock-fill" />
        </div>
      </div>

      <div class="input-group mb-3">
        <input
          v-model="passwordConfirmation"
          type="password"
          class="form-control"
          placeholder="Confirmar contraseña"
          required
          autocomplete="new-password"
        >
        <div class="input-group-text">
          <span class="bi bi-lock-fill" />
        </div>
      </div>

      <div v-if="auth.error" class="alert alert-danger py-2" role="alert">
        {{ auth.error }}
        <template v-if="auth.error.includes('ya está registrado')">
          <NuxtLink to="/login" class="alert-link ms-1">Inicia sesión</NuxtLink>
        </template>
      </div>

      <div class="row mb-3">
        <div class="col-12">
          <button type="submit" class="btn btn-primary w-100" :disabled="auth.loading">
            {{ auth.loading ? 'Registrando...' : 'Registrarse' }}
          </button>
        </div>
      </div>
    </form>

    <p class="mb-0 text-center">
      ¿Ya tienes cuenta?
      <NuxtLink to="/login" class="fw-semibold">Inicia sesión</NuxtLink>
    </p>
  </div>
</template>

<script setup lang="ts">
definePageMeta({
  layout: 'auth',
  middleware: 'guest',
})

const auth = useAuthStore()

const name = ref('')
const email = ref('')
const password = ref('')
const passwordConfirmation = ref('')

async function handleRegister() {
  try {
    await auth.register({
      name: name.value,
      email: email.value,
      password: password.value,
      password_confirmation: passwordConfirmation.value,
    })
    await navigateTo('/dashboard')
  } catch {
    // El error ya está en el store
  }
}
</script>
