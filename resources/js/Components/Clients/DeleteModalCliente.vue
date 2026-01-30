<script setup>
import ModalBase from '@/Components/UI/ModalBase.vue'
import { useForm } from '@inertiajs/vue3'

const props = defineProps({
    modelValue: { type: Boolean, default: false },
    client: Object,
})

const emit = defineEmits(['update:modelValue'])

const close = () => emit('update:modelValue', false)

const form = useForm({})

const submit = () => {
    form.delete(route('clients.destroy', props.client.id), {
        preserveScroll: true,
        onSuccess: () => {
            close()
        },
    })
}
</script>

<template>
    <ModalBase
        :modelValue="modelValue"
        @update:modelValue="v => emit('update:modelValue', v)"
        maxWidth="max-w-lg"
    >
        <template #title>Eliminar cliente</template>

        <div class="space-y-4">
            <p class="text-sm text-gray-600 dark:text-white">
                ¿Estás seguro de eliminar al cliente <span class="font-bold text-blue-600">{{ client.name }}</span>? 
                <p class="mt-3">Podra recuperarlo desde la papelera.</p>
            </p>
        </div>

        <template #footer>
            <button type="button" class="px-4 py-2 rounded-lg bg-gray-800 text-gray-200 hover:bg-gray-700" @click="close">
                Cancelar
            </button>

            <button
                type="button"
                class="px-4 py-2 rounded-lg bg-red-600 text-white hover:bg-red-700"
                @click="submit"
            >
                Eliminar
            </button>
        </template>
    </ModalBase>
</template>