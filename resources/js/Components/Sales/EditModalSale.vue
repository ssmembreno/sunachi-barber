<script setup>
import { useForm } from '@inertiajs/vue3';
import { watch } from 'vue';
import ModalBase from '@/Components/UI/ModalBase.vue';

const props = defineProps({
  modelValue: { type: Boolean, default: false },
  sale: Object,
  barbers: Array,
  services: Array,
  clients: Array,
});

const emit = defineEmits(['update:modelValue']);

const form = useForm({
  barber_id: '',
  services: [],
  client_id: '',
  client_name: '',
  amount: 0,
  payment_method: 'cash',
  notes: '',
});

// Update form when sale prop changes
watch(() => props.sale, (newSale) => {
  if (newSale && props.modelValue) {
    form.barber_id = newSale.barber_id;
    form.services = newSale.items && newSale.items.length > 0 
      ? newSale.items.map(i => ({ service_id: i.service_id, price: i.price }))
      : [{ service_id: '', price: 0 }];
    form.client_id = newSale.client_id || '';
    form.client_name = newSale.client_name || '';
    form.amount = newSale.amount;
    form.payment_method = newSale.payment_method;
    form.notes = newSale.notes || '';
  }
}, { immediate: true });

watch(
  () => props.modelValue,
  (open) => {
    if (!open) return;
    form.clearErrors();
    // Re-initialize form data just in case the prop change didn't trigger cleanly
    if (props.sale) {
        form.barber_id = props.sale.barber_id;
        form.services = props.sale.items && props.sale.items.length > 0 
          ? props.sale.items.map(i => ({ service_id: i.service_id, price: i.price }))
          : [{ service_id: '', price: 0 }];
        form.client_id = props.sale.client_id || '';
        form.client_name = props.sale.client_name || '';
        form.amount = props.sale.amount;
        form.payment_method = props.sale.payment_method;
        form.notes = props.sale.notes || '';
    }
  }
);

const close = () => {
  form.clearErrors();
  emit('update:modelValue', false);
};

const updateSale = () => {
  if (!props.sale) return;
  
  form.put(route('sales.update', props.sale.id), {
    preserveScroll: true,
    onSuccess: () => close(),
  });
};

const addService = () => {
  form.services.push({ service_id: '', price: 0 });
};

const removeService = (index) => {
  form.services.splice(index, 1);
  recalculateTotal();
};

const updateServicePrice = (index, serviceId) => {
  const service = props.services.find(s => s.id === serviceId);
  if (service) {
    form.services[index].price = parseFloat(service.price);
  }
  recalculateTotal();
};

const recalculateTotal = () => {
  form.amount = form.services.reduce((total, item) => total + (parseFloat(item.price) || 0), 0).toFixed(2);
};
</script>

