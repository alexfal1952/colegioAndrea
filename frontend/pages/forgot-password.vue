<template>
  <div class="page">
    <div class="card">
      <h1>¿Olvidaste tu contraseña?</h1>
      <p class="subtitle">Te enviaremos un enlace para restablecerla</p>

      <p v-if="success" class="success">{{ success }}</p>

      <form v-else class="form" @submit.prevent="handleSubmit">
        <label>
          Email
          <input v-model="email" type="email" required autocomplete="email" />
        </label>

        <p v-if="auth.error" class="error">{{ auth.error }}</p>

        <button type="submit" :disabled="auth.loading">
          {{ auth.loading ? 'Enviando...' : 'Enviar enlace' }}
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

.success {
  color: #15803d;
  background: #f0fdf4;
  border: 1px solid #bbf7d0;
  border-radius: 8px;
  padding: 1rem;
  margin: 0;
  font-size: 0.95rem;
  line-height: 1.5;
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
