<script setup>
import { ref, watch } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import CreateModalSale from '@/Components/Sales/CreateModalSale.vue';
import EditModalSale from '@/Components/Sales/EditModalSale.vue';
import DeleteModalSale from '@/Components/Sales/DeleteModalSale.vue';
import MenuActions from '@/Components/UI/MenuActions.vue';

const props = defineProps({
    sales: Object,
    filters: Object,
    barbers: Array,
    services: Array,
    clients: Array,
});

const search = ref(props.filters.search);

let timeout = null;
watch(search, (value) => {
    clearTimeout(timeout);
    timeout = setTimeout(() => {
        router.get(route('sales.index'), { search: value }, {
            preserveState: true,
            replace: true,
        });
    }, 300);
});

const isCreateModalOpen = ref(false);

const openMenuSaleId = ref(null);

const openMenuFor = (id) => {
  openMenuSaleId.value = id;
};

const closeAnyMenu = () => {
  openMenuSaleId.value = null;
};

const isEditModalOpen = ref(false);
const saleToEdit = ref(null);

const openEditModal = (sale) => {
    closeAnyMenu();
    saleToEdit.value = sale;
    isEditModalOpen.value = true;
};

const isDeleteModalOpen = ref(false);
const saleToDelete = ref(null);

const openDeleteModal = (sale) => {
    closeAnyMenu();
    saleToDelete.value = sale;
    isDeleteModalOpen.value = true;
};
</script>

