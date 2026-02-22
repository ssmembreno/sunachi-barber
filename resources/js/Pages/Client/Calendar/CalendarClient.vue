<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { Head, Link, usePage } from '@inertiajs/vue3'
import axios from 'axios'
import FullCalendar from '@fullcalendar/vue3'
import dayGridPlugin from '@fullcalendar/daygrid'
import timeGridPlugin from '@fullcalendar/timegrid'
import interactionPlugin from '@fullcalendar/interaction'
import ModalCreateClient from '@/Components/CalendarClient/ModalCreateClient.vue'
import ModalMyReservations from '@/Components/CalendarClient/ModalMyReservations.vue'
import Dropdown from '@/Components/Dropdown.vue'

const props = defineProps({
    canLogin: Boolean,
    canRegister: Boolean,
    services: Array,
    barbers: Array,
    lastReservations: Array,
});

const page = usePage()

// --- Header Logic ---
const isScrolled = ref(false);

const handleScroll = () => {
    isScrolled.value = window.scrollY > 50;
};

onMounted(() => {
    window.addEventListener('scroll', handleScroll);
});

onUnmounted(() => {
    window.removeEventListener('scroll', handleScroll);
});

// --- Calendar Logic ---
const calendarRef = ref(null)
const showWeekends = ref(true)
const isMobile = ref(window.innerWidth < 1024)

window.addEventListener('resize', () => {
  isMobile.value = window.innerWidth < 1024
})

const mapStatus = {
  confirmed: 'Confirmado',
  pending: 'Pendiente',
  noshow: 'No se presentó',
  completed: 'Finalizado',
  cancelled: 'Cancelado',
}

// Modal state info reservaciones
const modalOpen = ref(false)
const selectedEvent = ref(null)

// Modal create
const createModalOpen = ref(false)
const myReservationsOpen = ref(false)
const initialSelection = ref(null)

function refreshEvents() {
  const api = calendarRef.value?.getApi()
  api?.refetchEvents()
}

const selectedEventTime = computed(() => {
  if (!selectedEvent.value) return ''
  const start = selectedEvent.value.start
  const end = selectedEvent.value.end
  try {
    const startStr = new Intl.DateTimeFormat('es-ES', {
      weekday: 'short',
      year: 'numeric',
      month: 'short',
      day: '2-digit',
      hour: '2-digit',
      minute: '2-digit',
    }).format(start)

    const endStr = end
      ? new Intl.DateTimeFormat('es-ES', { hour: '2-digit', minute: '2-digit' }).format(end)
      : ''

    return endStr ? `${startStr} → ${endStr}` : startStr
  } catch {
    return ''
  }
})

function closeModal() {
  modalOpen.value = false
  selectedEvent.value = null
}

async function updateStatus(newStatus) {
  if (!selectedEvent.value) return
  
  try {
    const id = selectedEvent.value.id
    await axios.patch(route('calendar.updateStatus', id), {
      status: newStatus
    })
    
    // Actualizar UI localmente o refetch
    refreshEvents()
    closeModal()
  } catch (error) {
    console.error('Error al actualizar el estado:', error)
  }
}

async function cancelClientReservation() {
  if (!selectedEvent.value) return
  
  if (!confirm('¿Estás seguro que deseas cancelar tu cita? Esta acción no se puede deshacer.')) return;
  
  try {
    const id = selectedEvent.value.id
    await axios.post(route('booking.appointments.cancel', id))
    
    refreshEvents()
    closeModal()
  } catch (error) {
    console.error('Error al cancelar la cita:', error)
    alert('No se pudo cancelar la cita. ' + (error.response?.data?.message || ''))
  }
}

function goToday() {
  const api = calendarRef.value?.getApi()
  api?.today()
}

function toggleWeekends() {
  showWeekends.value = !showWeekends.value
  const api = calendarRef.value?.getApi()
  api?.setOption('weekends', showWeekends.value)
}

