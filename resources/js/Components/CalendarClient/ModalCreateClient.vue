<script setup>
import { ref, watch, computed } from 'vue'
import { usePage } from '@inertiajs/vue3'
import axios from 'axios'
import ModalBase from '@/Components/UI/ModalBase.vue'

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
  service_id: '',
  barber_id: '',
  client_notes: '',
  price: '',
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
    formData.value.service_id = ''
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
}

// Update price when service changes
watch(() => formData.value.service_id, (val) => {
  const service = props.services.find(s => s.id === val)
  if (service) {
    formData.value.price = service.price
  } else {
      formData.value.price = ''
  }
})

// Auto-select barber if only one
watch(() => props.barbers, (val) => {
    if (val.length === 1) {
        formData.value.barber_id = val[0].id
    }
}, { immediate: true })

</script>

<template>
  <ModalBase :modelValue="modelValue" @update:modelValue="close" maxWidth="max-w-xl">
    <template #title>
        <span class="text-xl font-black italic tracking-tighter text-white uppercase">
            NUEVA <span class="text-amber-500">RESERVA</span>
        </span>
    </template>

    <div class="space-y-6">
        
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

        <!-- Service Selection -->
        <div class="space-y-2">
            <label class="text-xs font-bold uppercase tracking-widest text-zinc-500">Servicio</label>
            <div class="relative">
                <select 
                    v-model="formData.service_id" 
                    class="w-full appearance-none bg-zinc-950 border border-zinc-800 rounded-lg px-4 py-3 text-white focus:border-amber-500 focus:ring-1 focus:ring-amber-500 transition-all outline-none"
                >
                    <option value="" class="bg-zinc-900 text-zinc-500">Selecciona un servicio...</option>
                    <option v-for="service in services" :key="service.id" :value="service.id" class="bg-zinc-900">
                        {{ service.name }} - ${{ service.price }}
                    </option>
                </select>
                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-zinc-500">
                    <svg class="h-4 w-4 fill-current" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M9.293 12.95l.707.707L15.657 8l-1.414-1.414L10 10.828 5.757 6.586 4.343 8z"/></svg>
                </div>
            </div>
            <p v-if="errors.service_id" class="text-xs text-red-500 font-bold mt-1">{{ errors.service_id[0] }}</p>
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

        <!-- Global Error -->
        <div v-if="errors.message" class="p-4 bg-red-900/30 border border-red-900/50 text-red-200 rounded-lg text-sm font-medium">
            {{ errors.message }}
        </div>
        
         <!-- Client Error -->
         <div v-if="errors.client" class="p-4 bg-red-900/30 border border-red-900/50 text-red-200 rounded-lg text-sm font-medium">
            {{ errors.client }}
        </div>

    </div>

    <template #footer>
      <button 
        @click="close" 
        class="px-6 py-3 text-xs font-bold uppercase tracking-widest text-zinc-400 hover:text-white transition-colors"
      >
        Cancelar
      </button>
      <button 
        @click="handleSubmit" 
        :disabled="loading"
        class="ml-3 px-6 py-3 bg-amber-500 hover:bg-amber-600 text-black text-xs font-bold uppercase tracking-widest rounded-lg transition-all transform hover:scale-105 disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none shadow-lg shadow-amber-500/20"
      >
        {{ loading ? 'Procesando...' : 'Confirmar Reserva' }}
      </button>
    </template>
  </ModalBase>
</template>
