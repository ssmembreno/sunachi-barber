<script setup>
import { watch } from 'vue'
import ModalBase from '@/Components/UI/ModalBase.vue'
import { useForm } from '@inertiajs/vue3'

const props = defineProps({
  modelValue: { type: Boolean, default: false },
  service: Object,
})

const emit = defineEmits(['update:modelValue'])

const form = useForm({
  name: props.service?.name || '',
  description: props.service?.description || '',
  price: props.service?.price || '',
  duration_minutes: props.service?.duration_minutes || '',
  is_active: props.service?.is_active !== undefined ? Boolean(props.service.is_active) : true,
})

watch(
  () => props.service,
  (s) => {
    if (!s) return
    form.name = s.name ?? ''
    form.description = s.description ?? ''
    form.price = s.price ?? ''
    form.duration_minutes = s.duration_minutes ?? ''
    form.is_active = s.is_active !== undefined ? Boolean(s.is_active) : true
  }
)

watch(
  () => props.modelValue,
  (open) => {
    if (!open) return
    form.clearErrors()
  }
)

const close = () => emit('update:modelValue', false)

const submit = () => {
    // Assuming backend resource route services.update maps to PUT /services/{service}
    // and expects {service} id.
  form.put(route('services.update', props.service.id), {
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

    <p class="text-xs text-gray-600 dark:text-gray-400 mb-4">
      Los campos marcados con <span class="text-red-500">*</span> son obligatorios
    </p>
    
    <template #title>
      Editar servicio
    </template>

    <div class="space-y-4">
      <!-- Nombre -->
      <div>
        <label class="text-sm font-medium text-gray-900 dark:text-gray-200">
          Nombre <span class="text-red-500">*</span>
        </label>
        <input
          v-model="form.name"
          placeholder="Ejemplo: Corte de pelo"
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

      <!-- Descripción -->
        <div>
            <label class="text-sm font-medium text-gray-900 dark:text-gray-200">
            Descripción <span class="text-red-500">*</span>
            </label>
            <input
            v-model="form.description"
            placeholder="Ejemplo: Corte de cabello profesional"
            class="mt-1 w-full rounded-lg px-3 py-2 text-sm
                    bg-white dark:bg-gray-900
                    border border-gray-300 dark:border-gray-800
                    text-gray-900 dark:text-gray-200
                    placeholder-gray-400 dark:placeholder-gray-500
                    focus:ring-2 focus:ring-blue-500 focus:outline-none"
            />
            <p v-if="form.errors.description" class="text-sm text-red-500 mt-1">
            {{ form.errors.description }}
            </p>
        </div>

      <!-- Precio -->
      <div>
        <label class="text-sm font-medium text-gray-900 dark:text-gray-200">
          Precio (LPS) <span class="text-red-500">*</span>
        </label>
        <input
          v-model="form.price"
          type="number"
          step="0.01"
          placeholder="Ejemplo: 150"
          class="mt-1 w-full rounded-lg px-3 py-2 text-sm
                 bg-white dark:bg-gray-900
                 border border-gray-300 dark:border-gray-800
                 text-gray-900 dark:text-gray-200
                 placeholder-gray-400 dark:placeholder-gray-500
                 focus:ring-2 focus:ring-blue-500 focus:outline-none"
        />
        <p v-if="form.errors.price" class="text-sm text-red-500 mt-1">
          {{ form.errors.price }}
        </p>
      </div>

      <!-- Duración -->
      <div>
        <label class="text-sm font-medium text-gray-900 dark:text-gray-200">
          Duración (minutos) <span class="text-red-500">*</span>
        </label>
        <input
          v-model="form.duration_minutes"
          type="number"
          placeholder="Ejemplo: 30"
          class="mt-1 w-full rounded-lg px-3 py-2 text-sm
                 bg-white dark:bg-gray-900
                 border border-gray-300 dark:border-gray-800
                 text-gray-900 dark:text-gray-200
                 placeholder-gray-400 dark:placeholder-gray-500
                 focus:ring-2 focus:ring-blue-500 focus:outline-none"
        />
        <p v-if="form.errors.duration_minutes" class="text-sm text-red-500 mt-1">
          {{ form.errors.duration_minutes }}
        </p>
      </div>

      <!-- Activo -->
      <div class="flex items-center">
            <input
                id="is_active"
                v-model="form.is_active"
                type="checkbox"
                class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded dark:bg-gray-900 dark:border-gray-800"
            />
            <label for="is_active" class="ml-2 block text-sm text-gray-900 dark:text-gray-200">
                Servicio activo
            </label>
        </div>
        <p v-if="form.errors.is_active" class="text-sm text-red-500 mt-1">
            {{ form.errors.is_active }}
        </p>

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
