<script setup>
import { computed } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
  totalCapital: {
    type: Number,
    default: 0
  },
  appointmentsPercentage: {
    type: Number,
    default: 0
  },
  salesPercentage: {
    type: Number,
    default: 0
  },
  completedAppointments: {
    type: Number,
    default: 0
  },
  pendingAppointments: {
    type: Number,
    default: 0
  },
  cancelledAppointments: {
    type: Number,
    default: 0
  },
  today: {
    type: String,
    default: ''
  },
  dateValue: {
    type: String,
    default: ''
  },
  chartData: {
    type: Object,
    default: () => ({ labels: [], data: [], year: 2026, total_year: 0 })
  }
})

const handleDateChange = (e) => {
    router.get(route('dashboard'), { date: e.target.value }, { preserveState: true })
}

const maxChartValue = computed(() => {
    if(!props.chartData || !props.chartData.data || props.chartData.data.length === 0) return 1;
    return Math.max(...props.chartData.data, 1);
})

// Variables para el Chart SVG
const chartHeight = 250 // Altura del SVG
const chartPaddingX = 20
const chartPaddingY = 20

// Generador de puntos (X, Y) para el gráfico lineal
const chartPoints = computed(() => {
    if (!props.chartData || !props.chartData.data || props.chartData.data.length === 0) return ''
    
    const count = props.chartData.data.length
    // Calculamos el espacio "x" disponible usando porcentaje para un diseño responsive
    // Retornamos un string de puntos: "x,y x,y..."
    return props.chartData.data.map((val, index) => {
        // En porcentaje de viewport width, x va de 0 a 100
        const x = (index / (count - 1)) * 100
        
        // El eje Y está invertido en SVG (y=0 es arriba)
        // Escalamos val sobre height, descontando los padding
        const ratio = val / maxChartValue.value
        // Altura disponible = 100%
        const y = 100 - (ratio * 100) 
        
        return `${x},${y}`
    })
})

const getSvgPath = computed(() => {
    const points = chartPoints.value
    if (!points || points.length === 0) return ''
    
    // Smooth line (curved) or straight line
    // Doing a straight line polyline first
    return points.join(' ')
})

// Calcula path bajo la curva para rellenar con un gradiente
const getAreaPath = computed(() => {
    const points = chartPoints.value
    if (!points || points.length === 0) return ''
    // Cierra el area hasta las esquinas inferiores
    return `${points.join(' ')} 100,100 0,100`
})

// Calculate cancellation ratio
const cancellationRatio = computed(() => {
  const total = props.completedAppointments + props.pendingAppointments + props.cancelledAppointments
  if (total === 0) return 0
  return Math.round((props.cancelledAppointments / total) * 100)
})

// Format currency
const formattedCapital = computed(() => {
  return new Intl.NumberFormat('es-HN', {
    style: 'currency',
    currency: 'HNL',
    minimumFractionDigits: 2
  }).format(props.totalCapital)
})

</script>

