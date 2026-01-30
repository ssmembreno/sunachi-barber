<script setup>
import ModalBase from '@/Components/UI/ModalBase.vue'

const props = defineProps({
  modelValue: { type: Boolean, default: false },
  title: { type: String, default: 'Confirmar acción' },
  message: { type: String, default: '' },
  confirmText: { type: String, default: 'Confirmar' },
  confirmColor: { type: String, default: 'bg-red-600 hover:bg-red-700' },
})

const emit = defineEmits(['update:modelValue', 'confirm'])

const close = () => emit('update:modelValue', false)
const confirm = () => emit('confirm')
</script>

<template>
  <ModalBase
    :modelValue="modelValue"
    @update:modelValue="v => emit('update:modelValue', v)"
    maxWidth="max-w-md"
  >
    <template #title>{{ title }}</template>

    <p class="text-sm text-gray-700 dark:text-gray-300">
      {{ message }}
    </p>

    <template #footer>
      <button
        type="button"
        class="px-4 py-2 rounded-lg bg-gray-200 text-gray-800
               hover:bg-gray-300
               dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700"
        @click="close"
      >
        Cancelar
      </button>

      <button
        type="button"
        class="px-4 py-2 rounded-lg text-white"
        :class="confirmColor"
        @click="confirm"
      >
        {{ confirmText }}
      </button>
    </template>
  </ModalBase>
</template>
