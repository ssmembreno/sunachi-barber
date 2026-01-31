<script setup>
import ModalBase from '@/Components/UI/ModalBase.vue'
import { useForm } from '@inertiajs/vue3'
import { watch } from 'vue'

const props = defineProps({
  modelValue: { type: Boolean, default: false },
})

const emit = defineEmits(['update:modelValue'])

const form = useForm({
  name: '',
  phone: '',
  notes: '',
})

const close = () => emit('update:modelValue', false)

watch(
  () => props.modelValue,
  (open) => {
    if (!open) return
    form.clearErrors()
  }
)

const submit = () => {
  form.post(route('clients.store'), {
    preserveScroll: true,
    onSuccess: () => {
      form.reset()
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
    <!-- Aviso -->
    <p class="text-xs text-gray-600 dark:text-gray-400 mb-4">
      Los campos marcados con <span class="text-red-500">*</span> son obligatorios
    </p>

    <template #title>
      Nuevo cliente
    </template>

    <div class="space-y-4">
      <!-- Nombre -->
      <div>
        <label class="text-sm font-medium text-gray-800 dark:text-gray-200">
          Nombre <span class="text-red-500">*</span>
        </label>
        <input
          v-model="form.name"
          placeholder="Ejemplo: Jose Manuel"
          class="mt-1 w-full rounded-lg px-3 py-2 text-sm
                 bg-white dark:bg-gray-900
                 border border-gray-300 dark:border-gray-800
                 text-gray-900 dark:text-gray-200
                 placeholder-gray-400 dark:placeholder-gray-500
                 focus:ring-2 focus:ring-blue-500 focus:outline-none"
        />
        <p v-if="form.errors.name" class="text-sm text-red-500 mt-1">
          {{ form.errors.name }}
        </p>
      </div>

      <!-- Teléfono -->
      <div>
        <label class="text-sm font-medium text-gray-800 dark:text-gray-200">
          Teléfono <span class="text-red-500">*</span>
        </label>
        <input
          v-model="form.phone"
          placeholder="Ejemplo: 123456789"
          class="mt-1 w-full rounded-lg px-3 py-2 text-sm
                 bg-white dark:bg-gray-900
                 border border-gray-300 dark:border-gray-800
                 text-gray-900 dark:text-gray-200
                 placeholder-gray-400 dark:placeholder-gray-500
                 focus:ring-2 focus:ring-blue-500 focus:outline-none"
        />
        <p v-if="form.errors.phone" class="text-sm text-red-500 mt-1">
          {{ form.errors.phone }}
        </p>
      </div>

      <!-- Notas -->
      <div>
        <label class="text-sm font-medium text-gray-800 dark:text-gray-200">
          Notas
        </label>
        <textarea
          v-model="form.notes"
          rows="3"
          placeholder="Ejemplo: Cliente antiguo, recurrente"
          class="mt-1 w-full rounded-lg px-3 py-2 text-sm
                 bg-white dark:bg-gray-900
                 border border-gray-300 dark:border-gray-800
                 text-gray-900 dark:text-gray-200
                 placeholder-gray-400 dark:placeholder-gray-500
                 focus:ring-2 focus:ring-blue-500 focus:outline-none"
        />
        <p v-if="form.errors.notes" class="text-sm text-red-500 mt-1">
          {{ form.errors.notes }}
        </p>
      </div>
    </div>

    <template #footer>
      <button
        type="button"
        class="px-4 py-2 rounded-lg text-sm
               bg-gray-200 text-gray-800 hover:bg-gray-300
               dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700"
        @click="close"
      >
        Cancelar
      </button>

      <button
        type="button"
        class="px-4 py-2 rounded-lg text-sm
               bg-blue-600 text-white hover:bg-blue-700
               disabled:opacity-50"
        :disabled="form.processing"
        @click="submit"
      >
        Guardar
      </button>
    </template>
  </ModalBase>
</template>
