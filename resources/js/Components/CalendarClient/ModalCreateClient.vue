<script setup>
import { ref, watch, computed, onUnmounted } from 'vue'
import { usePage } from '@inertiajs/vue3'
import axios from 'axios'

const props = defineProps({
  modelValue: Boolean,
  initialSelection: Object, // { start, end }
  services: {
      type: Array,
      default: () => []
  },
  barbers: {
      type: Array,
      default: () => []
  }
})

const emit = defineEmits(['update:modelValue', 'created'])
const page = usePage()
const user = computed(() => page.props.auth.user)

const loading = ref(false)
const formData = ref({
  start_at: '',
  end_at: '',
  // Guest fields
  client_name: '',
  client_phone: '',
  // Common fields
  services: [{ service_id: '', price: 0 }],
  barber_id: '',
  client_notes: '',
})

const totalPrecio = computed(() => {
    return formData.value.services.reduce((sum, item) => sum + (Number(item.price) || 0), 0)
})

const errors = ref({})

// Pre-fill data when selection changes
watch(() => props.initialSelection, (val) => {
  if (val) {
    // Format for datetime-local: YYYY-MM-DDTHH:mm
    formData.value.start_at = val.startStr.slice(0, 16)
    formData.value.end_at = val.endStr ? val.endStr.slice(0, 16) : ''
  }
}, { immediate: true })

const handleSubmit = async () => {
  loading.value = true
  errors.value = {}
  
  const payload = { ...formData.value }
  if (payload.start_at) {
      payload.start_at = new Date(payload.start_at).toISOString()
  }
  if (payload.end_at) {
      payload.end_at = new Date(payload.end_at).toISOString()
  }

  try {
    await axios.post(route('booking.appointments.store'), payload)
    emit('created')
    close()
    // Reset form for guests mostly
    formData.value.client_name = ''
    formData.value.client_phone = ''
    formData.value.client_notes = ''
    formData.value.services = [{ service_id: '', price: 0 }]
  } catch (error) {
    if (error.response?.status === 422) {
      errors.value = error.response.data.errors || { message: [error.response.data.message] }
    } else if (error.response?.data?.message) {
        errors.value = { message: error.response.data.message }
    } else {
      console.error('Error creating appointment', error)
      errors.value = { message: 'Ocurrió un error inesperado.' }
    }
  } finally {
    loading.value = false
  }
}

const close = () => {
  emit('update:modelValue', false)
  formData.value.services = [{ service_id: '', price: 0 }]
}

const addService = () => {
  formData.value.services.push({ service_id: '', price: 0 })
}

const removeService = (index) => {
  formData.value.services.splice(index, 1)
}

const updateServicePrice = (index, serviceId) => {
  const service = props.services.find(s => s.id === serviceId)
  if (service) {
    formData.value.services[index].price = parseFloat(service.price)
  }
}

// Auto-select barber if only one
watch(() => props.barbers, (val) => {
    if (val.length === 1) {
        formData.value.barber_id = val[0].id
    }
}, { immediate: true })

// Modal Logic (Escape key, etc)
const onKeydown = (e) => {
    if (e.key === 'Escape' && props.modelValue) close()
}

watch(() => props.modelValue, (val) => {
    if (val) document.addEventListener('keydown', onKeydown)
    else document.removeEventListener('keydown', onKeydown)
}, { immediate: true })

onUnmounted(() => {
    document.removeEventListener('keydown', onKeydown)
})
</script>

