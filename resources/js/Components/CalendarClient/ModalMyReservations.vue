<script setup>
import { computed } from 'vue'

const props = defineProps({
  modelValue: Boolean,
  lastReservations: {
    type: Array,
    default: () => []
  }
})

const emit = defineEmits(['update:modelValue'])

const close = () => {
  emit('update:modelValue', false)
}

// Helper para formatear fecha
const formatDate = (dateString) => {
  if (!dateString) return ''
  try {
    const date = new Date(dateString)
    return new Intl.DateTimeFormat('es-ES', {
      weekday: 'long',
      day: 'numeric',
      month: 'long',
      hour: '2-digit',
      minute: '2-digit'
    }).format(date)
  } catch {
    return dateString
  }
}

const mapStatus = {
  confirmed: 'Confirmado',
  pending: 'Pendiente',
  noshow: 'No se presentó',
  completed: 'Finalizado',
  cancelled: 'Cancelado',
}

const statusColor = (status) => {
    switch (status) {
        case 'confirmed': return 'text-emerald-400 bg-emerald-400/10 border-emerald-400/20'
        case 'pending': return 'text-amber-400 bg-amber-400/10 border-amber-400/20'
        case 'completed': return 'text-blue-400 bg-blue-400/10 border-blue-400/20'
        case 'cancelled': return 'text-red-400 bg-red-400/10 border-red-400/20'
        default: return 'text-zinc-400 bg-zinc-400/10 border-zinc-400/20'
    }
}
</script>

<template>
  <div v-if="modelValue" class="fixed inset-0 z-50 flex items-center justify-center p-4">
      <!-- Backdrop -->
      <div class="absolute inset-0 bg-black/80 backdrop-blur-sm" @click="close"></div>

      <!-- Modal Card -->
      <div class="relative w-full max-w-lg bg-zinc-900 border border-zinc-800 rounded-2xl shadow-2xl overflow-hidden flex flex-col max-h-[90vh]">
          <!-- Header -->
          <div class="flex items-center justify-between px-6 py-5 border-b border-zinc-800 bg-zinc-900/50">
              <h3 class="text-xl font-bold text-white uppercase tracking-wider">
                  Mis Reservas <span class="text-amber-500 text-sm align-super ml-1">recientes</span>
              </h3>
              <button 
                  @click="close"
                  class="p-2 text-zinc-500 hover:text-white transition-colors rounded-full hover:bg-zinc-800"
              >
                  <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                  </svg>
              </button>
          </div>

          <!-- Body -->
          <div class="flex-1 overflow-y-auto p-6 space-y-4">
              <div v-if="lastReservations.length === 0" class="text-center py-10 text-zinc-500">
                  <svg class="w-12 h-12 mx-auto mb-3 opacity-20" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                  </svg>
                  <p>No tienes reservas recientes.</p>
              </div>

              <div 
                v-for="reservation in lastReservations" 
                :key="reservation.id"
                class="group bg-zinc-950 border border-zinc-800 rounded-xl p-4 hover:border-amber-500/30 transition-all flex items-start gap-4"
              >
                  <!-- Date Badge -->
                  <div class="flex flex-col items-center bg-zinc-900 rounded-lg p-2 min-w-[3.5rem] border border-zinc-800">
                       <span class="text-xs font-bold text-amber-500 text-center uppercase">
                           {{ new Date(reservation.start_at).toLocaleString('es-ES', { month: 'short' }) }}
                       </span>
                       <span class="text-xl font-black text-white">
                           {{ new Date(reservation.start_at).getDate() }}
                       </span>
                  </div>

                  <!-- Info -->
                  <div class="flex-1 min-w-0">
                      <div class="flex items-center justify-between mb-1">
                          <h4 class="text-white font-bold truncate pr-2">
                              {{ reservation.service?.name || 'Servicio' }}
                          </h4>
                          <span 
                            class="px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider rounded border"
                            :class="statusColor(reservation.status)"
                          >
                            {{ mapStatus[reservation.status] || reservation.status }}
                          </span>
                      </div>
                      <p class="text-sm text-zinc-400 flex items-center gap-1.5">
                          <svg class="w-4 h-4 text-zinc-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                          </svg>
                          {{ new Date(reservation.start_at).toLocaleTimeString('es-ES', { hour: '2-digit', minute:'2-digit' }) }}
                      </p>
                      <p v-if="reservation.barber?.name" class="text-xs text-zinc-500 mt-1">
                          Barbero: {{ reservation.barber.name }}
                      </p>
                  </div>
              </div>
          </div>

          <!-- Footer -->
          <div class="p-6 border-t border-zinc-800 bg-zinc-900/50 flex justify-end">
              <button 
                  @click="close"
                  class="px-6 py-3 rounded-lg border border-zinc-700 text-zinc-300 font-bold uppercase tracking-wider text-xs hover:bg-zinc-800 hover:text-white transition-colors"
              >
                  Cerrar
              </button>
          </div>
      </div>
  </div>
</template>
