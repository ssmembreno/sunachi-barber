<script setup>
import { useForm } from '@inertiajs/vue3';
import ModalBase from '@/Components/UI/ModalBase.vue';

const props = defineProps({
  modelValue: { type: Boolean, default: false },
  sale: Object,
});

const emit = defineEmits(['update:modelValue']);

const form = useForm({});

const close = () => {
  emit('update:modelValue', false);
};

const deleteSale = () => {
  if (!props.sale) return;
  
  form.delete(route('sales.destroy', props.sale.id), {
    preserveScroll: true,
    onSuccess: () => close(),
  });
};
</script>

<template>
  <ModalBase
    :modelValue="modelValue"
    @update:modelValue="v => emit('update:modelValue', v)"
    maxWidth="max-w-md"
  >
    <div class="px-6 py-4">
      <div class="text-lg font-medium text-gray-900 dark:text-gray-100 flex items-center mb-4">
        <svg class="h-6 w-6 text-red-600 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
        </svg>
        Confirmar Eliminación
      </div>

      <div class="mt-4 text-sm text-gray-600 dark:text-gray-400">
        ¿Estás seguro que deseas eliminar esta venta registrada por un monto de 
        <span class="font-bold text-gray-900 dark:text-gray-100">${{ sale?.amount }}</span>?
        Esta acción no se puede deshacer.
      </div>
    </div>

    <template #footer>
      <button
        type="button"
        class="px-4 py-2 rounded-lg text-sm bg-gray-200 text-gray-800 hover:bg-gray-300 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700"
        @click="close"
      >
        Cancelar
      </button>

      <button
        type="button"
        class="px-4 py-2 rounded-lg text-sm bg-red-600 text-white hover:bg-red-700 disabled:opacity-50"
        :disabled="form.processing"
        @click="deleteSale"
      >
        Eliminar
      </button>
    </template>
  </ModalBase>
</template>