// ✅ Opciones FullCalendar
const calendarOptions = computed(() => ({
  plugins: [dayGridPlugin, timeGridPlugin, interactionPlugin],
  initialView: isMobile.value ? 'timeGridDay' : 'timeGridWeek',

  headerToolbar: {
    left: isMobile.value ? 'prev,next' : 'prev,next today',
    center: 'title',
    right: isMobile.value ? 'timeGridDay,dayGridMonth' : 'timeGridDay,timeGridWeek,dayGridMonth',
  },

  // Localization
  locale: 'es',
  buttonText: {
    today: 'Hoy',
    month: 'Mes',
    week: 'Semana',
    day: 'Día',
    list: 'Agenda'
  },

  // Horario visible
  slotMinTime: '08:00:00',
  slotMaxTime: '21:00:00',
  allDaySlot: false,
  slotDuration: '00:30:00',
  slotLabelInterval: '01:00',
  expandRows: true,

  // UX
  nowIndicator: true,
  selectable: true,
  selectMirror: true,
  unselectAuto: true,
  weekends: showWeekends.value,
  height: 'auto',
  stickyHeaderDates: true,

  // Carga dinámica desde backend
  events: async (info, successCallback, failureCallback) => {
    try {
      const { data } = await axios.get(route('booking.events'), {
        params: {
          start: info.startStr,
          end: info.endStr,
        },
      })
      successCallback(data)
    } catch (e) {
      console.error('Error cargando eventos', e)
      failureCallback(e)
    }
  },

  // Clases por estado
  // Clases por estado
  eventClassNames: (arg) => {
    // Si es admin/barbero, mostramos colores de estado
    const userRole = page.props.auth.user?.role
    if (userRole === 'admin' || userRole === 'barber') {
         const status = arg.event.extendedProps?.status
         return [`fc-status-${status || 'pending'}`]
    }
    return []
  },

  // Click en evento existente
  eventClick: (info) => {
    selectedEvent.value = info.event
    modalOpen.value = true
  },

  // Selección de rango (arrastrar o click y arrastrar)
  select: (info) => {
    // Helper para formato local
    const toLocalISO = (d) => {
        const pad = (n) => n < 10 ? '0' + n : n
        return d.getFullYear() +
            '-' + pad(d.getMonth() + 1) +
            '-' + pad(d.getDate()) +
            'T' + pad(d.getHours()) +
            ':' + pad(d.getMinutes())
    }

    initialSelection.value = {
        start: info.start,
        end: info.end,
        startStr: toLocalISO(info.start),
        endStr: toLocalISO(info.end)
    }
    createModalOpen.value = true
  },

  // Click simple en una celda
  dateClick: (info) => {
    const start = info.date
    // Default duration: 30 minutes
    const end = new Date(start.getTime() + 30 * 60000)
    
    // Helper para formato local YYYY-MM-DDTHH:mm compatible con input datetime-local
    const toLocalISO = (d) => {
        const pad = (n) => n < 10 ? '0' + n : n
        return d.getFullYear() +
            '-' + pad(d.getMonth() + 1) +
            '-' + pad(d.getDate()) +
            'T' + pad(d.getHours()) +
            ':' + pad(d.getMinutes())
    }

    initialSelection.value = {
        start: start,
        end: end,
        startStr: toLocalISO(start),
        endStr: toLocalISO(end)
    }
    createModalOpen.value = true
  },
  
  // Ajustes Touch
  longPressDelay: 100, // Reducir delay para que sea más ágil en móvil
  eventLongPressDelay: 100,
  selectLongPressDelay: 100,
}))
</script>

<style>
/* Ajustes visuales base - DARK THEMED */
.fc {
  font-family: 'Inter', ui-sans-serif, system-ui, sans-serif;
  --fc-page-bg-color: #09090b; /* zinc-950 */
  --fc-neutral-bg-color: #18181b; /* zinc-900 */
  --fc-border-color: #27272a; /* zinc-800 */
  --fc-list-event-hover-bg-color: #27272a;
  --fc-today-bg-color: rgba(245, 158, 11, 0.05); /* amber-500 gentle */
}

