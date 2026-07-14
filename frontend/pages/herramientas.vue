<template>
  <LteAppContent
    title="Herramientas AdminLTE"
    :breadcrumbs="[
      { label: 'Inicio', href: '/dashboard' },
      { label: 'Herramientas' },
    ]"
  >
    <LteAlert theme="info" icon="bi-info-circle" title="Catálogo de componentes">
      Aquí tienes los componentes principales de AdminLTE que puedes reutilizar.
      Cópialos desde esta página hacia tus propias vistas. Los plugins externos
      (gráficos, calendario, editor, etc.) se instalan aparte si los necesitas.
    </LteAlert>

    <LteCard title="Componentes disponibles" icon="bi-grid-3x3-gap" theme="primary" variant="outline">
      <LteTabs v-model="activeTab" pills>
        <LteTab id="widgets" title="Widgets" icon="bi-boxes">
          <div class="row mt-3">
            <div class="col-lg-3 col-6">
              <LteSmallBox title="150" text="Small Box" theme="primary" icon="bi-bag" url="#" url-text="Más info" />
            </div>
            <div class="col-lg-3 col-6">
              <LteSmallBox title="53%" text="Bounce Rate" theme="success" icon="bi-bar-chart" url="#" />
            </div>
            <div class="col-lg-3 col-6">
              <LteSmallBox title="44" text="User Registrations" theme="warning" icon="bi-person-plus" url="#" />
            </div>
            <div class="col-lg-3 col-6">
              <LteSmallBox title="65" text="Unique Visitors" theme="danger" icon="bi-pie-chart" url="#" />
            </div>
          </div>

          <div class="row">
            <div class="col-md-4">
              <LteInfoBox title="CPU Traffic" text="90%" icon="bi-cpu" theme="info" :progress="90" progress-text="90% Usage" />
            </div>
            <div class="col-md-4">
              <LteInfoBox title="Likes" text="41,410" icon="bi-heart" theme="danger" variant="solid" />
            </div>
            <div class="col-md-4">
              <LteInfoBox title="Sales" text="760" icon="bi-cart" theme="success" variant="solid" gradient />
            </div>
          </div>

          <div class="row">
            <div class="col-md-4">
              <LteCard title="Card" theme="primary" collapsible maximizable removable>
                <p class="mb-0">
                  <code>LteCard</code> — colapsable, maximizable y removable.
                </p>
              </LteCard>
            </div>
            <div class="col-md-4">
              <LteCard title="Outline" theme="success" variant="outline">
                <p class="mb-0">Variante <code>outline</code>.</p>
              </LteCard>
            </div>
            <div class="col-md-4">
              <LteCard title="Solid" theme="warning" variant="solid" gradient>
                <p class="mb-0">Variante <code>solid</code> + gradient.</p>
              </LteCard>
            </div>
          </div>

          <div class="row">
            <div class="col-md-3">
              <LteDescriptionBlock header="Ventas" text="12,381" icon="bi-graph-up" :percentage="17" />
            </div>
            <div class="col-md-3">
              <LteDescriptionBlock header="Costos" text="3,291" icon="bi-cash" :percentage="-5" />
            </div>
            <div class="col-md-3">
              <LteDescriptionBlock header="Usuarios" text="840" icon="bi-people" :percentage="8" />
            </div>
            <div class="col-md-3">
              <LteDescriptionBlock header="Tickets" text="64" icon="bi-ticket" :percentage="-2" />
            </div>
          </div>

          <div class="row">
            <div class="col-md-6">
              <LteCard title="Profile Card">
                <LteProfileCard
                  :name="auth.user?.name || 'Admin'"
                  :role="auth.user?.email"
                  :image="avatar"
                  :stats="[
                    { label: 'Proyectos', value: 12 },
                    { label: 'Tareas', value: 48 },
                    { label: 'Completadas', value: 31 },
                  ]"
                />
              </LteCard>
            </div>
            <div class="col-md-6">
              <LteCard title="Ratings">
                <p>Valoración de ejemplo:</p>
                <LteRatings :value="4" :max="5" theme="warning" show-text />
              </LteCard>
            </div>
          </div>
        </LteTab>

        <LteTab id="alerts" title="Alertas" icon="bi-exclamation-triangle">
          <div class="mt-3">
            <LteAlert theme="success" icon="bi-check-circle" title="Éxito" dismissible>
              Operación completada correctamente. (<code>LteAlert</code>)
            </LteAlert>
            <LteAlert theme="danger" icon="bi-x-circle" title="Error" dismissible>
              Algo salió mal. Revisa los datos e intenta de nuevo.
            </LteAlert>
            <LteAlert theme="warning" icon="bi-exclamation-triangle" title="Advertencia">
              Este es un aviso importante.
            </LteAlert>
            <LteAlert theme="info" icon="bi-info-circle" title="Información">
              Tip: usa dismissible para permitir cerrar la alerta.
            </LteAlert>

            <div class="row">
              <div class="col-md-6">
                <LteCallout theme="info" title="Callout Info">
                  <code>LteCallout</code> — ideal para tips y notas laterales.
                </LteCallout>
              </div>
              <div class="col-md-6">
                <LteCallout theme="danger" title="Callout Danger">
                  Usa callouts para resaltar avisos críticos.
                </LteCallout>
              </div>
            </div>

            <LteCard title="Progress">
              <LteProgress :value="35" theme="primary" show-label class="mb-3" />
              <LteProgress :value="70" theme="success" striped animated show-label class="mb-3" />
              <LteProgressGroup label="Add Products to Cart" :value="60" theme="primary" />
              <LteProgressGroup label="Complete Purchase" :value="30" theme="danger" />
              <LteProgressGroup label="Visit Premium Page" :value="85" theme="success" />
            </LteCard>

            <LteCard title="Toast">
              <LteButton theme="primary" label="Mostrar toast" icon="bi-bell" @click="showToast = true" />
              <div class="toast-container position-fixed bottom-0 end-0 p-3">
                <LteToast v-model="showToast" theme="success" title="Andrea" subtitle="ahora" autohide :delay="3000">
                  Notificación de ejemplo con <code>LteToast</code>.
                </LteToast>
              </div>
            </LteCard>
          </div>
        </LteTab>

        <LteTab id="forms" title="Formularios" icon="bi-input-cursor-text">
          <div class="row mt-3">
            <div class="col-md-6">
              <LteCard title="Inputs básicos">
                <LteInput v-model="form.name" label="Nombre" placeholder="Tu nombre" />
                <LteInput v-model="form.email" label="Email" type="email" placeholder="correo@ejemplo.com" />
                <LteTextarea v-model="form.notes" label="Notas" :rows="3" placeholder="Escribe algo..." />
                <LteSelect
                  v-model="form.role"
                  label="Rol"
                  :options="[
                    { value: 'admin', label: 'Administrador' },
                    { value: 'editor', label: 'Editor' },
                    { value: 'viewer', label: 'Viewer' },
                  ]"
                />
                <LteInputColor v-model="form.color" label="Color" />
                <LteInputSwitch v-model="form.active" label="Usuario activo" theme="success" />
                <div class="d-flex gap-2 mt-3 flex-wrap">
                  <LteButton theme="primary" label="Guardar" icon="bi-save" type="button" />
                  <LteButton theme="secondary" label="Cancelar" outline type="button" />
                  <LteButton theme="danger" label="Eliminar" icon="bi-trash" type="button" />
                </div>
              </LteCard>
            </div>

            <div class="col-md-6">
              <LteCard title="Modal">
                <p>Abre un diálogo con <code>LteModal</code>.</p>
                <LteButton theme="info" label="Abrir modal" icon="bi-window" @click="showModal = true" />
                <LteModal v-model="showModal" title="Ejemplo de modal" theme="primary" centered>
                  <p>Contenido del modal. Puedes poner formularios, tablas, etc.</p>
                  <template #footer="{ close }">
                    <LteButton theme="secondary" label="Cerrar" outline @click="close()" />
                    <LteButton theme="primary" label="Confirmar" @click="close()" />
                  </template>
                </LteModal>
              </LteCard>

              <LteCard title="Wizard" class="mt-3">
                <LteWizard
                  v-model="wizardStep"
                  prev-label="Anterior"
                  next-label="Siguiente"
                  finish-label="Finalizar"
                  @finish="wizardStep = 0"
                >
                  <LteWizardStep title="Datos">
                    <p>Paso 1: datos básicos del usuario.</p>
                    <LteInput v-model="form.name" label="Nombre" />
                  </LteWizardStep>
                  <LteWizardStep title="Configuración">
                    <p>Paso 2: preferencias.</p>
                    <LteInputSwitch v-model="form.active" label="Activar notificaciones" />
                  </LteWizardStep>
                  <LteWizardStep title="Confirmar">
                    <p>Paso 3: revisa y confirma.</p>
                    <LteAlert theme="success" title="Listo">
                      Puedes finalizar el asistente.
                    </LteAlert>
                  </LteWizardStep>
                </LteWizard>
              </LteCard>
            </div>
          </div>
        </LteTab>

        <LteTab id="nav" title="Navegación" icon="bi-menu-button-wide">
          <div class="row mt-3">
            <div class="col-md-6">
              <LteCard title="Accordion">
                <LteAccordion>
                  <LteAccordionItem id="acc-1" title="¿Qué es AdminLTE?" default-open>
                    Plantilla de dashboard basada en Bootstrap 5, adaptada a Vue/Nuxt.
                  </LteAccordionItem>
                  <LteAccordionItem id="acc-2" title="¿Cómo añado una página?">
                    Crea un archivo en <code>pages/</code>, usa
                    <code>definePageMeta({ layout: 'admin', middleware: 'auth' })</code>
                    y agrégalo al menú en <code>layouts/admin.vue</code>.
                  </LteAccordionItem>
                  <LteAccordionItem id="acc-3" title="¿Dónde están los componentes?">
                    Vienen de <code>@adminlte/vue</code> y se auto-importan con el módulo
                    <code>@adminlte/nuxt</code>.
                  </LteAccordionItem>
                </LteAccordion>
              </LteCard>
            </div>

            <div class="col-md-6">
              <LteCard title="Timeline">
                <LteTimeline :items="timelineItems" />
              </LteCard>
            </div>
          </div>
        </LteTab>

        <LteTab id="lista" title="Lista completa" icon="bi-list-check">
          <div class="row mt-3">
            <div class="col-md-4" v-for="group in catalog" :key="group.title">
              <LteCard :title="group.title" :icon="group.icon" theme="secondary" variant="outline">
                <ul class="list-unstyled mb-0">
                  <li v-for="item in group.items" :key="item" class="mb-1">
                    <code>{{ item }}</code>
                  </li>
                </ul>
              </LteCard>
            </div>
          </div>

          <LteCallout theme="warning" title="Plugins opcionales">
            Estos requieren instalación extra:
            <code>LteApexChart</code>, <code>LteDatatable</code>, <code>LteEditor</code>,
            <code>LteFlatpickr</code>, <code>LteTomSelect</code>, <code>LteCalendar</code>,
            <code>LteVectorMap</code>, <code>LteSortable</code>, <code>LteKanban</code>.
          </LteCallout>
        </LteTab>
      </LteTabs>
    </LteCard>
  </LteAppContent>
