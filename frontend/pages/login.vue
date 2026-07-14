<template>
  <div class="page">
    <div class="card">
      <h1>Iniciar sesión</h1>
      <p class="subtitle">Accede a tu cuenta</p>

      <form class="form" @submit.prevent="handleLogin">
        <label>
          Email
          <input v-model="email" type="email" required autocomplete="email" />
        </label>

        <label>
          Contraseña
          <input v-model="password" type="password" required autocomplete="current-password" />
        </label>

        <p class="forgot">
          <NuxtLink to="/forgot-password">¿Olvidaste tu contraseña?</NuxtLink>
        </p>

        <p v-if="auth.error" class="error">{{ auth.error }}</p>

        <button type="submit" :disabled="auth.loading">
          {{ auth.loading ? 'Entrando...' : 'Entrar' }}
        </button>
      </form>

      <p class="footer">
        ¿No tienes cuenta?
        <NuxtLink to="/register">Regístrate</NuxtLink>
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

.forgot {
  margin: 0;
  text-align: right;
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