<template>
  <AppLayout>
    <div class="min-h-screen bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-gray-100 p-4 sm:p-6 lg:p-8 font-sans transition-colors">
      
      <!-- Top header section -->
      <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
        <div>
          <div class="flex items-center gap-3 text-sm text-gray-500 dark:text-gray-400 mb-1">
            <span>Última actualización - justo ahora</span>
            <span class="px-2 py-0.5 rounded-full bg-blue-100 text-blue-700 dark:bg-gray-800 dark:text-blue-400 border border-blue-200 dark:border-gray-700 text-xs font-semibold tracking-wide uppercase">En vivo</span>
          </div>
          <h1 class="text-3xl sm:text-4xl font-bold tracking-tight text-gray-900 dark:text-white mb-1">Resumen General</h1>
        </div>
        
        <div class="flex flex-wrap gap-2">
          <div class="relative flex items-center">
            <input 
              type="date" 
              :value="props.dateValue" 
              @change="handleDateChange"
              class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10"
            />
            <button class="px-4 py-2 rounded-xl bg-white dark:bg-[#1c1d29] hover:bg-gray-100 dark:hover:bg-[#252636] border border-gray-200 dark:border-gray-800 transition-colors text-sm font-medium flex items-center gap-2 shadow-sm pointer-events-none">
               <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
               {{ props.today }}
            </button>
          </div>
          <a :href="route('dashboard.export', { date: props.dateValue })" target="_blank" class="px-4 py-2 rounded-xl bg-white dark:bg-[#1c1d29] hover:bg-gray-100 dark:hover:bg-[#252636] border border-gray-200 dark:border-gray-800 transition-colors text-sm font-medium flex items-center gap-2 shadow-sm">
             <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
             Exportar
          </a>
        </div>
      </div>

      <!-- Main Metric Panel -->
      <div class="rounded-3xl bg-white dark:bg-gradient-to-br dark:from-[#1c1d29] dark:to-[#13141c] border border-gray-200 dark:border-gray-800/60 p-6 sm:p-8 mb-8 shadow-xl dark:shadow-2xl relative overflow-hidden group">
        <!-- Glow effect background (mainly visible on dark mode) -->
        <div class="absolute -right-20 -top-20 w-64 h-64 bg-indigo-100 dark:bg-indigo-600/20 blur-[100px] rounded-full pointer-events-none group-hover:bg-indigo-200 dark:group-hover:bg-indigo-500/30 transition-all duration-700"></div>
        <div class="absolute -left-20 -bottom-20 w-80 h-80 bg-blue-100 dark:bg-blue-600/10 blur-[120px] rounded-full pointer-events-none"></div>
        
        <div class="relative z-10 flex flex-col lg:flex-row justify-between items-start lg:items-end gap-8">
           <div>
              <div class="flex items-center gap-3 mb-2">
                <h2 class="text-xl sm:text-2xl font-semibold text-gray-800 dark:text-gray-200">Capital Obtenido {{ props.today }}</h2>
                <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-indigo-100 text-indigo-600 dark:bg-indigo-500/20 dark:text-indigo-400">
                   <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </span>
              </div>
              <p class="text-sm text-gray-500 dark:text-gray-400 mb-6 max-w-sm">Total de ingresos generados por citas finalizadas y ventas directas registradas.</p>
              
              <div class="flex items-baseline gap-3">
                 <span class="text-5xl sm:text-7xl font-bold tracking-tighter text-gray-900 dark:text-transparent dark:bg-clip-text dark:bg-gradient-to-r dark:from-white dark:to-gray-400">
                    {{ formattedCapital }}
                 </span>
              </div>
           </div>

           <!-- Right side visual (Chart placeholder) -->
           <div class="w-full lg:w-1/3 bg-gray-50 dark:bg-[#0d0e15]/50 rounded-2xl p-5 border border-gray-100 dark:border-gray-800/50 backdrop-blur-sm">
              <div class="flex justify-between items-center mb-4">
                 <span class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Distribución</span>
                 <span class="text-xs bg-white dark:bg-gray-800 border border-gray-200 dark:border-transparent px-2 py-1 rounded-md text-gray-700 dark:text-gray-300 shadow-sm dark:shadow-none">Global</span>
              </div>
              
              <!-- Dummy progress bar / visualizer -->
              <div class="space-y-4">
                 <div class="relative w-full h-2 bg-gray-200 dark:bg-gray-800 rounded-full overflow-hidden">
                    <div class="absolute top-0 left-0 h-full bg-gradient-to-r from-indigo-500 to-purple-500 rounded-full" :style="{ width: props.appointmentsPercentage + '%' }"></div>
                 </div>
                 
                 <div class="flex justify-between items-center text-xs text-gray-600 dark:text-gray-400">
                    <div class="flex items-center gap-2">
                       <div class="w-2 h-2 rounded-full bg-indigo-500"></div>
                       <span>Citas</span>
                    </div>
                    <span class="font-medium text-gray-900 dark:text-gray-200">{{ props.appointmentsPercentage }}%</span>
                 </div>
                 
                 <div class="flex justify-between items-center text-xs text-gray-600 dark:text-gray-400 pt-1 border-t border-gray-200 dark:border-gray-800/50">
                    <div class="flex items-center gap-2">
                       <div class="w-2 h-2 rounded-full bg-purple-500"></div>
                       <span>Ventas directas</span>
                    </div>
                    <span class="font-medium text-gray-900 dark:text-gray-200">{{ props.salesPercentage }}%</span>
                 </div>
              </div>
           </div>
        </div>
      </div>

      <!-- Secondary Metrics Grid (Placeholders) -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
         <!-- Citas Finalizadas -->
         <div class="rounded-2xl bg-white dark:bg-[#1c1d29] border border-gray-200 dark:border-gray-800/60 p-5 hover:border-gray-300 dark:hover:border-gray-700 transition-colors shadow-sm dark:shadow-none">
            <h3 class="text-sm text-gray-500 dark:text-gray-400 mb-1">Citas Finalizadas</h3>
            <div class="flex items-end justify-between">
               <span class="text-2xl font-bold text-gray-900 dark:text-white">{{ props.completedAppointments }}</span>
               <span v-if="props.completedAppointments > 0" class="text-xs text-green-600 dark:text-green-400 flex items-center gap-1">En el día <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg></span>
            </div>
            <div class="mt-4 h-10 w-full rounded-lg bg-green-50 dark:bg-green-500/10 border border-green-100 dark:border-green-500/20 flex items-center justify-center text-green-600 dark:text-green-400 text-xs font-semibold uppercase tracking-wider">
               Éxito
            </div>
         </div>

         <!-- Citas Pendientes -->
         <div class="rounded-2xl bg-white dark:bg-[#1c1d29] border border-gray-200 dark:border-gray-800/60 p-5 hover:border-gray-300 dark:hover:border-gray-700 transition-colors shadow-sm dark:shadow-none">
            <h3 class="text-sm text-gray-500 dark:text-gray-400 mb-1">Citas Pendientes</h3>
            <div class="flex items-end justify-between">
               <span class="text-2xl font-bold text-gray-900 dark:text-white">{{ props.pendingAppointments }}</span>
               <span class="text-xs text-amber-600 dark:text-amber-400 flex items-center gap-1">En curso/Futuras <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg></span>
            </div>
            <div class="mt-4 h-10 w-full rounded-lg bg-amber-50 dark:bg-amber-500/10 border border-amber-100 dark:border-amber-500/20 flex items-center justify-center text-amber-600 dark:text-amber-400 text-xs font-semibold uppercase tracking-wider">
               Por Atender
            </div>
         </div>

         <!-- Citas Canceladas -->
         <div class="rounded-2xl bg-white dark:bg-[#1c1d29] border border-gray-200 dark:border-gray-800/60 p-5 hover:border-gray-300 dark:hover:border-gray-700 transition-colors shadow-sm dark:shadow-none">
            <h3 class="text-sm text-gray-500 dark:text-gray-400 mb-1">Citas Canceladas</h3>
            <div class="flex items-end justify-between">
               <span class="text-2xl font-bold text-gray-900 dark:text-white">{{ props.cancelledAppointments }}</span>
               <span v-if="props.cancelledAppointments > 0" class="text-xs text-red-600 dark:text-red-400 flex items-center gap-1">Perdidas <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg></span>
            </div>
            <div class="mt-4 h-10 w-full rounded-lg bg-red-50 dark:bg-red-500/10 border border-red-100 dark:border-red-500/20 flex items-center justify-center text-red-600 dark:text-red-400 text-xs font-semibold uppercase tracking-wider">
               No Efectuadas
            </div>
         </div>

         <!-- Ratio de Cancelaciones -->
         <div class="rounded-2xl bg-white dark:bg-[#1c1d29] border border-gray-200 dark:border-gray-800/60 p-5 hover:border-gray-300 dark:hover:border-gray-700 transition-colors shadow-sm dark:shadow-none">
            <h3 class="text-sm text-gray-500 dark:text-gray-400 mb-1">Ratio de Cancelaciones</h3>
            <div class="flex items-end justify-between">
               <span class="text-2xl font-bold text-gray-900 dark:text-white">{{ cancellationRatio }}%</span>
            </div>
            <div class="mt-4 h-10 w-full rounded-lg bg-gray-50 dark:bg-gray-800/50 flex items-center justify-center text-gray-500 dark:text-gray-400 text-xs font-medium border border-gray-100 dark:border-transparent relative overflow-hidden">
                <div class="absolute left-0 top-0 h-full bg-red-500/20 dark:bg-red-500/30" :style="{ width: cancellationRatio + '%' }"></div>
                <span class="relative z-10">{{ cancellationRatio === 0 ? 'Sin datos' : 'Impacto' }}</span>
            </div>
         </div>
      </div>

      <!-- Gráfico Mensual Evolutivo (Lineal) -->
      <div class="mt-8 rounded-3xl bg-white dark:bg-gradient-to-br dark:from-[#1c1d29] dark:to-[#13141c] border border-gray-200 dark:border-gray-800/60 p-6 sm:p-8 shadow-xl dark:shadow-2xl">
         <div class="flex flex-col sm:flex-row justify-between sm:items-end mb-8 gap-4">
            <div>
               <h3 class="text-xl sm:text-2xl font-bold text-gray-900 dark:text-white mb-2">Ingresos de {{ props.chartData.monthName }} {{ props.chartData.year }}</h3>
               <p class="text-sm font-medium text-gray-500 dark:text-gray-400">
                  Total del mes: 
                  <span class="text-indigo-600 dark:text-indigo-400 font-bold">
                      {{ new Intl.NumberFormat('es-HN', { style: 'currency', currency: 'HNL' }).format(props.chartData.total_month) }}
                  </span>
               </p>
            </div>
            
            <div class="text-xs text-zinc-500 bg-zinc-100 dark:bg-zinc-800/50 px-3 py-1.5 rounded-lg border border-zinc-200 dark:border-zinc-700 w-fit">
                Evaluación diaria de ingresos
            </div>
         </div>
         
         <!-- Chart SVG interactivo -->
         <div class="relative w-full h-64 mt-4 select-none">
            
            <!-- Guias horizontales de fondo -->
            <div class="absolute inset-x-0 inset-y-0 flex flex-col justify-between pointer-events-none">
               <div class="w-full flex items-center gap-2">
                   <div class="w-12 text-xs text-zinc-400 text-right">{{ maxChartValue > 1 ? new Intl.NumberFormat('es-HN', { notation: "compact", compactDisplay: "short" }).format(maxChartValue) : '' }}</div>
                   <div class="flex-1 border-t border-dashed border-zinc-200 dark:border-zinc-700/50"></div>
               </div>
               <div class="w-full flex items-center gap-2">
                   <div class="w-12 text-xs text-zinc-400 text-right">{{ maxChartValue > 1 ? new Intl.NumberFormat('es-HN', { notation: "compact", compactDisplay: "short" }).format(maxChartValue / 2) : '' }}</div>
                   <div class="flex-1 border-t border-dashed border-zinc-200 dark:border-zinc-700/50"></div>
               </div>
               <div class="w-full flex items-center gap-2">
                   <div class="w-12 text-xs text-zinc-400 text-right">0</div>
                   <div class="flex-1 border-t border-dashed border-zinc-200 dark:border-zinc-700/50 text-xs"></div>
               </div>
            </div>

            <!-- SVG Container (Responsive) -->
            <div class="absolute left-10 md:left-14 right-2 top-2 bottom-6 z-10 w-[calc(100%-3rem)] md:w-[calc(100%-4rem)]">
                <svg preserveAspectRatio="none" width="100%" height="100%" viewBox="0 0 100 100" class="overflow-visible">
                   <defs>
                      <linearGradient id="gradientLine" x1="0" y1="0" x2="0" y2="1">
                         <stop offset="0%" stop-color="rgba(99, 102, 241, 0.4)" />
                         <stop offset="100%" stop-color="rgba(99, 102, 241, 0)" />
                      </linearGradient>
                      <filter id="glow" x="-20%" y="-20%" width="140%" height="140%">
                         <feGaussianBlur stdDeviation="1.5" result="blur" />
                         <feComposite in="SourceGraphic" in2="blur" operator="over" />
                      </filter>
                   </defs>
                   
                   <!-- Area sombreada -->
                   <polygon :points="getAreaPath" fill="url(#gradientLine)" class="transition-all duration-700 ease-in-out"></polygon>
                   
                   <!-- Linea principal -->
                   <polyline :points="getSvgPath" fill="none" class="stroke-indigo-500 line-glow transition-all duration-700 ease-in-out" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vector-effect: non-scaling-stroke;"></polyline>
                </svg>

                <!-- Puntos interactivos / Tooltips posicionales -->
                <div class="absolute inset-0 flex justify-between pointer-events-none w-full">
                    <div v-for="(val, index) in props.chartData.data" :key="index" class="relative group h-full flex items-end justify-center pointer-events-auto cursor-crosshair flex-1 max-w-10">
                        
                        <div v-if="val > 0" class="absolute z-40 w-2 h-2 rounded-full border-2 border-indigo-500 bg-white dark:bg-zinc-900 shadow-[0_0_8px_rgba(99,102,241,0.8)] opacity-0 group-hover:opacity-100 transition-opacity" :style="{ bottom: `${(val / maxChartValue) * 100}%` }"></div>

                        <!-- Columna invisible para el hover -->
                        <div class="w-full h-full bg-indigo-500/0 hover:bg-zinc-200/20 dark:hover:bg-indigo-500/10 transition-colors z-20"></div>
                        
                        <!-- Tooltip superior (Eje Y/Val) -->
                        <div class="absolute top-0 opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap bg-zinc-900 border border-zinc-700 text-white rounded-lg px-3 py-2 text-xs font-bold shadow-xl translate-y-[-110%] pointer-events-none z-30">
                            {{ props.chartData.labels[index] }} {{ props.chartData.monthName }}
                            <br/>
                            <span class="text-indigo-400 font-black mt-1 block">
                                {{ new Intl.NumberFormat('es-HN', { style: 'currency', currency: 'HNL' }).format(val) }}
                            </span>
                        </div>

                        <!-- Guideline vertical on hover -->
                        <div class="absolute top-0 bottom-0 w-[1px] bg-indigo-500/0 group-hover:bg-indigo-500/70 z-10 pointer-events-none transition-colors border-dashed border-indigo-500 hidden sm:block"></div>
                        
                        <!-- Label Inferior (Eje X/Day) -->
                        <div class="absolute -bottom-6 text-[10px] md:text-xs font-semibold text-zinc-500">
                             <span v-if="index % Math.ceil(props.chartData.data.length / 10) === 0 || index === props.chartData.data.length - 1">{{ props.chartData.labels[index] }}</span>
                        </div>
                    </div>
                </div>
            </div>
         </div>
      </div>
      
    </div>
  </AppLayout>
</template>