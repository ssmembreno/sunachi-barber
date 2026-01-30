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
            'w-full rounded-xl shadow-2xl',
            'border border-gray-200 bg-white',
            'dark:border-gray-800 dark:bg-black',
            props.maxWidth
          ]"
        >
          <!-- Header -->
          <div class="flex items-center justify-between px-5 py-4 border-b border-gray-200 dark:border-gray-800">
            <h3 class="text-gray-900 dark:text-white font-semibold">
              <slot name="title" />
            </h3>

            <button
              class="text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white"
              type="button"
              @click="close"
            >
              ✕
            </button>
          </div>

          <!-- Body -->
          <div class="px-5 py-4 text-black dark:text-gray-200">
            <slot />
          </div>

          <!-- Footer -->
          <div class="px-5 py-4 border-t border-gray-200 dark:border-gray-800 flex justify-end gap-2">
            <slot name="footer" :close="close" />
          </div>
        </div>
      </div>
    </div>
  </teleport>
</template>
