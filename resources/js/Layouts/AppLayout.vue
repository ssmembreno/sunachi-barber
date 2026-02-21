<script setup>
import { ref, onMounted, onBeforeUnmount } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'
import { useTheme } from '@/composables/useTheme'

const { isDark, toggleTheme } = useTheme()

/* Sidebar */
const sidebarOpen = ref(false)

/* User menu */
const userMenuOpen = ref(false)
const userMenuRef = ref(null)

/* Auth user (Breeze lo expone aquí) */
const page = usePage()
const user = page.props.auth?.user

const toggleUserMenu = (e) => {
  e.stopPropagation()
  userMenuOpen.value = !userMenuOpen.value
}

const closeUserMenu = () => {
  userMenuOpen.value = false
}

/* Cerrar menú al hacer click fuera */
const onDocumentClick = (e) => {
  if (!userMenuRef.value) return
  if (!userMenuRef.value.contains(e.target)) {
    closeUserMenu()
  }
}

onMounted(() => {
  document.addEventListener('click', onDocumentClick)
})

onBeforeUnmount(() => {
  document.removeEventListener('click', onDocumentClick)
})
</script>

<template>
<div class="font-sans antialiased min-h-screen transition-colors duration-200 bg-gray-50 text-gray-800 dark:bg-gray-900 dark:text-gray-200">
    <div class="flex h-screen overflow-hidden">

      <!-- Overlay móvil -->
      <div
        v-show="sidebarOpen"
        class="fixed inset-0 z-20 bg-black/40 lg:hidden"
        @click="sidebarOpen = false"
      />

      <!-- Sidebar -->
      <aside
        :class="sidebarOpen ? 'translate-x-0 ease-out' : '-translate-x-full ease-in'"
        class="fixed inset-y-0 left-0 z-30 w-64 transition duration-300 transform
               bg-white dark:bg-gray-900
               lg:translate-x-0 lg:static lg:inset-0
               border-r border-gray-200 dark:border-gray-800
               flex flex-col"
      >
        <div class="flex items-center justify-center h-20 border-b border-gray-200 dark:border-gray-800">
          <h1 class="text-2xl font-bold text-blue-600 dark:text-blue-500">
            Sü<span class="text-gray-900 dark:text-white">nachi</span>
            <span class="text-gray-900 dark:text-white"> Barber</span>
          </h1>
        </div>

        <nav class="flex-1 px-4 py-6 space-y-8 overflow-y-auto">
          <!-- Dashboard -->
          <div>
            <Link
              :href="route('dashboard')"
              class="flex items-center px-4 py-3 rounded-lg transition"
              :class="route().current('dashboard')
                ? 'text-white bg-blue-600 shadow-md shadow-blue-500/30'
                : 'text-gray-600 hover:text-gray-900 hover:bg-gray-100 dark:text-gray-400 dark:hover:text-white dark:hover:bg-gray-800'"
            >
              <svg class="h-6 w-6 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M4 6h16M4 12h16M4 18H7" />
              </svg>
              Dashboard
            </Link>
          </div>

          <!-- Clients -->
          <div>
            <h3 class="px-4 text-xs font-semibold uppercase tracking-wider mb-3 text-gray-500 dark:text-gray-500">
              Clients
            </h3>

            <Link
              :href="route('clients.index')"
              class="flex items-center px-4 py-2 rounded-lg transition"
              :class="route().current('clients.*')
                ? 'text-gray-900 bg-gray-100 dark:text-white dark:bg-gray-800'
                : 'text-gray-600 hover:text-gray-900 hover:bg-gray-100 dark:text-gray-400 dark:hover:text-white dark:hover:bg-gray-800'"
            >
              <svg class="h-5 w-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2
                         c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857
                         M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0
                         019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0
                         11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
              </svg>
              Clients
            </Link>
            
          </div>
          <!-- Calendario de reservas -->
           <div>
            <h3 class="px-4 text-xs font-semibold uppercase tracking-wider mb-3 text-gray-500 dark:text-gray-500">
              Reservas
            </h3>
            <Link
              :href="route('calendar.admin')"
              class="flex items-center px-4 py-2 rounded-lg transition"
              :class="route().current('calendar.admin')
                ? 'text-gray-900 bg-gray-100 dark:text-white dark:bg-gray-800'
                : 'text-gray-600 hover:text-gray-900 hover:bg-gray-100 dark:text-gray-400 dark:hover:text-white dark:hover:bg-gray-800'"
            >
              <svg class="h-5 w-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
              </svg>
              Calendario
            </Link>
           </div>

          <div>
            <h3 class="px-4 text-xs font-semibold uppercase tracking-wider mb-3 text-gray-500 dark:text-gray-500">
              Staff
            </h3>
            <Link
              :href="route('barbers.index')"
              class="flex items-center px-4 py-2 rounded-lg transition"
              :class="route().current('barbers.index')
                ? 'text-gray-900 bg-gray-100 dark:text-white dark:bg-gray-800'
                : 'text-gray-600 hover:text-gray-900 hover:bg-gray-100 dark:text-gray-400 dark:hover:text-white dark:hover:bg-gray-800'"
            >
              <svg class="h-5 w-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
              Barberos
            </Link>

            <Link
              :href="route('services.index')"
              class="flex items-center px-4 py-2 rounded-lg transition"
              :class="route().current('services.*')
                ? 'text-gray-900 bg-gray-100 dark:text-white dark:bg-gray-800'
                : 'text-gray-600 hover:text-gray-900 hover:bg-gray-100 dark:text-gray-400 dark:hover:text-white dark:hover:bg-gray-800'"
            >
              <svg class="h-5 w-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M14.121 14.121L19 19m-7-7l7-7m-7 7l-2.879 2.879M12 12L9.121 9.121m0 5.758a3 3 0 10-4.243 4.243 3 3 0 004.243-4.243zm0-5.758a3 3 0 10-4.243-4.243 3 3 0 004.243 4.243z" />
              </svg>
              Servicios
            </Link>

           </div>
        </nav>
      </aside>

      <!-- Contenido derecho -->
      <div class="flex-1 flex flex-col overflow-hidden">
        <!-- Header -->
        <header class="flex justify-between items-center h-20 px-6
                       border-b border-gray-200 dark:border-gray-800
                       bg-white dark:bg-gray-900">
          <!-- Toggle móvil -->
          <button class="text-gray-600 dark:text-gray-400 lg:hidden" @click="sidebarOpen = true" type="button">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path d="M4 6h16M4 12h16M4 18h7" />
            </svg>
          </button>

          <!-- Botón tema -->
          <button
            @click="toggleTheme"
            type="button"
            class="p-2 rounded-full transition
                   hover:bg-gray-100 dark:hover:bg-gray-800"
            :title="isDark ? 'Modo claro' : 'Modo oscuro'"
          >
            <svg v-if="isDark" class="w-6 h-6 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
            </svg>
            <svg v-else class="w-6 h-6 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
            </svg>
          </button>

          <div class="hidden md:flex relative w-96">
            <input
              type="text"
              class="w-full py-2 pl-10 pr-4 rounded-full border
                     bg-gray-50 text-gray-800 border-gray-200
                     dark:bg-gray-800 dark:text-gray-300 dark:border-transparent
                     focus:ring-2 focus:ring-blue-500 focus:outline-none"
              placeholder="Search..."
            >
          </div>

          <!-- Usuario -->
          <div class="relative" ref="userMenuRef">
            <button
              class="flex items-center space-x-3 focus:outline-none"
              @click="toggleUserMenu"
              type="button"
            >
              <img
                class="w-10 h-10 rounded-full border-2 border-gray-200 dark:border-gray-700"
                :src="`https://ui-avatars.com/api/?name=${encodeURIComponent(user?.name ?? 'User')}&color=7F9CF5&background=EBF4FF`"
                alt=""
              >
              <span class="hidden md:block text-sm font-medium text-gray-800 dark:text-gray-300">
                {{ user?.name ?? 'User' }}
              </span>
            </button>

            <!-- Dropdown -->
            <div
              v-show="userMenuOpen"
              class="absolute right-0 mt-2 w-48 rounded-md shadow-lg py-1 z-50 border
                     bg-white border-gray-200
                     dark:bg-gray-800 dark:border-gray-700"
            >
              <Link
                :href="route('profile.edit')"
                class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100
                       dark:text-gray-300 dark:hover:bg-gray-700"
                @click="closeUserMenu"
              >
                Perfil
              </Link>

              <Link
                :href="route('logout')"
                method="post"
                as="button"
                class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-gray-100
                       dark:text-red-400 dark:hover:bg-gray-700"
                @click="closeUserMenu"
              >
                Cerrar sesión
              </Link>
            </div>
          </div>
        </header>

        <div class="hidden sm:block">
          <slot name="breadcrumbs" />
        </div>

        <!-- Slot -->
        <main class="flex-1 overflow-x-hidden overflow-y-auto p-6 bg-gray-50 dark:bg-gray-900">
          <slot />
        </main>
      </div>
    </div>
  </div>
</template>