/* Toolbars */
.fc .fc-toolbar {
  @apply mb-6 gap-4 flex-wrap justify-center sm:justify-between !important;
}

.fc .fc-toolbar-title {
  @apply text-xl font-bold text-zinc-100 capitalize transition-all !important;
}

/* Separar grupos de botones */
.fc .fc-button-group {
  @apply gap-2 !important;
  margin-left: 0 !important;
}

.fc .fc-button-group > .fc-button {
  @apply rounded-xl !important;
  margin-left: 0 !important;
}

/* Botones con estilo moderno Dark/Amber */
.fc .fc-button {
  @apply border-zinc-800 bg-zinc-900 text-zinc-300 font-semibold px-4 py-2 text-sm shadow-sm transition-all hover:bg-zinc-800 hover:text-white hover:border-amber-500/50 focus:ring-2 focus:ring-amber-500 focus:outline-none !important;
}

.fc .fc-button-primary:not(:disabled).fc-button-active,
.fc .fc-button-primary:not(:disabled):active {
  @apply bg-amber-500 border-amber-500 text-black shadow-lg shadow-amber-500/20 hover:bg-amber-400 !important;
}

/* Grid layout */
.fc-theme-standard td, .fc-theme-standard th {
  border-color: var(--fc-border-color);
}

.fc .fc-scrollgrid {
  @apply rounded-2xl overflow-hidden border-none shadow-sm !important;
}

.fc .fc-timegrid-slot {
  height: 4.5em !important;
  border-bottom: 1px dashed var(--fc-border-color) !important;
}

/* Header de columnas (Lunes, Martes...) */
.fc .fc-col-header-cell {
  @apply py-4 bg-zinc-900/80 backdrop-blur-md !important;
}

.fc .fc-col-header-cell-cushion {
  @apply text-zinc-400 font-bold uppercase tracking-wider text-xs !important;
}

/* Horas lateral */
.fc .fc-timegrid-slot-label-cushion, 
.fc .fc-timegrid-axis-cushion {
  @apply text-zinc-500 font-medium text-xs !important;
}

/* Indicador de "Ahora" */
.fc .fc-timegrid-now-indicator-line {
  border-color: #f59e0b; /* amber-500 */
  border-width: 2px;
}
.fc .fc-timegrid-now-indicator-arrow {
  border-color: #f59e0b;
  border-top-color: transparent;
  border-bottom-color: transparent;
}

/* Eventos Premium */
.fc-v-event {
  @apply rounded-lg border-none shadow-md transition-all hover:scale-[1.02] active:scale-95 !important;
}

.fc-status-pending {
  background: rgba(245, 158, 11, 0.15) !important;
  border-left: 3px solid #f59e0b !important;
  color: #fbbf24 !important;
}

.fc-status-confirmed {
  background: rgba(16, 185, 129, 0.15) !important;
  border-left: 3px solid #10b981 !important;
  color: #34d399 !important;
}

.fc-status-cancelled,
.fc-status-noshow {
  background: rgba(239, 68, 68, 0.15) !important;
  border-left: 3px solid #ef4444 !important;
  color: #f87171 !important;
  @apply opacity-70 grayscale-[0.3] !important;
}

.fc-status-completed {
  background: rgba(59, 130, 246, 0.15) !important;
  border-left: 3px solid #3b82f6 !important;
  color: #60a5fa !important;
  @apply opacity-80 !important;
}

.fc .fc-event-title {
  @apply font-bold text-xs sm:text-sm !important;
  color: inherit !important;
}
.fc .fc-event-time {
  @apply text-[10px] sm:text-xs font-semibold opacity-90 !important;
  color: inherit !important;
}

