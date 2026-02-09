<script setup>
import { ref } from 'vue'
import ModalBase from '../UI/ModalBase.vue'
import { useForm } from '@inertiajs/vue3'
import { watch } from 'vue'

const props = defineProps({
    modelValue: Boolean,
})

const form = useForm({
    name: '',
    phone: '',
    email: '',
    is_active: 1,
    notes: '',
})

const emit = defineEmits(['update:modelValue'])

const close = () => emit('update:modelValue', false)

watch(
  () => props.modelValue,
  (open) => {
    if (!open) return
    form.clearErrors()
  }
)

const submitForm = () => {
    form.post(route('barbers.store'), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset()
            close()
        },
    })
}
</script>

<template>
    <ModalBase :modelValue="modelValue" @update:modelValue="v => emit('update:modelValue', v)">
        <template #title>
            Añadir barbero
        </template>

        <!-- Aviso -->
        <p class="text-xs text-gray-600 dark:text-gray-400 mb-4">
            Los campos marcados con <span class="text-red-500">*</span> son obligatorios
        </p>
        
        <form @submit.prevent="submitForm">
            <div class="mb-4">
                <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-200">Nombre<span class="text-red-500">*</span></label>
                <input type="text" id="name" v-model="form.name" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm bg-white dark:bg-gray-900 dark:border-gray-700 dark:text-gray-200" placeholder="John Doe">
            </div>
            <div class="mb-4">
                <label for="phone" class="block text-sm font-medium text-gray-700 dark:text-gray-200">Teléfono<span class="text-red-500">*</span></label>
                <input type="text" id="phone" v-model="form.phone" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm bg-white dark:bg-gray-900 dark:border-gray-700 dark:text-gray-200" placeholder="123456789">
            </div>
            <div class="mb-4">
                <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-200">Email<span class="text-red-500">*</span></label>
                <input type="email" id="email" v-model="form.email" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm bg-white dark:bg-gray-900 dark:border-gray-700 dark:text-gray-200" placeholder="John@example.com">
            </div>
            <div class="mb-4">
                <label for="notes" class="block text-sm font-medium text-gray-700 dark:text-gray-200">Notas</label>
                <textarea id="notes" v-model="form.notes" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm bg-white dark:bg-gray-900 dark:border-gray-700 dark:text-gray-200" placeholder="Deja una nota sobre el barbero"></textarea>
            </div>
        </form>

        <template #footer>
            <button type="button" @click="close" class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600 dark:focus:ring-gray-600">
                Cancelar
            </button>
            <button type="submit" @click="submitForm" class="ml-3 rounded-md border border-transparent bg-blue-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:border-blue-600 dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-600">
                Guardar
            </button>
        </template>
    </ModalBase>
</template>