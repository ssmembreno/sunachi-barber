<script setup>
import { ref, onMounted, watch } from 'vue'
import axios from 'axios'
import ModalBase from '@/Components/UI/ModalBase.vue'

const props = defineProps({
  modelValue: Boolean,
  initialSelection: Object, // { start, end }
})

const emit = defineEmits(['update:modelValue', 'created'])

const loading = ref(false)
const formData = ref({
  start_at: '',
  end_at: '',
  client_id: '',
  service_id: '',
  barber_id: '', // For now we'll pick the first user or let them choose
  status: 'confirmed',
  source: 'admin',
  notes: '',
  client_notes: '',
  price: '',
  created_by: '',
})

const clients = ref([])
const services = ref([])
const barbers = ref([])
const errors = ref({})

const fetchOptions = async () => {
  try {
    const { data } = await axios.get(route('calendar.formData'))
    clients.value = data.clients
    services.value = data.services
    barbers.value = data.barbers
    
    // Set defaults if available
    if (barbers.value.length > 0) formData.value.barber_id = barbers.value[0].id
    if (data.current_user_id) formData.value.created_by = data.current_user_id
  } catch (error) {
    console.error('Error fetching modal options', error)
  }
}

watch(() => props.initialSelection, (val) => {
  if (val) {
    formData.value.start_at = val.startStr.split('+')[0] // Format for datetime-local
    formData.value.end_at = val.endStr.split('+')[0]
  }
}, { immediate: true })

onMounted(() => {
  fetchOptions()
})

const handleSubmit = async () => {
  loading.value = true
  errors.value = {}
  try {
    // Clone data to avoid modifying the form while sending
    const payload = { ...formData.value }
    
    // Ensure we send ISO UTC strings so backend stores the correct absolute time
    if (payload.start_at) {
        payload.start_at = new Date(payload.start_at).toISOString()
    }
    if (payload.end_at) {
        payload.end_at = new Date(payload.end_at).toISOString()
    }

    await axios.post(route('calendar.store'), payload)
    emit('created')
    close()
  } catch (error) {
    if (error.response?.status === 422) {
      errors.value = error.response.data.errors || { message: [error.response.data.message] }
    } else {
      console.error('Error creating appointment', error)
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
  const service = services.value.find(s => s.id === val)
  if (service) {
    formData.value.price = service.price
  }
})
</script>

<template>
  <ModalBase :modelValue="modelValue" @update:modelValue="close" maxWidth="max-w-2xl">
    <template #title>Nueva Cita</template>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <!-- Cliente -->
      <div class="md:col-span-2">
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Cliente</label>
        <select 
          v-model="formData.client_id" 
          class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-900 dark:border-gray-700"
          :class="{ 'border-red-500': errors.client_id }"
        >
          <option value="">Seleccione un cliente</option>
          <option v-for="client in clients" :key="client.id" :value="client.id">
            {{ client.name }} ({{ client.phone }})
          </option>
        </select>
        <p v-if="errors.client_id" class="mt-1 text-xs text-red-500">{{ errors.client_id[0] }}</p>
      </div>

      <!-- Servicio -->
      <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Servicio</label>
        <select 
          v-model="formData.service_id" 
          class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-900 dark:border-gray-700"
          :class="{ 'border-red-500': errors.service_id }"
        >
          <option value="">Seleccione un servicio</option>
          <option v-for="service in services" :key="service.id" :value="service.id">
            {{ service.name }} - ${{ service.price }}
          </option>
        </select>
        <p v-if="errors.service_id" class="mt-1 text-xs text-red-500">{{ errors.service_id[0] }}</p>
      </div>

      <!-- Barbero -->
      <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Barbero</label>
        <select 
          v-model="formData.barber_id" 
          class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-900 dark:border-gray-700"
          :class="{ 'border-red-500': errors.barber_id }"
        >
          <option v-for="barber in barbers" :key="barber.id" :value="barber.id">
            {{ barber.name }}
          </option>
        </select>
        <p v-if="errors.barber_id" class="mt-1 text-xs text-red-500">{{ errors.barber_id[0] }}</p>
      </div>

      <!-- Fecha Inicio -->
      <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Inicio</label>
        <input 
          v-model="formData.start_at" 
          type="datetime-local" 
          class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-900 dark:border-gray-700 text-sm"
          :class="{ 'border-red-500': errors.start_at }"
        />
        <p v-if="errors.start_at" class="mt-1 text-xs text-red-500">{{ errors.start_at[0] }}</p>
      </div>

      <!-- Fecha Fin -->
      <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Fin</label>
        <input 
          v-model="formData.end_at" 
          type="datetime-local" 
          class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-900 dark:border-gray-700 text-sm"
          :class="{ 'border-red-500': errors.end_at }"
        />
        <p v-if="errors.end_at" class="mt-1 text-xs text-red-500">{{ errors.end_at[0] }}</p>
      </div>

      <!-- Precio -->
      <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Precio</label>
        <input 
          v-model="formData.price" 
          type="number" 
          step="0.01" 
          class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-900 dark:border-gray-700"
        />
      </div>

      <!-- Notas -->
      <div class="md:col-span-2">
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Notas internas</label>
        <textarea 
          v-model="formData.notes" 
          rows="2"
          class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-900 dark:border-gray-700"
        ></textarea>
      </div>

      <!-- Error general (solapes) -->
      <div v-if="errors.message" class="md:col-span-2 p-3 bg-red-100 border border-red-200 text-red-700 rounded-xl text-sm">
        {{ errors.message }}
      </div>
    </div>

    <template #footer>
      <button 
        @click="close" 
        class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-xl hover:bg-gray-50"
      >
        Cancelar
      </button>
      <button 
        @click="handleSubmit" 
        :disabled="loading"
        class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-xl hover:bg-indigo-700 disabled:opacity-50"
      >
        {{ loading ? 'Creando...' : 'Crear Cita' }}
      </button>
    </template>
  </ModalBase>
</template>
