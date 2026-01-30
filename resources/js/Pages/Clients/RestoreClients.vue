<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import { ref, watch } from 'vue'
import { router, Link } from '@inertiajs/vue3'
import ConfirmModal from '@/Components/UI/ConfirmModal.vue'


const showConfirm = ref(false)
const actionType = ref(null)
const selectedClient = ref(null)
const props = defineProps({
  clients: Object,
  filters: Object,
})
const search = ref(props.filters?.search ?? '')


let timeout = null
watch(search, (value) => {
  clearTimeout(timeout)
  timeout = setTimeout(() => {
    router.get(route('clients.trash'), { search: value }, {
      preserveState: true,
      replace: true,
    })
  }, 300)
})

const openRestoreModal = (client) => {
  selectedClient.value = client
  actionType.value = 'restore'
  showConfirm.value = true
}

const openDeleteModal = (client) => {
  selectedClient.value = client
  actionType.value = 'delete'
  showConfirm.value = true
}

const confirmAction = () => {
  if (!selectedClient.value) return

  if (actionType.value === 'restore') {
    router.put(route('clients.restore', selectedClient.value.id), {
      preserveScroll: true,
    })
  }

  if (actionType.value === 'delete') {
    router.delete(route('clients.forceDelete', selectedClient.value.id), {
      preserveScroll: true,
    })
  }

  showConfirm.value = false
  selectedClient.value = null
}
</script>

<template>
  <AppLayout title="Papelera">
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
            <li class="inline-flex items-center">
              <Link :href="route('clients.index')" class="inline-flex items-center text-sm font-medium text-gray-500 hover:text-blue-600 dark:text-gray-400 dark:hover:text-blue-500 transition-colors">
                <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                  <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                </svg>
                Clientes
              </Link>
            </li>
            <li aria-current="page">
              <div class="flex items-center">
                <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                  <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                </svg>
                <span class="ml-1 text-sm font-semibold text-gray-800 md:ml-2 dark:text-white">Papelera de Clientes</span>
              </div>
            </li>
          </ol>
        </nav>
      </div>
    </template>

    
    <div class="min-h-screen font-sans bg-gray-50 dark:bg-transparent text-gray-700 dark:text-gray-400">
      <div class="max-w-15xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        <!-- Header -->
        <div class="sm:flex sm:items-center sm:justify-between mb-12 gap-6">
          <div>
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-white tracking-tight sm:text-3xl">
              Papelera de clientes
            </h1>
            <p class="mt-2 text-sm text-gray-600 dark:text-gray-500">
              Estas viendo los clientes eliminados. Puedes restaurarlos o eliminarlos definitivamente.
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
                placeholder="Buscar en papelera..."
                class="w-full pl-10 pr-4 py-2 rounded-lg border text-sm
                       bg-white dark:bg-[#1c1c24]
                       border-gray-200 dark:border-transparent
                       text-gray-900 dark:text-gray-300
                       placeholder-gray-400 dark:placeholder-gray-600
                       focus:ring-1 focus:ring-blue-500 focus:outline-none"
              >
            </div>

            <!-- Back -->
            <Link
              :href="route('clients.index')"
              class="flex items-center justify-center gap-2 px-5 py-2 rounded-lg text-sm font-medium
                     bg-gray-200 text-gray-800 hover:bg-gray-300
                     dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700 transition"
            >
              Volver a clientes
            </Link>
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
                <th class="px-6 py-3">Eliminado</th>
                <th class="px-6 py-3 text-right">Acciones</th>
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
                  {{ client.notes ?? '—' }}
                </td>

                <td class="px-6 py-4 whitespace-nowrap text-gray-600 dark:text-gray-400">
                  {{ client.deleted_at ?? '—' }}
                </td>

                <td class="px-6 py-4 whitespace-nowrap text-right">
                  <div class="flex justify-end gap-2">
                    <button
                    class="px-3 py-2 rounded-lg text-sm bg-emerald-600 text-white hover:bg-emerald-700"
                    @click="openRestoreModal(client)"
                  >
                    Restaurar
                  </button>

                  <button
                    class="px-3 py-2 rounded-lg text-sm bg-red-600 text-white hover:bg-red-700"
                    @click="openDeleteModal(client)"
                  >
                    Eliminar definitivo
                  </button>
                  </div>
                </td>
              </tr>
              <!-- Modal de confirmación de las acciones -->
                <ConfirmModal
                  v-model="showConfirm"
                  :title="actionType === 'delete'
                    ? 'Eliminar cliente definitivamente'
                    : 'Restaurar cliente'"
                  :message="actionType === 'delete'
                    ? `¿Seguro que deseas eliminar definitivamente a «${selectedClient?.name}»? Esta acción no se puede deshacer.`
                    : `¿Deseas restaurar a «${selectedClient?.name}»?`"
                  :confirmText="actionType === 'delete' ? 'Eliminar' : 'Restaurar'"
                  :confirmColor="actionType === 'delete'
                    ? 'bg-red-600 hover:bg-red-700'
                    : 'bg-emerald-600 hover:bg-emerald-700'"
                  @confirm="confirmAction"
                />

              <tr v-if="clients.data.length === 0">
                <td colspan="5" class="px-6 py-10 text-center text-gray-500">
                  La papelera está vacía.
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
        </div>

      </div>
    </div>
  </AppLayout>
</template>
