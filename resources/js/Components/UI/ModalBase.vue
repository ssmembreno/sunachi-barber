<script setup>
import { watch } from 'vue'

const props = defineProps({
  modelValue: { type: Boolean, default: false },
  maxWidth: { type: String, default: 'max-w-lg' },
  closeOnBackdrop: { type: Boolean, default: true },
  closeOnEsc: { type: Boolean, default: true },
})

const emit = defineEmits(['update:modelValue'])

const close = () => emit('update:modelValue', false)

const onBackdrop = () => {
  if (props.closeOnBackdrop) close()
}

const onKeydown = (e) => {
  if (!props.closeOnEsc) return
  if (e.key === 'Escape' && props.modelValue) close()
}

watch(
  () => props.modelValue,
  (open) => {
    if (open) document.addEventListener('keydown', onKeydown)
    else document.removeEventListener('keydown', onKeydown)
  },
  { immediate: true }
)
</script>

<template>
  <teleport to="body">
    <div v-show="props.modelValue" class="fixed inset-0 z-[60]">
      <!-- Overlay -->
      <div
        class="absolute inset-0 bg-black/50 dark:bg-black/75"
        @click="onBackdrop"
      />

      <!-- Panel -->
      <div class="relative h-full w-full flex items-center justify-center p-4">
        <div
          :class="[
            'w-full rounded-2xl shadow-2xl flex flex-col max-h-[90vh]',
            'border border-gray-200 dark:border-zinc-800 bg-white dark:bg-zinc-900',
            props.maxWidth
          ]"
        >
          <!-- Header -->
          <div class="flex-shrink-0 flex items-center justify-between px-6 py-5 border-b border-gray-200 dark:border-zinc-800">
            <h3 class="text-gray-900 dark:text-white font-semibold flex-1">
              <slot name="title" />
            </h3>

            <button
              class="ml-4 text-gray-400 hover:text-gray-500 dark:text-zinc-500 dark:hover:text-white transition-colors"
              type="button"
              @click="close"
            >
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
          </div>

          <!-- Body -->
          <div class="flex-1 overflow-y-auto px-6 py-6 text-gray-600 dark:text-zinc-300">
            <slot />
          </div>

          <!-- Footer -->
          <div class="flex-shrink-0 px-6 py-5 border-t border-gray-200 dark:border-zinc-800 flex justify-end gap-3 bg-gray-50 dark:bg-zinc-900/50 rounded-b-2xl">
            <slot name="footer" :close="close" />
          </div>
        </div>
      </div>
    </div>
  </teleport>
</template>
