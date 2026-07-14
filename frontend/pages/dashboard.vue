<template>
  <div class="page">
    <header class="header">
      <div>
        <h1>Dashboard</h1>
        <p>Bienvenido, {{ auth.user?.name }}</p>
      </div>
      <button @click="handleLogout">Cerrar sesión</button>
    </header>

    <main class="content">
      <section class="panel">
        <h2>Tu perfil</h2>
        <dl>
          <div>
            <dt>ID</dt>
            <dd>{{ auth.user?.id }}</dd>
          </div>
          <div>
            <dt>Nombre</dt>
            <dd>{{ auth.user?.name }}</dd>
          </div>
          <div>
            <dt>Email</dt>
            <dd>{{ auth.user?.email }}</dd>
          </div>
        </dl>
      </section>
    </main>
  </div>
</template>

<script setup lang="ts">
definePageMeta({
  middleware: 'auth',
})

const auth = useAuthStore()

async function handleLogout() {
  await auth.logout()
  await navigateTo('/login')
}
</script>

<style scoped>
.page {
  min-height: 100vh;
  background: #f8fafc;
}

.header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1.5rem 2rem;
  background: #0f172a;
  color: #fff;
}

.header h1 {
  margin: 0;
}

.header p {
  margin: 0.25rem 0 0;
  color: #cbd5e1;
}

.header button {
  border: none;
  border-radius: 8px;
  padding: 0.65rem 1rem;
  background: #ef4444;
  color: #fff;
  cursor: pointer;
}

.content {
  padding: 2rem;
}

.panel {
  max-width: 640px;
  background: #fff;
  border-radius: 12px;
  padding: 1.5rem;
  box-shadow: 0 4px 20px rgba(15, 23, 42, 0.08);
}

.panel h2 {
  margin-top: 0;
}

dl {
  display: grid;
  gap: 1rem;
}

dt {
  font-size: 0.85rem;
  color: #64748b;
}

dd {
  margin: 0.15rem 0 0;
  font-size: 1.05rem;
  color: #0f172a;
}
</style>