<template>
  <teleport to="body">
    <div v-show="modelValue" class="fixed inset-0 z-[60] overflow-y-auto px-4 py-6 sm:px-0 flex items-center justify-center">
        <!-- Backdrop -->
        <div class="fixed inset-0 transform transition-all" @click="close">
            <div class="absolute inset-0 bg-zinc-950/80 backdrop-blur-sm"></div>
        </div>

        <!-- Panel -->
        <div class="relative w-full max-w-xl transform rounded-2xl bg-zinc-950 border border-zinc-800 shadow-2xl shadow-black transition-all sm:w-full sm:mx-auto">
            
            <!-- Header -->
            <div class="flex items-center justify-between px-6 py-5 border-b border-zinc-800">
                <span class="text-xl font-black italic tracking-tighter text-white uppercase">
                    NUEVA <span class="text-amber-500">RESERVA</span>
                </span>
                <button @click="close" class="text-zinc-500 hover:text-white transition-colors focus:outline-none">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Body -->
            <div class="p-6 space-y-6 max-h-[70vh] overflow-y-auto custom-scrollbar">
                
                <!-- Authenticated User Info -->
                <div v-if="user" class="p-4 rounded-xl bg-amber-500/10 border border-amber-500/20 flex items-center gap-3">
                     <div class="w-10 h-10 rounded-full bg-amber-500 flex items-center justify-center text-black font-bold">
                        {{ user.name.charAt(0) }}
                     </div>
                     <div>
                        <p class="text-xs font-bold uppercase tracking-widest text-amber-500">Reservando como</p>
                        <p class="font-bold text-white text-lg">{{ user.name }}</p>
                     </div>
                </div>

                <!-- Guest Fields -->
                <div v-else class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <div class="space-y-2">
                        <label class="text-xs font-bold uppercase tracking-widest text-zinc-500">Tu Nombre</label>
                        <input 
                            v-model="formData.client_name" 
                            type="text" 
                            class="w-full bg-zinc-950 border border-zinc-800 rounded-lg px-4 py-3 text-white focus:border-amber-500 focus:ring-1 focus:ring-amber-500 transition-all outline-none"
                            placeholder="Ej. Juan Pérez"
                        />
                        <p v-if="errors.client_name" class="text-xs text-red-500 font-bold mt-1">{{ errors.client_name[0] }}</p>
                    </div>
                    <div class="space-y-2">
                        <label class="text-xs font-bold uppercase tracking-widest text-zinc-500">Teléfono</label>
                        <input 
                            v-model="formData.client_phone" 
                            type="text" 
                            class="w-full bg-zinc-950 border border-zinc-800 rounded-lg px-4 py-3 text-white focus:border-amber-500 focus:ring-1 focus:ring-amber-500 transition-all outline-none"
                            placeholder="Ej. 600123456"
                        />
                        <p v-if="errors.client_phone" class="text-xs text-red-500 font-bold mt-1">{{ errors.client_phone[0] }}</p>
                    </div>
                </div>

                <!-- Services Selection -->
                <div class="space-y-2">
                    <div class="flex justify-between items-center mb-1">
                        <label class="text-xs font-bold uppercase tracking-widest text-zinc-500">Servicios</label>
                        <button type="button" @click="addService" class="text-xs flex items-center bg-zinc-900 text-amber-500 px-3 py-1.5 rounded-lg border border-zinc-800 hover:border-amber-500/30 hover:bg-zinc-800 transition-all">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                            Añadir servicio
                        </button>
                    </div>
                
                    <div class="space-y-3">
                        <div v-for="(item, index) in formData.services" :key="index" class="flex gap-2 items-start bg-zinc-900 border border-zinc-800 rounded-lg p-2">
                            <div class="flex-1 relative">
                                <select 
                                    v-model="item.service_id" 
                                    @change="updateServicePrice(index, item.service_id)"
                                    class="w-full appearance-none bg-zinc-950 border border-zinc-800 rounded-lg px-4 py-3 text-white focus:border-amber-500 focus:ring-1 focus:ring-amber-500 transition-all outline-none"
                                >
                                    <option value="" class="bg-zinc-900 text-zinc-500">Selecciona...</option>
                                    <option v-for="service in services" :key="service.id" :value="service.id" class="bg-zinc-900">
                                        {{ service.name }} - {{ service.price }} Lps
                                    </option>
                                </select>

                                <p v-if="errors[`services.${index}.service_id`]" class="text-xs text-red-500 font-bold mt-1">{{ errors[`services.${index}.service_id`][0] }}</p>
                            </div>
                            <button type="button" @click="removeService(index)" v-if="formData.services.length > 1" class="text-zinc-500 hover:text-red-500 p-3 transition-colors mt-1">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                            </button>
                        </div>
                    </div>
                </div>

                 <!-- Barber Selection -->
                 <div class="space-y-2">
                    <label class="text-xs font-bold uppercase tracking-widest text-zinc-500">Profesional</label>
                    <div class="relative">
                        <select 
                            v-model="formData.barber_id" 
                            class="w-full appearance-none bg-zinc-950 border border-zinc-800 rounded-lg px-4 py-3 text-white focus:border-amber-500 focus:ring-1 focus:ring-amber-500 transition-all outline-none"
                        >
                            <option v-for="barber in barbers" :key="barber.id" :value="barber.id" class="bg-zinc-900">
                                {{ barber.name }}
                            </option>
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-zinc-500">
                            <svg class="h-4 w-4 fill-current" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M9.293 12.95l.707.707L15.657 8l-1.414-1.414L10 10.828 5.757 6.586 4.343 8z"/></svg>
                        </div>
                    </div>
                    <p v-if="errors.barber_id" class="text-xs text-red-500 font-bold mt-1">{{ errors.barber_id[0] }}</p>
                </div>

                <!-- Time Selection -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                     <div class="space-y-2">
                        <label class="text-xs font-bold uppercase tracking-widest text-zinc-500">Inicio</label>
                        <input 
                            v-model="formData.start_at" 
                            type="datetime-local" 
                            style="color-scheme: dark;"
                            class="w-full bg-zinc-950 border border-zinc-800 rounded-lg px-4 py-3 text-white focus:border-amber-500 focus:ring-1 focus:ring-amber-500 transition-all outline-none"
                        />
                    </div>
                     <div class="space-y-2">
                        <label class="text-xs font-bold uppercase tracking-widest text-zinc-500">Fin</label>
                        <input 
                            v-model="formData.end_at" 
                            type="datetime-local" 
                            style="color-scheme: dark;"
                            class="w-full bg-zinc-950 border border-zinc-800 rounded-lg px-4 py-3 text-white focus:border-amber-500 focus:ring-1 focus:ring-amber-500 transition-all outline-none"
                        />
                    </div>
                </div>

                <!-- Notes -->
                <div class="space-y-2">
                     <label class="text-xs font-bold uppercase tracking-widest text-zinc-500">Nota (Opcional)</label>
                     <textarea 
                        v-model="formData.client_notes" 
                        rows="2"
                        class="w-full bg-zinc-950 border border-zinc-800 rounded-lg px-4 py-3 text-white focus:border-amber-500 focus:ring-1 focus:ring-amber-500 transition-all outline-none resize-none"
                        placeholder="Detalles adicionales..."
                     ></textarea>
                </div>

                <!-- Total -->
                <div class="flex items-center justify-between p-4 rounded-xl bg-amber-500/10 border border-amber-500/20">
                    <span class="text-sm font-bold uppercase tracking-widest text-amber-500">Total a Pagar</span>
                    <span class="text-2xl font-black text-white">{{ totalPrecio.toFixed(2) }} Lps</span>
                </div>

                <!-- Global Error -->
                <div v-if="errors.message" class="p-4 bg-red-900/30 border border-red-900/50 text-red-200 rounded-lg text-sm font-medium">
                    {{ errors.message }}
                </div>
                
                 <!-- Client Error -->
                 <div v-if="errors.client" class="p-4 bg-red-900/30 border border-red-900/50 text-red-200 rounded-lg text-sm font-medium">
                    {{ errors.client }}
                </div>

            </div>

            <!-- Footer -->
            <div class="px-6 py-4 bg-zinc-900/50 border-t border-zinc-800 flex justify-end gap-3 rounded-b-2xl">
                 <button 
                    @click="close" 
                    class="px-6 py-3 text-xs font-bold uppercase tracking-widest text-zinc-400 hover:text-white transition-colors"
                  >
                    Cancelar
                  </button>
                  <button 
                    @click="handleSubmit" 
                    :disabled="loading"
                    class="px-6 py-3 bg-amber-500 hover:bg-amber-600 text-black text-xs font-bold uppercase tracking-widest rounded-lg transition-all transform hover:scale-105 disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none shadow-lg shadow-amber-500/20"
                  >
                    {{ loading ? 'Procesando...' : 'Confirmar Reserva' }}
                  </button>
            </div>
        </div>
    </div>
  </teleport>
</template>