<template>
  <ModalBase
    :modelValue="modelValue"
    @update:modelValue="v => emit('update:modelValue', v)"
    maxWidth="max-w-2xl"
  >
    <template #title>
      Editar Venta #{{ sale?.id }}
    </template>

    <div class="space-y-4 text-sm font-medium text-gray-900 dark:text-gray-200">
      <!-- Barber -->
      <div>
        <label class="block mb-1">
          Barbero <span class="text-red-500">*</span>
        </label>
        <select
          v-model="form.barber_id"
          required
          class="w-full rounded-lg px-3 py-2 text-sm bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-800 text-gray-900 dark:text-gray-200 focus:ring-2 focus:ring-blue-500 focus:outline-none"
        >
          <option value="" disabled>Selecciona un barbero</option>
          <option v-for="barber in barbers" :key="barber.id" :value="barber.id">{{ barber.name }}</option>
        </select>
        <p v-if="form.errors.barber_id" class="text-sm text-red-500 mt-1">{{ form.errors.barber_id }}</p>
      </div>

      <!-- Services -->
      <div>
        <div class="flex justify-between items-center mb-1">
          <label class="block">Servicios <span class="text-red-500">*</span></label>
          <button type="button" @click="addService" class="text-xs flex items-center bg-blue-100 text-blue-700 dark:bg-gray-800 dark:text-blue-400 px-2 py-1 rounded hover:bg-blue-200 dark:hover:bg-gray-700 transition">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            Añadir Servicio
          </button>
        </div>
        
        <div class="space-y-3">
          <div v-for="(item, index) in form.services" :key="index" class="flex gap-2 items-start bg-gray-50 dark:bg-[#1c1c24] p-3 rounded-lg border border-gray-200 dark:border-gray-800">
            <div class="flex-1">
              <select
                v-model="item.service_id"
                @change="updateServicePrice(index, item.service_id)"
                required
                class="w-full rounded-lg px-3 py-2 text-sm bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-800 text-gray-900 dark:text-gray-200 focus:ring-2 focus:ring-blue-500 focus:outline-none"
              >
                <option value="" disabled>Selecciona un servicio</option>
                <option v-for="service in services" :key="service.id" :value="service.id">{{ service.name }} - ${{ service.price }}</option>
              </select>
              <p v-if="form.errors[`services.${index}.service_id`]" class="text-red-500 text-xs mt-1">{{ form.errors[`services.${index}.service_id`] }}</p>
            </div>
            <div class="w-24">
              <input
                type="number"
                step="0.01"
                v-model="item.price"
                @input="recalculateTotal"
                required
                placeholder="Precio"
                class="w-full rounded-lg px-3 py-2 text-sm bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-800 text-gray-900 dark:text-gray-200 focus:ring-2 focus:ring-blue-500 focus:outline-none"
              >
            </div>
            <button type="button" @click="removeService(index)" v-if="form.services.length > 1" class="text-gray-500 hover:text-red-500 dark:text-gray-400 dark:hover:text-red-400 p-2 transition-colors">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
            </button>
          </div>
        </div>
      </div>

      <!-- Client -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <label class="block mb-1">Cliente (Registrado)</label>
          <select
            v-model="form.client_id"
            class="w-full rounded-lg px-3 py-2 text-sm bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-800 text-gray-900 dark:text-gray-200 focus:ring-2 focus:ring-blue-500 focus:outline-none"
          >
            <option value="">Cliente Ocasional / Sin registro</option>
            <option v-for="client in clients" :key="client.id" :value="client.id">{{ client.name }} ({{ client.phone }})</option>
          </select>
          <p v-if="form.errors.client_id" class="text-sm text-red-500 mt-1">{{ form.errors.client_id }}</p>
        </div>

        <div v-if="!form.client_id">
          <label class="block mb-1">Nombre Cliente (Ocasional)</label>
          <input
            type="text"
            v-model="form.client_name"
            placeholder="Ej: Juan Pérez"
            class="w-full rounded-lg px-3 py-2 text-sm bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-800 text-gray-900 dark:text-gray-200 placeholder-gray-400 dark:placeholder-gray-500 focus:ring-2 focus:ring-blue-500 focus:outline-none"
          >
          <p v-if="form.errors.client_name" class="text-sm text-red-500 mt-1">{{ form.errors.client_name }}</p>
        </div>
      </div>

      <!-- Price & Payment Method -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4 bg-gray-50 dark:bg-[#1c1c24] p-4 rounded-lg border border-gray-200 dark:border-gray-800">
        <div>
          <label class="block mb-1 font-semibold">Monto Total ($)</label>
          <input
            type="number"
            step="0.01"
            v-model="form.amount"
            required
            class="w-full rounded-lg px-3 py-2 text-lg font-bold text-gray-900 dark:text-green-400 bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-800 focus:ring-2 focus:ring-blue-500 focus:outline-none"
          >
          <p v-if="form.errors.amount" class="text-sm text-red-500 mt-1">{{ form.errors.amount }}</p>
        </div>

        <div>
          <label class="block mb-1">Método de Pago</label>
          <select
            v-model="form.payment_method"
            required
            class="w-full rounded-lg px-3 py-2 text-sm bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-800 text-gray-900 dark:text-gray-200 focus:ring-2 focus:ring-blue-500 focus:outline-none mt-1"
          >
            <option value="cash">Efectivo</option>
            <option value="card">Tarjeta</option>
            <option value="transfer">Transferencia</option>
            <option value="other">Otro</option>
          </select>
          <p v-if="form.errors.payment_method" class="text-sm text-red-500 mt-1">{{ form.errors.payment_method }}</p>
        </div>
      </div>

      <!-- Notes -->
      <div>
        <label class="block mb-1">Notas</label>
        <textarea
          v-model="form.notes"
          rows="2"
          placeholder="Opcional: detalles adicionales de la venta"
          class="w-full rounded-lg px-3 py-2 text-sm bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-800 text-gray-900 dark:text-gray-200 placeholder-gray-400 dark:placeholder-gray-500 focus:ring-2 focus:ring-blue-500 focus:outline-none"
        ></textarea>
        <p v-if="form.errors.notes" class="text-sm text-red-500 mt-1">{{ form.errors.notes }}</p>
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
        class="px-4 py-2 rounded-lg text-sm bg-blue-600 text-white hover:bg-blue-700 disabled:opacity-50"
        :disabled="form.processing"
        @click="updateSale"
      >
        Guardar
      </button>
    </template>
  </ModalBase>
</template>
