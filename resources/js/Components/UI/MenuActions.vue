<script setup>
import { ref, onMounted, onBeforeUnmount } from 'vue'

const props = defineProps({
  isOpen: { type: Boolean, default: false },
})

const emit = defineEmits(['toggle', 'close', 'edit', 'delete'])

const menuRef = ref(null)

const onDocumentClick = (e) => {
  if (!props.isOpen) return
  if (!menuRef.value) return
  if (!menuRef.value.contains(e.target)) emit('close')
}

onMounted(() => document.addEventListener('click', onDocumentClick))
onBeforeUnmount(() => document.removeEventListener('click', onDocumentClick))

const toggle = (e) => {
  e.stopPropagation()
  // Si está abierto -> cerrar; si está cerrado -> pedir al padre abrir este
  props.isOpen ? emit('close') : emit('toggle')
}
</script>

<template>
  <div ref="menuRef" class="relative inline-block">
    <button type="button" @click="toggle" class="hover:text-black transition-colors mt-4 dark:hover:text-white">
      <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
        <path d="M6 10a2 2 0 11-4 0 2 2 0 014 0zM12 10a2 2 0 11-4 0 2 2 0 014 0zM16 12a2 2 0 100-4 2 2 0 000 4z" />
      </svg>
    </button>

    <div
      v-if="isOpen"
      class="absolute right-0 w-48 bg-gray-800 rounded-lg shadow-lg py-2 z-50 "
    >
      <button
        type="button"
        class="block px-4 py-2 text-gray-200 hover:bg-gray-700 w-full text-left"
        @click="emit('edit'); emit('close')"
      >
        Editar
      </button>

      <button
        type="button"
        class="block px-4 py-2 text-gray-200 hover:bg-gray-700 w-full text-left"
        @click="emit('delete'); emit('close')"
      >
        Eliminar
      </button>
    </div>
  </div>
</template>
