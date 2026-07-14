<template>
  <div class="page">
    <div class="card">
      <h1>Nueva contraseña</h1>
      <p class="subtitle">Elige una contraseña segura para tu cuenta</p>

      <p v-if="!isValidLink" class="error">
        El enlace no es válido. Solicita uno nuevo desde
        <NuxtLink to="/forgot-password">olvidé mi contraseña</NuxtLink>.
      </p>

      <form v-else class="form" @submit.prevent="handleSubmit">
        <label>
          Email
          <input v-model="email" type="email" readonly />
        </label>

        <label>
          Nueva contraseña
          <input v-model="password" type="password" required minlength="8" autocomplete="new-password" />
        </label>

        <label>
          Confirmar contraseña
          <input v-model="passwordConfirmation" type="password" required minlength="8" autocomplete="new-password" />
        </label>

        <p v-if="auth.error" class="error">{{ auth.error }}</p>

        <button type="submit" :disabled="auth.loading">
          {{ auth.loading ? 'Guardando...' : 'Restablecer contraseña' }}
        </button>
      </form>

      <p class="footer">
        <NuxtLink to="/login">Volver al inicio de sesión</NuxtLink>
      </p>
    </div>
  </div>
</template>

<script setup lang="ts">
definePageMeta({
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

<style scoped>
.page {
  min-height: 100vh;
  display: grid;
  place-items: center;
  background: linear-gradient(135deg, #0f172a, #1e3a5f);
  padding: 1rem;
}

.card {
  width: 100%;
  max-width: 420px;
  background: #fff;
  border-radius: 16px;
  padding: 2rem;
  box-shadow: 0 20px 50px rgba(0, 0, 0, 0.25);
}

h1 {
  margin: 0;
  font-size: 1.75rem;
  color: #0f172a;
}

.subtitle {
  margin: 0.5rem 0 1.5rem;
  color: #64748b;
}

.form {
  display: grid;
  gap: 1rem;
}

label {
  display: grid;
  gap: 0.35rem;
  font-size: 0.9rem;
  color: #334155;
}

input {
  border: 1px solid #cbd5e1;
  border-radius: 8px;
  padding: 0.75rem;
  font-size: 1rem;
}

input[readonly] {
  background: #f8fafc;
  color: #64748b;
}

button {
  margin-top: 0.5rem;
  border: none;
  border-radius: 8px;
  padding: 0.85rem;
  background: #2563eb;
  color: #fff;
  font-weight: 600;
  cursor: pointer;
}

button:disabled {
  opacity: 0.7;
  cursor: not-allowed;
}

.error {
  color: #dc2626;
  margin: 0;
  font-size: 0.9rem;
}

.footer {
  margin-top: 1.5rem;
  text-align: center;
  color: #64748b;
}

a {
  color: #2563eb;
  text-decoration: none;
}
</style>
