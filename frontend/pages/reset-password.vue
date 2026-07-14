<template>
  <div>
    <p class="login-box-msg">Elige una contraseña segura</p>

    <div v-if="!isValidLink" class="alert alert-danger" role="alert">
      El enlace no es válido. Solicita uno nuevo desde
      <NuxtLink to="/forgot-password" class="alert-link">olvidé mi contraseña</NuxtLink>.
    </div>

    <form v-else @submit.prevent="handleSubmit">
      <div class="input-group mb-3">
        <input
          v-model="email"
          type="email"
          class="form-control"
          readonly
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
          placeholder="Nueva contraseña"
          required
          minlength="8"
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
          minlength="8"
          autocomplete="new-password"
        >
        <div class="input-group-text">
          <span class="bi bi-lock-fill" />
        </div>
      </div>

      <div v-if="auth.error" class="alert alert-danger py-2" role="alert">
        {{ auth.error }}
      </div>

      <div class="row mb-3">
        <div class="col-12">
          <button type="submit" class="btn btn-primary w-100" :disabled="auth.loading">
            {{ auth.loading ? 'Guardando...' : 'Restablecer contraseña' }}
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
const route = useRoute()

const token = computed(() => typeof route.query.token === 'string' ? route.query.token : '')
const email = ref(typeof route.query.email === 'string' ? route.query.email : '')
const password = ref('')
const passwordConfirmation = ref('')

const isValidLink = computed(() => !!token.value && !!email.value)

async function handleSubmit() {
  if (!isValidLink.value) {
    return
  }

  try {
    await auth.resetPassword({
      token: token.value,
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
