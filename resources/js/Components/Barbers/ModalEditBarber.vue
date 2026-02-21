<script setup>
import { ref, watch } from 'vue'
import ModalBase from '../UI/ModalBase.vue'
import { useForm } from '@inertiajs/vue3'

const props = defineProps({
  modelValue: Boolean,
  barber: Object,
})

const emit = defineEmits(['update:modelValue', 'updated'])

const form = useForm({
  name: '',
  phone: '',
  email: '',
  notes: '',
  is_active: true,
})

watch(() => props.barber, (newBarber) => {
  if (newBarber) {
    form.name = newBarber.name
    form.phone = newBarber.phone
    form.email = newBarber.email
    form.notes = newBarber.notes
    form.is_active = !!newBarber.is_active
    form.clearErrors()
  }
}, { immediate: true })

const close = () => {
  emit('update:modelValue', false)
}

const updateBarber = () => {
  form.put(route('barbers.update', props.barber.id), {
    onSuccess: () => {
      close()
      emit('updated')
    }
  })
}
</script>

<template>
    <ModalBase :modelValue="modelValue" @update:modelValue="close">
        <template #title>
            Editar barbero
        </template>

        <!-- Aviso -->
        <p class="text-xs text-gray-600 dark:text-gray-400 mb-4">
            Los campos marcados con <span class="text-red-500">*</span> son obligatorios
        </p>
        
        <div class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nombre<span class="text-red-500">*</span></label>
                <input v-model="form.name" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm bg-white dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                <div v-if="form.errors.name" class="text-red-500 text-xs mt-1">{{ form.errors.name }}</div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Teléfono <span class="text-red-500">*</span></label>
                <input v-model="form.phone" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm bg-white dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                 <div v-if="form.errors.phone" class="text-red-500 text-xs mt-1">{{ form.errors.phone }}</div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Email <span class="text-red-500">*</span></label>
                <input v-model="form.email" type="email" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm bg-white dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                 <div v-if="form.errors.email" class="text-red-500 text-xs mt-1">{{ form.errors.email }}</div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Notas</label>
                <textarea v-model="form.notes" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm bg-white dark:bg-gray-700 dark:border-gray-600 dark:text-white"></textarea>
                 <div v-if="form.errors.notes" class="text-red-500 text-xs mt-1">{{ form.errors.notes }}</div>
            </div>
            <div class="flex items-center">
                <input v-model="form.is_active" type="checkbox" class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600">
                <label class="ml-2 block text-sm text-gray-900 dark:text-gray-300">Activo</label>
            </div>
        </div>

        <template #footer>
            <button @click="close" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-600">
                Cancelar
            </button>
            <button @click="updateBarber" :disabled="form.processing" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50">
                Guardar
            </button>
        </template>
    </ModalBase>
</template>