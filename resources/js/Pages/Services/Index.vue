<script setup> 
import AppLayout from '@/Layouts/AppLayout.vue';
import CreateModalService from '@/Components/Services/CreateModalService.vue';
import { ref } from 'vue';
import { Link } from '@inertiajs/vue3';

import MenuActions from '@/Components/UI/MenuActions.vue';
import EditModalService from '@/Components/Services/EditModalService.vue';
import DeleteModalService from '@/Components/Services/DeleteModalService.vue';

const props = defineProps({
    services: Array,
});
// Create
const showCreateModal = ref(false)

const openMenuClientId = ref(null)

const openMenuFor = (id) => {
  openMenuClientId.value = id
}

const closeAnyMenu = () => {
  openMenuClientId.value = null
}

// Edit
const selectedService = ref(null)
const showEditModal = ref(false)

const openEditModal = (service) => {
  closeAnyMenu()
  selectedService.value = service
  showEditModal.value = true
}

// Delete
const showDeleteModal = ref(false)

const openDeleteModal = (service) => {
  closeAnyMenu()
  selectedService.value = service
  showDeleteModal.value = true
}


</script>

<template>
    <AppLayout title="Servicios">
        <!-- Breadcrumbs (rutas) -->
        <template #breadcrumbs>
        <div class="px-8 py-3 bg-white dark:bg-gray-900 border-b border-gray-200 dark:border-gray-800 hidden sm:block">
            <nav class="flex" aria-label="Breadcrumb">
            <ol class="inline-flex items-center space-x-1 md:space-x-3">
                <li class="inline-flex items-center">
                <Link :href="route('dashboard')" class="inline-flex items-center text-sm font-medium text-gray-500 hover:text-blue-600 dark:text-gray-400 dark:hover:text-blue-500 transition-colors">
                    <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                    <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"></path>
                    </svg>
                    Dashboard
                </Link>
                </li>
                <li aria-current="page">
                <div class="flex items-center">
                    <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                    </svg>
                    <span class="ml-1 text-sm font-semibold text-gray-800 md:ml-2 dark:text-white">Servicios</span>
                </div>
                </li>
            </ol>
            </nav>
        </div>
        </template>
        
        
    <div class="min-h-screen font-sans bg-gray-50 dark:bg-transparent text-gray-700 dark:text-gray-400">
      <div class="max-w-15xl mx-auto px-4 sm:px-6 lg:px-8 py-10">

        <!-- Header -->
        <div class="sm:flex sm:items-center sm:justify-between mb-12">
          <div>
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-white tracking-tight sm:text-3xl">
              Servicios
            </h1>
            <p class="mt-2 text-sm text-gray-600 dark:text-gray-500">
              Gestiona la información de tus servicios.
            </p>
          </div>

          <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-4">
            <!-- Add -->
            <button
               @click="showCreateModal = true"
              class="flex items-center justify-center gap-2 px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-all"
            >
              <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M12 4v16m8-8H4" />
              </svg>
                Añadir servicio
            </button>
            <CreateModalService v-model="showCreateModal" />
          </div>
        </div>

        <!-- Table -->
        <div class="overflow-x-auto">
          <table class="w-full text-left border-separate border-spacing-y-2">
            <thead>
              <tr class="text-[11px] uppercase tracking-[0.1em] font-bold text-gray-500 dark:text-gray-600">
                <th class="px-6 py-3">Nombre</th>
                <th class="px-6 py-3">Precio</th>
                <th class="px-6 py-3">Duración</th>
                <th class="px-6 py-3">Estado</th>
                <th class="px-6 py-3">Descripción</th>
                <th class="px-6 py-3">Acciones</th>
              </tr>
            </thead>

            <tbody class="text-[14px]">
              <tr
                v-for="service in services"
                :key="service.id"
                class="rounded-lg hover:bg-gray-100 dark:hover:bg-[#1c1c24] transition"
              >
                <td class="px-6 py-4 whitespace-nowrap font-medium text-gray-900 dark:text-gray-200">
                  {{ service.name }}
                </td>

                <td class="px-6 py-4 whitespace-nowrap text-gray-600 dark:text-gray-400">
                  {{ service.price }} LPS
                </td>

                <td class="px-6 py-4 whitespace-nowrap text-gray-700 dark:text-gray-400">
                  {{ service.duration_minutes }} min
                  
                </td>

                <td class="px-6 py-4 whitespace-nowrap text-gray-700 dark:text-gray-400">
                  <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                    :class="service.is_active
                      ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400'
                      : 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400'">
                    {{ service.is_active ? 'Activo' : 'Inactivo' }}
                  </span>
                </td>

                <td class="px-6 py-4 whitespace-nowrap text-gray-700 dark:text-gray-400">
                  {{ service.description }}
                </td>

                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                  <MenuActions
                  :is-open="openMenuClientId === service.id"
                  @toggle="openMenuFor(service.id)"
                  @close="closeAnyMenu"
                  @edit="openEditModal(service)"
                  @delete="openDeleteModal(service)"
                  />
                </td>
            
            </tr>

            <EditModalService v-if="selectedService" :service="selectedService" v-model="showEditModal" @updated:modelValue="v => (showEditModal = v)" />
            <DeleteModalService v-if="selectedService" :service="selectedService" v-model="showDeleteModal" @deleted:modelValue="v => (showDeleteModal = v)" />

            <tr v-if="services.length === 0">
              <td colspan="5" class="px-6 py-10 text-center text-gray-500">
                No se encontraron servicios.
              </td>
            </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>


    </AppLayout>
</template>