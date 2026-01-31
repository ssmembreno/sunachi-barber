
<script setup>
import { ref, computed } from 'vue'
import axios from 'axios'
import FullCalendar from '@fullcalendar/vue3'
import dayGridPlugin from '@fullcalendar/daygrid'
import timeGridPlugin from '@fullcalendar/timegrid'
import interactionPlugin from '@fullcalendar/interaction'
import AppLayout from '@/Layouts/AppLayout.vue'
import ModalCreateAdmin from '@/Components/CalendarAdmin/ModalCreateAdmin.vue'

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
  longPressDelay: 350,
  selectLongPressDelay: 350,
  eventLongPressDelay: 350,
  weekends: showWeekends.value,
  height: 'auto',
  stickyHeaderDates: true,

  // Carga dinámica desde backend
  events: async (info, successCallback, failureCallback) => {
    try {
      const { data } = await axios.get(route('calendar.events'), {
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
  eventClassNames: (arg) => {
    const status = arg.event.extendedProps?.status
    return [`fc-status-${status || 'pending'}`]
  },

  // Click en evento
  eventClick: (info) => {
    selectedEvent.value = info.event
    modalOpen.value = true
  },

  // Selección de franja
  select: (selectionInfo) => {
    initialSelection.value = selectionInfo
    createModalOpen.value = true
  },
}))
</script>

<style>
/* Ajustes visuales base */
.fc {
  font-family: 'Inter', ui-sans-serif, system-ui, sans-serif;
  --fc-border-color: rgba(229, 231, 235, 0.5);
  --fc-today-bg-color: rgba(59, 130, 246, 0.05);
}

.dark .fc {
  --fc-border-color: rgba(75, 85, 99, 0.3);
  --fc-today-bg-color: rgba(59, 130, 246, 0.1);
  --fc-page-bg-color: #111827;
  --fc-neutral-bg-color: #1f2937;
  --fc-list-event-hover-bg-color: #1f2937;
}

/* Toolbars */
.fc .fc-toolbar {
  @apply mb-8 gap-4 flex-wrap justify-center sm:justify-between !important;
}

.fc .fc-toolbar-title {
  @apply text-xl font-bold text-gray-800 dark:text-gray-100 capitalize transition-all !important;
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

/* Botones con estilo moderno */
.fc .fc-button {
  @apply border-gray-200 bg-white text-gray-700 font-semibold px-4 py-2 text-sm shadow-sm transition-all hover:bg-gray-50 focus:ring-2 focus:ring-blue-500 focus:outline-none dark:bg-gray-800 dark:border-gray-700 dark:text-gray-200 dark:hover:bg-gray-700 !important;
}

.fc .fc-button-primary:not(:disabled).fc-button-active,
.fc .fc-button-primary:not(:disabled):active {
  @apply bg-blue-600 border-blue-600 text-white shadow-md shadow-blue-500/30 hover:bg-blue-700 !important;
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
  @apply py-4 bg-gray-50/80 dark:bg-gray-800/80 backdrop-blur-md !important;
}

.fc .fc-col-header-cell-cushion {
  @apply text-gray-600 dark:text-gray-300 font-semibold !important;
}

/* Horas lateral */
.fc .fc-timegrid-slot-label-cushion, 
.fc .fc-timegrid-axis-cushion {
  @apply text-gray-500 dark:text-gray-400 font-medium text-xs !important;
}

/* Indicador de "Ahora" */
.fc .fc-timegrid-now-indicator-line {
  border-color: #3b82f6;
  border-width: 2px;
}
.fc .fc-timegrid-now-indicator-arrow {
  border-color: #3b82f6;
  border-top-color: transparent;
  border-bottom-color: transparent;
}

/* Eventos Premium */
.fc-v-event {
  @apply rounded-xl border-l-4 shadow-md transition-all hover:scale-[1.02] active:scale-95 !important;
  border-top: none !important;
  border-right: none !important;
  border-bottom: none !important;
}

.fc-status-pending {
  background: #fffbeb !important;
  border-color: #f59e0b !important;
  color: #92400e !important;
}
.dark .fc-status-pending {
  background: rgba(245, 158, 11, 0.15) !important;
  color: #fbbf24 !important;
}

.fc-status-confirmed {
  background: #eff6ff !important;
  border-color: #3b82f6 !important;
  color: #1e40af !important;
}
.dark .fc-status-confirmed {
  background: rgba(59, 130, 246, 0.15) !important;
  color: #93c5fd !important;
}

.fc-status-cancelled,
.fc-status-noshow {
  background: #fef2f2 !important;
  border-color: #ef4444 !important;
  color: #991b1b !important;
  @apply opacity-70 grayscale-[0.3] !important;
}
.dark .fc-status-cancelled,
.dark .fc-status-noshow {
  background: rgba(239, 68, 68, 0.15) !important;
  color: #f87171 !important;
}

.fc-status-completed {
  background: #f0fdf4 !important;
  border-color: #10b981 !important;
  color: #065f46 !important;
  @apply opacity-80 !important;
}
.dark .fc-status-completed {
  background: rgba(16, 185, 129, 0.1) !important;
  color: #34d399 !important;
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
.dark .fc-daygrid-day-number {
  @apply text-gray-300 !important;
}
.dark .fc-daygrid-day:hover {
  @apply bg-gray-800/50 !important;
}

.fc .fc-v-event .fc-event-main,
.fc .fc-v-event .fc-event-main-frame,
.fc .fc-h-event .fc-event-main,
.fc .fc-h-event .fc-event-main-frame {
  color: inherit !important;
  margin-left: 10px !important;
}
</style>

<template>
<AppLayout>
  <div class="p-6">
    <!-- Header -->
    <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
      <div>
        <h1 class="text-xl font-semibold text-gray-900 dark:text-gray-100">Agenda</h1>
        <p class="text-sm text-gray-500 dark:text-gray-400">
          Gestiona citas (pendientes, confirmadas, canceladas).
        </p>
      </div>

      <!-- Quick filters (solo UI por ahora) -->
      <div class="flex items-center gap-2">
        <button
          type="button"
          class="relative rounded-2xl border border-gray-200 bg-white p-2 sm:p-4 shadow-xl dark:border-gray-800 dark:bg-gray-900 transition-all"
          @click="goToday"
        >
          Hoy
        </button>

        <button
          type="button"
          class="rounded-xl border border-gray-200 bg-white px-3 py-2 text-sm text-gray-700 shadow-sm hover:bg-gray-50 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-200 dark:hover:bg-gray-800"
          @click="toggleWeekends"
        >
          {{ showWeekends ? 'Ocultar' : 'Mostrar' }} fines
        </button>
      </div>
    </div>

    <!-- Calendar card -->
    <div class="relative rounded-2xl border border-gray-200 bg-white p-2 sm:p-4 shadow-xl dark:border-gray-800 dark:bg-gray-900 transition-all">
      <FullCalendar ref="calendarRef" :options="calendarOptions" />
      
      <!-- Botón flotante para móvil -->
      <button 
        v-if="isMobile"
        @click="createModalOpen = true"
        class="fixed bottom-6 right-6 z-40 w-14 h-14 bg-blue-600 text-white rounded-full shadow-2xl flex items-center justify-center hover:bg-blue-700 active:scale-90 transition-all"
      >
        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
        </svg>
      </button>
    </div>

    <!-- Modal simple (click evento) -->
    <div v-if="modalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4">
      <div class="absolute inset-0 bg-black/50" @click="closeModal"></div>

      <div class="relative w-full max-w-lg rounded-2xl border border-gray-200 bg-white p-5 shadow-xl dark:border-gray-800 dark:bg-gray-900">
        <div class="flex items-start justify-between gap-3">
          <div>
            <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
              {{ selectedEvent?.title }}
            </h2>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
              {{ selectedEventTime }}
            </p>
            <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
              Estado:
              <span class="font-medium">{{ mapStatus[selectedEvent?.extendedProps?.status] || 'N/A' }}</span>
            </p>
          </div>

          <button
            type="button"
            class="rounded-xl px-2 py-1 text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-800"
            @click="closeModal"
            aria-label="Cerrar"
          >
            ✕
          </button>
        </div>

        <div class="mt-5 flex flex-wrap gap-2">
          <button
            v-if="selectedEvent?.extendedProps?.status === 'confirmed' || selectedEvent?.extendedProps?.status === 'pending'"
            type="button"
            class="rounded-xl bg-green-600 px-4 py-2 text-sm font-medium text-white hover:bg-green-700 transition-colors"
            @click="updateStatus('completed')"
          >
            Finalizar Cita
          </button>
          
          <button
            v-if="selectedEvent?.extendedProps?.status !== 'noshow' && selectedEvent?.extendedProps?.status !== 'cancelled' && selectedEvent?.extendedProps?.status !== 'completed'"
            type="button"
            class="rounded-xl bg-gray-200 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-300 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 transition-colors"
            @click="updateStatus('noshow')"
          >
            No se presentó
          </button>

          <button
            type="button"
            class="rounded-xl border border-gray-200 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 dark:hover:bg-gray-800 transition-colors"
            @click="closeModal"
          >
            Cerrar
          </button>
        </div>
      </div>
    </div>

    <!-- Modal para crear cita -->
    <ModalCreateAdmin 
      v-model="createModalOpen" 
      :initialSelection="initialSelection"
      @created="refreshEvents"
    />
  </div>
</AppLayout>
</template>
