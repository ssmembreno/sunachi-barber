<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import { ref, watch } from 'vue'
import { router, Link } from '@inertiajs/vue3'
import ModalCreateClient from '@/Components/Clients/ModalCreateClient.vue'
import ModalEditClient from '@/Components/Clients/ModalEditClient.vue'
import MenuActions from '@/Components/UI/MenuActions.vue'
import DeleteModalCliente from '@/Components/Clients/DeleteModalCliente.vue'

const props = defineProps({
    clients: Object,
    filters: Object,
})

const search = ref(props.filters.search)

let timeout = null
watch(search, (value) => {
    clearTimeout(timeout)
    timeout = setTimeout(() => {
        router.get(route('clients.index'), { search: value }, {
            preserveState: true,
            replace: true,
        })
    }, 300)
})

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
const selectedClient = ref(null)
const showEditModal = ref(false)

const openEditModal = (client) => {
  closeAnyMenu()
  selectedClient.value = client
  showEditModal.value = true
}

// Delete
const showDeleteModal = ref(false)

const openDeleteModal = (client) => {
  closeAnyMenu()
  selectedClient.value = client
  showDeleteModal.value = true
}

</script>

<template>
  <AppLayout title="Clientes">
    
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
                <span class="ml-1 text-sm font-semibold text-gray-800 md:ml-2 dark:text-white">Clientes</span>
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
              Clientes
            </h1>
            <p class="mt-2 text-sm text-gray-600 dark:text-gray-500">
              Gestiona la información de tus clientes registrados.
            </p>
          </div>

          <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-4">
            <!-- Search -->
            <div class="relative w-full sm:min-w-[300px]">
              <span class="absolute inset-y-0 left-0 pl-3 flex items-center">
                <svg class="h-5 w-5 text-gray-400 dark:text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
              </span>
              <input
                v-model="search"
                type="text"
                placeholder="Buscar cliente..."
                class="w-full pl-10 pr-4 py-2 mt-3 sm:mt-0 rounded-lg border text-sm
                       bg-white dark:bg-[#1c1c24]
                       border-gray-200 dark:border-transparent
                       text-gray-900 dark:text-gray-300
                       placeholder-gray-400 dark:placeholder-gray-600
                       focus:ring-1 focus:ring-blue-500 focus:outline-none"
              >
            </div>

            <!-- Add -->
            <button
              @click="showCreateModal = true"
              class="flex items-center justify-center gap-2 px-5 py-1 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-all"
            >
              <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M12 4v16m8-8H4" />
              </svg>
              Añadir cliente
              <ModalCreateClient v-model="showCreateModal" @created="showCreateModal = false" />
            </button>
          </div>
        </div>

        <!-- Table -->
        <div class="overflow-x-auto">
          <table class="w-full text-left border-separate border-spacing-y-2">
            <thead>
              <tr class="text-[11px] uppercase tracking-[0.1em] font-bold text-gray-500 dark:text-gray-600">
                <th class="px-6 py-3">Nombre</th>
                <th class="px-6 py-3">Teléfono</th>
                <th class="px-6 py-3">Notas</th>
              </tr>
            </thead>

            <tbody class="text-[14px]">
              <tr
                v-for="client in clients.data"
                :key="client.id"
                class="rounded-lg hover:bg-gray-100 dark:hover:bg-[#1c1c24] transition"
              >
                <td class="px-6 py-4 whitespace-nowrap font-medium text-gray-900 dark:text-gray-200">
                  {{ client.name }}
                </td>

                <td class="px-6 py-4 whitespace-nowrap text-gray-600 dark:text-gray-400">
                  {{ client.phone ?? 'N/A' }}
                </td>

                <td class="px-6 py-4 whitespace-nowrap text-gray-700 dark:text-gray-400">
                  {{ client.notes }}
                </td>

                <MenuActions
                  :is-open="openMenuClientId === client.id"
                  @toggle="openMenuFor(client.id)"
                  @close="closeAnyMenu"
                  @edit="openEditModal(client)"
                  @delete="openDeleteModal(client)"
                />
                
              </tr>
              <ModalEditClient v-if="selectedClient" :client="selectedClient" v-model="showEditModal" @updated:modelValue="v => (showEditModal = v)" />
              <DeleteModalCliente v-if="selectedClient" :client="selectedClient" v-model="showDeleteModal" @deleted:modelValue="v => (showDeleteModal = v)" />
                
              <tr v-if="clients.data.length === 0">
                <td colspan="5" class="px-6 py-10 text-center text-gray-500">
                  No se encontraron clientes.
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div
          v-if="clients.total > 0"
          class="mt-10 flex flex-col sm:flex-row items-center justify-between border-t border-gray-200 dark:border-gray-800 pt-6 gap-6"
        >
          <div class="text-sm text-gray-600 dark:text-gray-500">
            Mostrando
            <span class="text-gray-800 dark:text-gray-300">{{ clients.from }}</span>
            a
            <span class="text-gray-800 dark:text-gray-300">{{ clients.to }}</span>
            de
            <span class="text-gray-800 dark:text-gray-300">{{ clients.total }}</span>
            resultados
          </div>

          <div class="flex flex-col sm:flex-row items-center gap-4 w-full sm:w-auto">
             <!-- Mobile Pagination Links -->
             <div class="flex flex-col items-center gap-4 w-full sm:hidden">
                <Link
                  v-if="clients.links[0].url"
                  :href="clients.links[0].url"
                  class="w-full text-center px-4 py-2 text-sm rounded-lg border border-gray-300 dark:border-gray-800 text-gray-700 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-[#1c1c24]"
                  v-html="clients.links[0].label"
                  preserve-state
                />
                <div v-else class="w-full text-center px-4 py-2 text-sm text-gray-400 rounded-lg border border-gray-200 dark:border-gray-800" v-html="clients.links[0].label" />

                <div class="flex flex-wrap justify-center gap-2">
                  <template v-for="(link, k) in clients.links.slice(1, -1)" :key="k">
                    <Link
                      v-if="link.url"
                      class="px-4 py-2 text-sm rounded-lg border transition-all border-gray-300 dark:border-gray-800 text-gray-700 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-[#1c1c24]"
                      :class="{ 'bg-blue-600 text-white border-blue-600': link.active }"
                      :href="link.url"
                      v-html="link.label"
                      preserve-state
                    />
                    <div v-else class="px-4 py-2 text-sm text-gray-400 rounded-lg border border-transparent" v-html="link.label" />
                  </template>
                </div>

                <Link
                  v-if="clients.links[clients.links.length - 1].url"
                  :href="clients.links[clients.links.length - 1].url"
                  class="w-full text-center px-4 py-2 text-sm rounded-lg border border-gray-300 dark:border-gray-800 text-gray-700 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-[#1c1c24]"
                  v-html="clients.links[clients.links.length - 1].label"
                  preserve-state
                />
                <div v-else class="w-full text-center px-4 py-2 text-sm text-gray-400 rounded-lg border border-gray-200 dark:border-gray-800" v-html="clients.links[clients.links.length - 1].label" />
             </div>

             <!-- Desktop Pagination Links -->
             <div class="hidden sm:flex items-center gap-2">
                <template v-for="(link, k) in clients.links" :key="k">
                  <div
                    v-if="link.url === null"
                    class="px-4 py-2 text-sm text-gray-400 rounded-lg"
                    v-html="link.label"
                  />
                  <Link
                    v-else
                    class="px-4 py-2 text-sm rounded-lg border transition-all
                           border-gray-300 dark:border-gray-800
                           text-gray-700 dark:text-gray-400
                           hover:bg-gray-100 dark:hover:bg-[#1c1c24]"
                    :class="{ 'bg-blue-600 text-white border-blue-600': link.active }"
                    :href="link.url"
                    v-html="link.label"
                    preserve-state
                  />
                </template>
              </div>

              <!-- Trash Trash Link -->
              <Link :href="route('clients.trash')" class="shrink-0">
                <img
                  src="img/contenedor-de-basura.png"
                  alt="Papelera"
                  class="w-12 h-12 cursor-pointer transition-transform hover:scale-110 sm:w-12 sm:h-12"
                >
              </Link>
          </div>
        </div>

      </div>
    </div>
  </AppLayout>
</template>