/* Fix para el modo dark en la vista de mes */
.fc-daygrid-day-number {
  @apply text-zinc-400 !important;
}
.fc-daygrid-day:hover {
  @apply bg-zinc-900 !important;
}

.fc .fc-v-event .fc-event-main,
.fc .fc-v-event .fc-event-main-frame,
.fc .fc-h-event .fc-event-main,
.fc .fc-h-event .fc-event-main-frame {
  color: inherit !important;
  margin-left: 6px !important;
}
</style>

<template>
    <Head title="Reservar Cita - Sunachi Barber" />

    <div class="min-h-screen bg-zinc-950 text-zinc-100 font-sans selection:bg-amber-500 selection:text-black">
        <!-- Header -->
        <header 
            :class="[
                'fixed top-0 z-50 w-full transition-[background-color,border-color,padding,backdrop-filter] duration-500 px-6 md:px-12 border-b',
                isScrolled 
                    ? 'bg-zinc-950/80 backdrop-blur-xl border-zinc-800 py-3' 
                    : 'bg-zinc-950 border-transparent py-5'
            ]"
        >
            <div class="max-w-7xl mx-auto flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <Link :href="route('welcome')" class="text-2xl font-black tracking-tighter text-white">
                        SUNACHI <span class="text-amber-500">BARBER</span>
                    </Link>
                </div>

                <nav class="hidden lg:flex items-center gap-8 text-sm font-medium uppercase tracking-widest text-zinc-400">
                    <Link :href="route('welcome') + '#inicio'" class="hover:text-amber-500 transition-colors">Inicio</Link>
                    <Link :href="route('welcome') + '#servicios'" class="hover:text-amber-500 transition-colors">Servicios</Link>
                </nav>

                <div class="flex items-center gap-4">
                    <div v-if="$page.props.auth.user" class="flex items-center gap-4">
                        <Dropdown align="right" width="48" contentClasses="py-1 bg-zinc-950 border border-zinc-800">
                            <template #trigger>
                                <button class="flex items-center gap-2 text-sm font-bold uppercase tracking-widest text-zinc-400 hover:text-amber-500 transition-colors focus:outline-none">
                                    {{ $page.props.auth.user.name }}
                                    <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                    </svg>
                                </button>
                            </template>

                            <template #content>
                                <Link :href="route('profile.edit')" class="block w-full px-4 py-2 text-start text-sm leading-5 text-zinc-400 hover:bg-zinc-900 hover:text-amber-500 transition duration-150 ease-in-out"> 
                                    Ver perfil 
                                </Link>
                                <Link :href="route('logout')" method="post" as="button" class="block w-full px-4 py-2 text-start text-sm leading-5 text-zinc-400 hover:bg-zinc-900 hover:text-amber-500 transition duration-150 ease-in-out"> 
                                    Cerrar sesión 
                                </Link>
                            </template>
                        </Dropdown>
                    </div>
                    <template v-else>
                        <Link :href="route('login')" class="text-sm font-semibold hover:text-amber-500 transition-colors">
                            Iniciar sesión
                        </Link>
                        <Link :href="route('register')" class="hidden sm:block px-5 py-2.5 bg-amber-500 hover:bg-amber-600 text-black text-xs font-bold uppercase tracking-widest rounded-full transition-all duration-300">
                            Registrarse
                        </Link>
                    </template>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="pt-32 px-4 pb-12 max-w-7xl mx-auto">
            
            <!-- Banner Informational -->
            <div class="mb-12 relative overflow-hidden rounded-2xl border border-zinc-800 bg-zinc-900/50 p-8 text-center">
                <div class="relative z-10">
                    <h2 class="text-3xl md:text-4xl font-black text-white italic mb-2">
                        RESERVA TU <span class="text-amber-500">CITA</span>
                    </h2>
                    <p class="text-zinc-400 text-lg">
                        Estás reservando una cita para corte de pelo. Selecciona el horario que mejor te convenga.
                    </p>
                </div>
                <!-- Decorative background elements -->
                <div class="absolute top-0 left-0 w-full h-full bg-gradient-to-r from-amber-500/5 to-transparent pointer-events-none"></div>
            </div>

            <!-- Calendar Card -->
            <div class="relative bg-zinc-900 border border-zinc-800 rounded-2xl shadow-2xl p-4 sm:p-6">
                <!-- Internal Header with Filters -->
                <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between border-b border-zinc-800 pb-6">
                    <div>
                        <h3 class="text-xl font-bold text-white">Disponibilidad</h3>
                        <p class="text-sm text-zinc-500 mt-1">
                            Horarios disponibles en tiempo real.
                        </p>
                    </div>

                    <div class="flex items-center gap-3">
                        <button
                            v-if="$page.props.auth.user"
                            type="button"
                            class="px-4 py-2 rounded-lg bg-zinc-950 border border-amber-500/30 text-amber-500 hover:bg-amber-500 hover:text-black transition-all font-bold text-sm uppercase tracking-wider"
                            @click="myReservationsOpen = true"
                        >
                            Mis Reservas
                        </button>

                        <button
                            type="button"
                            class="px-4 py-2 rounded-lg bg-zinc-950 border border-zinc-700 text-zinc-300 hover:border-amber-500/50 hover:text-amber-500 transition-all font-medium text-sm"
                            @click="goToday"
                        >
                            Hoy
                        </button>

                        <button
                            type="button"
                            class="px-4 py-2 rounded-lg bg-zinc-950 border border-zinc-700 text-zinc-300 hover:border-amber-500/50 hover:text-amber-500 transition-all font-medium text-sm"
                            @click="toggleWeekends"
                        >
                            {{ showWeekends ? 'Ocultar' : 'Mostrar' }} fines
                        </button>
                    </div>
                </div>

                <FullCalendar ref="calendarRef" :options="calendarOptions" />

                <!-- Floating button for mobile -->
                <button 
                  v-if="isMobile"
                  @click="createModalOpen = true"
                  class="fixed bottom-6 right-6 z-50 w-14 h-14 bg-amber-500 text-black rounded-full shadow-2xl flex items-center justify-center hover:bg-amber-400 active:scale-90 transition-all"
                >
                  <svg class="w-8 h-8 font-bold" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                  </svg>
                </button>
            </div>
        </main>

        <!-- Footer (Optional, simplified) -->
        <footer class="py-8 border-t border-zinc-900 bg-zinc-950 text-center">
             <p class="text-xs text-zinc-600 uppercase tracking-widest">&copy; 2026 Sunachi Barber. Reservas en línea.</p>
        </footer>

        <!-- Modal (Existing Implementation Wrapper with Dark Styles) -->
        <div v-if="modalOpen" class="fixed inset-0 z-[60] flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-black/80 backdrop-blur-sm" @click="closeModal"></div>

            <div class="relative w-full max-w-lg rounded-2xl border border-zinc-800 bg-zinc-900 p-6 shadow-2xl">
                <div class="flex items-start justify-between gap-3 mb-6">
                    <div>
                        <h2 class="text-xl font-bold text-white">
                            {{ selectedEvent?.title }}
                        </h2>
                        <p class="mt-1 text-sm text-amber-500 font-medium">
                            {{ selectedEventTime }}
                        </p>
                    </div>

                    <button
                        type="button"
                        class="rounded-full p-2 text-zinc-500 hover:bg-zinc-800 hover:text-white transition-colors"
                        @click="closeModal"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>

                <!-- Detalles adicionales solo para el dueño -->
                <div v-if="selectedEvent?.extendedProps?.is_mine" class="mb-6 rounded-xl bg-amber-500/10 border border-amber-500/20 p-4">
                    <div class="grid grid-cols-2 gap-4 text-sm">
                        <div v-if="selectedEvent.extendedProps.service_name" class="col-span-2">
                            <span class="block text-zinc-500 text-xs uppercase font-bold">Servicios</span>
                            <span class="text-zinc-200 font-medium">{{ selectedEvent.extendedProps.service_name }}</span>
                        </div>
                        <div v-if="selectedEvent.extendedProps.price">
                            <span class="block text-zinc-500 text-xs uppercase font-bold">Precio Total</span>
                            <span class="text-zinc-200 font-medium">{{ selectedEvent.extendedProps.price }} Lps</span>
                        </div>
                        <div v-if="selectedEvent.extendedProps.notes" class="col-span-2">
                             <span class="block text-zinc-500 text-xs uppercase font-bold">Notas</span>
                             <span class="text-zinc-200 italic">"{{ selectedEvent.extendedProps.notes }}"</span>
                        </div>
                    </div>
                </div>

                <div class="flex flex-wrap gap-3">
                     <!-- Action Buttons adapted to theme -- Only for Admin/Barber -->
                     <template v-if="$page.props.auth.user && ['admin', 'barber'].includes($page.props.auth.user.role)">
                        
                        <div class="w-full grid grid-cols-2 gap-3 mb-3">
                            <!-- Finalizar -->
                            <button
                                v-if="['pending', 'confirmed'].includes(selectedEvent?.extendedProps?.status)"
                                type="button"
                                class="rounded-lg bg-blue-500/10 text-blue-500 border border-blue-500/20 px-4 py-2 text-sm font-bold uppercase tracking-wide hover:bg-blue-500 hover:text-white transition-colors"
                                @click="updateStatus('completed')"
                            >
                                Finalizar
                            </button>


                            <!-- No asistió -->
                            <button
                                v-if="['pending', 'confirmed'].includes(selectedEvent?.extendedProps?.status)"
                                type="button"
                                class="rounded-lg bg-red-500/10 text-red-500 border border-red-500/20 px-4 py-2 text-sm font-bold uppercase tracking-wide hover:bg-red-500 hover:text-white transition-colors"
                                @click="updateStatus('noshow')"
                            >
                                No asistió
                            </button>

                            <button
                                v-if="['pending', 'confirmed'].includes(selectedEvent?.extendedProps?.status)"
                                type="button"
                                class="col-span-2 rounded-lg bg-zinc-800 text-red-500 border border-red-500/40 px-4 py-2 text-sm font-bold uppercase tracking-wide hover:bg-red-500 hover:text-white transition-colors"
                                @click="updateStatus('cancelled')"
                            >
                                Cancelar Cita (Admin)
                            </button>
                        </div>
                     </template>

                     <template v-else-if="selectedEvent?.extendedProps?.is_mine">
                          <button
                            v-if="['pending', 'confirmed'].includes(selectedEvent?.extendedProps?.status)"
                            type="button"
                            class="w-full mb-3 rounded-lg bg-zinc-800 text-red-500 border border-red-500/40 px-4 py-2 text-sm font-bold uppercase tracking-wide hover:bg-red-500 hover:text-white transition-colors"
                            @click="cancelClientReservation"
                          >
                            Cancelar mi cita
                          </button>
                     </template>

                      <button
                        type="button"
                        class="px-6 py-3 rounded-lg border border-zinc-700 text-zinc-300 font-bold uppercase tracking-wide text-sm hover:bg-zinc-800 hover:text-white transition-colors w-full"
                        @click="closeModal"
                      >
                        Cerrar
                      </button>
                </div>
            </div>
        </div>

        <!-- Modal Create Appointment -->
        <ModalCreateClient 
          v-model="createModalOpen" 
          :initialSelection="initialSelection"
          :services="services"
          :barbers="barbers"
          @created="refreshEvents"
        />

        <!-- Mis Reservas Modal -->
        <ModalMyReservations 
            v-model="myReservationsOpen"
            :lastReservations="lastReservations"
        />
    </div>
</template>