</template>

<script setup lang="ts">
import type { TimelineItem } from '@adminlte/vue'

definePageMeta({
  layout: 'admin',
  middleware: 'auth',
})

const auth = useAuthStore()
const activeTab = ref('widgets')
const showToast = ref(false)
const showModal = ref(false)
const wizardStep = ref(0)

const form = reactive({
  name: auth.user?.name ?? '',
  email: auth.user?.email ?? '',
  notes: '',
  role: 'admin',
  color: '#0d6efd',
  active: true,
})

const avatar = computed(
  () =>
    `https://ui-avatars.com/api/?name=${encodeURIComponent(auth.user?.name || 'U')}&background=0d6efd&color=fff`,
)

const timelineItems: TimelineItem[] = [
  {
    time: '12:05',
    title: 'Soporte respondido',
    body: 'Se cerró el ticket #204.',
    icon: 'bi-envelope',
    iconTheme: 'primary',
  },
  {
    time: '10:30',
    title: 'Nuevo usuario',
    body: 'Se registró un cliente nuevo.',
    icon: 'bi-person-plus',
    iconTheme: 'success',
  },
  {
    time: '09:00',
    title: 'Deploy',
    body: 'Frontend actualizado con AdminLTE.',
    icon: 'bi-rocket-takeoff',
    iconTheme: 'warning',
  },
]

const catalog = [
  {
    title: 'Layout',
    icon: 'bi-layout-sidebar',
    items: [
      'LteDashboardLayout',
      'LteAuthLayout',
      'LteAppContent',
      'LteSidebar',
      'LteTopbar',
      'LteFooter',
      'LteColorModeToggle',
      'LteFullscreenToggle',
    ],
  },
  {
    title: 'Widgets',
    icon: 'bi-boxes',
    items: [
      'LteCard',
      'LteSmallBox',
      'LteInfoBox',
      'LteAlert',
      'LteCallout',
      'LteProgress',
      'LteProgressGroup',
      'LteTimeline',
      'LteRatings',
      'LteProfileCard',
      'LteDescriptionBlock',
      'LteToast',
      'LteTabs / LteTab',
      'LteAccordion',
      'LteBreadcrumb',
      'LteCommandPalette',
    ],
  },
  {
    title: 'Formularios y tools',
    icon: 'bi-ui-checks',
    items: [
      'LteButton',
      'LteInput',
      'LteTextarea',
      'LteSelect',
      'LteInputSwitch',
      'LteInputColor',
      'LteInputFile',
      'LteModal',
      'LteWizard / LteWizardStep',
    ],
  },
]
</script>