<template>
  <AppLayout title="Ventas">
    <Head title="Ventas" />
    
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
                <span class="ml-1 text-sm font-semibold text-gray-800 md:ml-2 dark:text-white">Ventas</span>
              </div>
            </li>
          </ol>
        </nav>
      </div>
    </template>

    <div class="min-h-screen font-sans bg-gray-50 dark:bg-transparent text-gray-700 dark:text-gray-400">
      <div class="max-w-15xl mx-auto px-4 sm:px-6 lg:px-8 py-10">

        <!-- Header and Actions -->
        <div class="sm:flex sm:items-center sm:justify-between mb-12">
          <div>
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-white tracking-tight sm:text-3xl">
              Ventas
            </h1>
            <p class="mt-2 text-sm text-gray-600 dark:text-gray-500">
              Gestiona el registro de tus ventas y servicios directos.
            </p>
          </div>

          <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-4">
            <!-- Search -->
            <div class="relative w-full sm:min-w-[300px]">
              <span class="absolute inset-y-0 left-0 pl-3 flex items-center">
                <svg class="h-5 w-5 text-gray-400 dark:text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
              </span>
              <input
                v-model="search"
                type="text"
                placeholder="Buscar venta..."
                class="w-full pl-10 pr-4 py-2 mt-3 sm:mt-0 rounded-lg border text-sm
                       bg-white dark:bg-[#1c1c24]
                       border-gray-200 dark:border-transparent
                       text-gray-900 dark:text-gray-300
                       placeholder-gray-400 dark:placeholder-gray-600
                       focus:ring-1 focus:ring-blue-500 focus:outline-none"
              >
            </div>

            <!-- Add Button -->
            <button
              @click="isCreateModalOpen = true"
              class="flex items-center justify-center gap-2 px-5 py-1 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-all"
            >
              <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
              </svg>
              Nueva venta
              <CreateModalSale 
                  v-model="isCreateModalOpen" 
                  :barbers="barbers"
                  :services="services"
                  :clients="clients"
              />
            </button>
          </div>
        </div>

        <!-- Sales Table -->
        <div class="overflow-x-auto">
          <table class="w-full text-left border-separate border-spacing-y-2">
            <thead>
              <tr class="text-[11px] uppercase tracking-[0.1em] font-bold text-gray-500 dark:text-gray-600">
                <th class="px-6 py-3">Fecha</th>
                <th class="px-6 py-3">Barbero</th>
                <th class="px-6 py-3">Servicios</th>
                <th class="px-6 py-3">Cliente</th>
                <th class="px-6 py-3">Monto</th>
                <th class="px-6 py-3"></th> <!-- Acciones -->
              </tr>
            </thead>

            <tbody class="text-[14px]">
              <tr
                v-for="sale in sales.data"
                :key="sale.id"
                class="rounded-lg hover:bg-gray-100 dark:hover:bg-[#1c1c24] transition"
              >
                <td class="px-6 py-4 whitespace-nowrap text-gray-900 dark:text-gray-200">
                  {{ new Date(sale.sold_at).toLocaleString() }}
                </td>

                <td class="px-6 py-4 whitespace-nowrap font-medium text-gray-900 dark:text-gray-200">
                  {{ sale.barber?.name }}
                </td>

                <td class="px-6 py-4 text-gray-600 dark:text-gray-400">
                  <div class="flex flex-wrap gap-1">
                    <span v-for="item in sale.items" :key="item.id" class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300 whitespace-nowrap">
                      {{ item.service?.name }}
                    </span>
                  </div>
                </td>

                <td class="px-6 py-4 whitespace-nowrap text-gray-600 dark:text-gray-400">
                  {{ sale.client_id ? sale.client?.name : (sale.client_name || 'Desconocido') }}
                  <span v-if="!sale.client_id" class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-[10px] font-medium bg-gray-200 text-gray-800 dark:bg-gray-700 dark:text-gray-300">
                    Ocasional
                  </span>
                </td>

                <td class="px-6 py-4 whitespace-nowrap font-medium text-gray-900 dark:text-gray-200">
                  {{ sale.amount }} Lps
                  <span class="text-xs font-normal text-gray-500 dark:text-gray-500 ml-1">
                    ({{ sale.payment_method }})
                  </span>
                </td>

                <MenuActions
                  :is-open="openMenuSaleId === sale.id"
                  @toggle="openMenuFor(sale.id)"
                  @close="closeAnyMenu"
                  @edit="openEditModal(sale)"
                  @delete="openDeleteModal(sale)"
                />
              </tr>
              
              <EditModalSale 
                  v-if="saleToEdit"
                  v-model="isEditModalOpen" 
                  :sale="saleToEdit"
                  :barbers="barbers"
                  :services="services"
                  :clients="clients"
              />
              
              <DeleteModalSale 
                  v-if="saleToDelete"
                  v-model="isDeleteModalOpen" 
                  :sale="saleToDelete"
              />
                
              <tr v-if="sales.data.length === 0">
                <td colspan="6" class="px-6 py-10 text-center text-gray-500">
                  <div class="flex flex-col items-center justify-center">
                    <svg class="h-10 w-10 text-gray-400 dark:text-gray-600 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4M12 20V4" />
                    </svg>
                    No se encontraron ventas para mostrar. <br>
                    ¡Registra una nueva venta!
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div
          v-if="sales.total > 0"
          class="mt-10 flex flex-col sm:flex-row items-center justify-between border-t border-gray-200 dark:border-gray-800 pt-6 gap-6"
        >
          <div class="text-sm text-gray-600 dark:text-gray-500">
            Mostrando
            <span class="text-gray-800 dark:text-gray-300">{{ sales.from }}</span>
            a
            <span class="text-gray-800 dark:text-gray-300">{{ sales.to }}</span>
            de
            <span class="text-gray-800 dark:text-gray-300">{{ sales.total }}</span>
            resultados
          </div>

          <div class="flex flex-col sm:flex-row items-center gap-4 w-full sm:w-auto">
             <!-- Mobile Pagination Links -->
             <div class="flex flex-col items-center gap-4 w-full sm:hidden">
                <Link
                  v-if="sales.links[0].url"
                  :href="sales.links[0].url"
                  class="w-full text-center px-4 py-2 text-sm rounded-lg border border-gray-300 dark:border-gray-800 text-gray-700 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-[#1c1c24]"
                  v-html="sales.links[0].label"
                  preserve-state
                />
                <div v-else class="w-full text-center px-4 py-2 text-sm text-gray-400 rounded-lg border border-gray-200 dark:border-gray-800" v-html="sales.links[0].label" />

                <div class="flex flex-wrap justify-center gap-2">
                  <template v-for="(link, k) in sales.links.slice(1, -1)" :key="k">
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
                  v-if="sales.links[sales.links.length - 1].url"
                  :href="sales.links[sales.links.length - 1].url"
                  class="w-full text-center px-4 py-2 text-sm rounded-lg border border-gray-300 dark:border-gray-800 text-gray-700 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-[#1c1c24]"
                  v-html="sales.links[sales.links.length - 1].label"
                  preserve-state
                />
                <div v-else class="w-full text-center px-4 py-2 text-sm text-gray-400 rounded-lg border border-gray-200 dark:border-gray-800" v-html="sales.links[sales.links.length - 1].label" />
             </div>

             <!-- Desktop Pagination Links -->
             <div class="hidden sm:flex items-center gap-2">
                <template v-for="(link, k) in sales.links" :key="k">
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
          </div>
        </div>

      </div>
    </div>
  </AppLayout>
</template>
