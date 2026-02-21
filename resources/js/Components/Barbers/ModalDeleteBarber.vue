<script setup>
import ModalBase from '@/Components/UI/ModalBase.vue'
import { useForm } from '@inertiajs/vue3'

const props = defineProps({
    modelValue: { type: Boolean, default: false },
    barber: Object,
})

const emit = defineEmits(['update:modelValue'])

const close = () => emit('update:modelValue', false)

const form = useForm({})

const submit = () => {
    form.delete(route('barbers.destroy', props.barber.id), {
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
        <template #title>Eliminar barbero</template>

        <div class="space-y-4">
            <p class="text-sm text-gray-600 dark:text-white">
                ¿Estás seguro de eliminar el barbero <span class="font-bold text-blue-600">{{ barber.name }}</span>? 
                <p class="mt-3">Esta acción no se puede deshacer.</p>
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