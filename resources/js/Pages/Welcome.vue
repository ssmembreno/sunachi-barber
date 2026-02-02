<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { ref, onMounted, onUnmounted } from 'vue';
import Dropdown from '@/Components/Dropdown.vue';

defineProps({
    canLogin: Boolean,
    canRegister: Boolean,
    laravelVersion: String,
    phpVersion: String,
});

const isScrolled = ref(false);

const handleScroll = () => {
    isScrolled.value = window.scrollY > 50;
};

onMounted(() => {
    window.addEventListener('scroll', handleScroll);
});

onUnmounted(() => {
    window.removeEventListener('scroll', handleScroll);
});

const services = [
    {
        name: 'Corte de Pelo',
        price: '$25',
        image: '/images/landings/haircut.png',
        desc: 'Corte clásico o moderno realizado por expertos.'
    },
    {
        name: 'Arreglo de Barba',
        price: '$15',
        image: '/images/landings/beard.png',
        desc: 'Perfilado y cuidado con productos premium.'
    },
    {
        name: 'Afeitado Clásico',
        price: '$20',
        image: '/images/landings/shave.png',
        desc: 'Tradición con toalla caliente y navaja.'
    },
    {
        name: 'Pack Corte + Barba',
        price: '$35',
        image: '/images/landings/pack.png',
        desc: 'Nuestro servicio completo para un look impecable.'
    }
];

const testimonials = [
    { name: 'Carlos Ruiz', rating: 5, text: 'La mejor experiencia que he tenido en una barbería. Atención de primera.' },
    { name: 'Miguel Ángel', rating: 5, text: 'Ambiente increíble y cortes precisos. Se nota el profesionalismo.' },
    { name: 'Juan Pérez', rating: 4, text: 'Excelente servicio y puntualidad. El pack completo vale mucho la pena.' }
];
</script>

<template>
    <Head title="Bienvenido a Sunachi Barber" />

    <div class="min-h-screen bg-zinc-950 text-zinc-100 selection:bg-amber-500 selection:text-black">
        <!-- Header -->
        <header 
            :class="[
                'fixed top-0 z-50 w-full transition-[background-color,border-color,padding,backdrop-filter] duration-500 px-6 md:px-12 border-b',
                isScrolled 
                    ? 'bg-zinc-950/80 backdrop-blur-xl border-zinc-800 py-3' 
                    : 'bg-transparent border-transparent py-5'
            ]"
        >
            <div class="max-w-7xl mx-auto flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="text-2xl font-black tracking-tighter text-white">SUNACHI <span class="text-amber-500">BARBER</span></span>
                </div>

                <nav class="hidden lg:flex items-center gap-8 text-sm font-medium uppercase tracking-widest text-zinc-400">
                    <a href="#inicio" class="hover:text-amber-500 transition-colors">Inicio</a>
                    <a href="#servicios" class="hover:text-amber-500 transition-colors">Servicios</a>
                    <a href="#precios" class="hover:text-amber-500 transition-colors">Precios</a>
                    <a href="#galeria" class="hover:text-amber-500 transition-colors">Galería</a>
                    <a href="#contacto" class="hover:text-amber-500 transition-colors">Contacto</a>
                </nav>

                <div class="flex items-center gap-4">
                    <div v-if="$page.props.auth.user" class="flex items-center gap-4">
                        <Dropdown align="right" width="48" contentClasses="py-1 bg-zinc-950 border border-zinc-800">
                            <template #trigger>
                                <button class="flex items-center gap-2 text-sm font-bold uppercase tracking-widest text-zinc-400 hover:text-amber-500 transition-colors focus:outline-none">
                                    {{ $page.props.auth.user.name }}
                                    <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                    </svg>
                                </button>
                            </template>

                            <template #content>
                                <Link :href="route('profile.edit')" class="block w-full px-4 py-2 text-start text-sm leading-5 text-zinc-400 hover:bg-zinc-900 hover:text-amber-500 transition duration-150 ease-in-out"> 
                                    Ver perfil 
                                </Link>
                                <Link :href="route('logout')" method="post" as="button" class="block w-full px-4 py-2 text-start text-sm leading-5 text-zinc-400 hover:bg-zinc-900 hover:text-amber-500 transition duration-150 ease-in-out"> 
                                    Cerrar sesión 
                                </Link>
                            </template>
                        </Dropdown>
                    </div>
                    <template v-else>
                        <Link v-if="canLogin" :href="route('login')" class="text-sm font-semibold hover:text-amber-500 transition-colors">
                            Iniciar sesión
                        </Link>
                        <Link v-if="canRegister" :href="route('register')" class="hidden sm:block px-5 py-2.5 bg-amber-500 hover:bg-amber-600 text-black text-xs font-bold uppercase tracking-widest rounded-full transition-all duration-300">
                            Registrarse
                        </Link>
                    </template>
                </div>
            </div>
        </header>

        <!-- Hero -->
        <section id="inicio" class="relative h-screen flex items-center justify-center overflow-hidden">
            <div class="absolute inset-0">
                <img src="/images/landings/hero.png" alt="Barbershop" class="w-full h-full object-cover brightness-50" />
                <div class="absolute inset-0 bg-gradient-to-t from-zinc-950 via-zinc-950/20 to-transparent"></div>
            </div>
            
            <div class="relative z-10 text-center px-6 max-w-4xl">
                <h1 class="text-5xl md:text-8xl font-black mb-6 tracking-tight leading-none text-white italic">
                    DONDE EL ESTILO <br>
                    <span class="text-amber-500">ENCUENTRA LA TRADICIÓN</span>
                </h1>
                <p class="text-lg md:text-xl text-zinc-300 mb-10 max-w-2xl mx-auto">
                    Vive una experiencia premium de barbería en un ambiente clásico con un toque contemporáneo. Tu imagen es nuestra prioridad.
                </p>
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <Link :href="route('booking.index')" class="px-8 py-4 bg-amber-500 hover:bg-amber-600 text-black font-bold uppercase tracking-widest rounded-lg transition-all transform hover:scale-105">
                        Realizar una reserva
                    </Link>
                    <button class="px-8 py-4 bg-transparent border border-zinc-700 hover:border-amber-500 text-white font-bold uppercase tracking-widest rounded-lg transition-all">
                        Ver servicios
                    </button>
                </div>
            </div>
        </section>

        <!-- Features -->
        <section class="py-24 bg-zinc-950 border-y border-zinc-900">
            <div class="max-w-7xl mx-auto px-6 grid grid-cols-1 md:grid-cols-3 gap-12 text-center">
                <div class="group">
                    <div class="w-16 h-16 bg-zinc-900 border border-zinc-800 rounded-full flex items-center justify-center mx-auto mb-6 group-hover:border-amber-500 transition-colors">
                        <svg class="w-8 h-8 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                    </div>
                    <h3 class="text-xl font-bold mb-3">Barberos profesionales</h3>
                    <p class="text-zinc-500">Expertos en técnicas clásicas y modernas para cada tipo de cabello.</p>
                </div>
                <div class="group">
                    <div class="w-16 h-16 bg-zinc-900 border border-zinc-800 rounded-full flex items-center justify-center mx-auto mb-6 group-hover:border-amber-500 transition-colors">
                        <svg class="w-8 h-8 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <h3 class="text-xl font-bold mb-3">Atención puntual</h3>
                    <p class="text-zinc-500">Respetamos tu tiempo. Sistema de reservas eficiente y sin esperas.</p>
                </div>
                <div class="group">
                    <div class="w-16 h-16 bg-zinc-900 border border-zinc-800 rounded-full flex items-center justify-center mx-auto mb-6 group-hover:border-amber-500 transition-colors">
                        <svg class="w-8 h-8 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"></path></svg>
                    </div>
                    <h3 class="text-xl font-bold mb-3">Ambiente premium</h3>
                    <p class="text-zinc-500">Relájate con una bebida premium mientras cuidamos de tu estilo.</p>
                </div>
            </div>
        </section>

        <!-- Services -->
        <section id="servicios" class="py-24 bg-zinc-900/30">
            <div class="max-w-7xl mx-auto px-6">
                <div class="text-center mb-16">
                    <h2 class="text-sm font-bold uppercase tracking-[0.3em] text-amber-500 mb-4">Lo que mejor hacemos</h2>
                    <h3 class="text-4xl md:text-5xl font-black text-white">NUESTROS SERVICIOS</h3>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
                    <div v-for="service in services" :key="service.name" class="group relative bg-zinc-950 rounded-2xl overflow-hidden border border-zinc-800 hover:border-amber-500/50 transition-all duration-500 shadow-2xl">
                        <div class="h-64 overflow-hidden">
                            <img :src="service.image" :alt="service.name" class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-110" />
                        </div>
                        <div class="p-8">
                            <div class="flex justify-between items-start mb-4">
                                <h4 class="text-xl font-bold text-white">{{ service.name }}</h4>
                                <span class="text-amber-500 font-bold bg-amber-500/10 px-3 py-1 rounded-lg text-sm">{{ service.price }}</span>
                            </div>
                            <p class="text-zinc-500 text-sm mb-6">{{ service.desc }}</p>
                            <!-- <Link :href="route('calendar.client.index')" class="block text-center w-full py-3 bg-zinc-900 border border-zinc-800 text-zinc-300 text-xs font-bold uppercase tracking-widest rounded-lg hover:bg-amber-500 hover:text-black hover:border-amber-500 transition-all">
                                Reservar ahora
                            </Link> -->
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Testimonials -->
        <section class="py-24 border-y border-zinc-900 flex items-center justify-center">
            <div class="max-w-5xl mx-auto px-6 text-center">
                <div class="mb-16">
                    <h2 class="text-sm font-bold uppercase tracking-[0.3em] text-amber-500 mb-4">Testimonios</h2>
                    <h3 class="text-4xl font-black text-white italic">LO QUE DICEN NUESTROS CLIENTES</h3>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    <div v-for="t in testimonials" :key="t.name" class="bg-zinc-900/50 p-8 rounded-2xl border border-zinc-800">
                        <div class="flex justify-center mb-4">
                            <div v-for="i in 5" :key="i" class="text-amber-500">
                                <svg :class="['w-5 h-5', i > t.rating ? 'text-zinc-700' : 'fill-current']" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" /></svg>
                            </div>
                        </div>
                        <p class="text-zinc-400 italic mb-6">"{{ t.text }}"</p>
                        <h4 class="text-white font-bold">{{ t.name }}</h4>
                    </div>
                </div>
            </div>
        </section>

        <!-- Contact & Location -->
        <section id="contacto" class="py-24">
            <div class="max-w-7xl mx-auto px-6 grid grid-cols-1 lg:grid-cols-2 gap-16">
                <div>
                    <h2 class="text-sm font-bold uppercase tracking-[0.3em] text-amber-500 mb-4">Contacto & Ubicación</h2>
                    <h3 class="text-4xl md:text-5xl font-black text-white mb-8">ENCUÉNTRANOS</h3>
                    
                    <div class="space-y-8">
                        <div class="flex items-start gap-6">
                            <div class="w-12 h-12 bg-zinc-900 border border-zinc-800 rounded-full flex items-center justify-center shrink-0">
                                <svg class="w-6 h-6 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                            </div>
                            <div>
                                <h4 class="text-lg font-bold text-white mb-1">Dirección</h4>
                                <p class="text-zinc-500">Calle Principal #123, Ciudad Centro, CP 45000</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-6">
                            <div class="w-12 h-12 bg-zinc-900 border border-zinc-800 rounded-full flex items-center justify-center shrink-0">
                                <svg class="w-6 h-6 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            </div>
                            <div>
                                <h4 class="text-lg font-bold text-white mb-1">Horarios</h4>
                                <p class="text-zinc-500">Lun - Vie: 10:00 AM - 8:00 PM <br> Sáb: 9:00 AM - 7:00 PM</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-6">
                            <div class="w-12 h-12 bg-zinc-900 border border-zinc-800 rounded-full flex items-center justify-center shrink-0">
                                <svg class="w-6 h-6 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path></svg>
                            </div>
                            <div>
                                <h4 class="text-lg font-bold text-white mb-1">Teléfono</h4>
                                <p class="text-zinc-500">+1 (234) 567-890</p>
                            </div>
                        </div>
                    </div>

                    <div class="mt-12 group overflow-hidden rounded-2xl border border-zinc-800 h-64 grayscale opacity-50 hover:grayscale-0 hover:opacity-100 transition-all duration-500">
                         <!-- Placeholder for Map -->
                         <div class="w-full h-full bg-zinc-900 flex items-center justify-center">
                            <span class="text-zinc-600 font-bold tracking-widest uppercase">Mapa Interactivo</span>
                         </div>
                    </div>
                </div>

                <div class="bg-zinc-900/50 p-10 rounded-3xl border border-zinc-800">
                    <h4 class="text-2xl font-bold text-white mb-8">ESCRIBENOS</h4>
                    <form @submit.prevent class="space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-2">
                                <label class="text-xs font-bold uppercase tracking-widest text-zinc-500">Nombre</label>
                                <input type="text" class="w-full bg-zinc-950 border border-zinc-800 rounded-lg px-4 py-3 text-white focus:border-amber-500 focus:ring-1 focus:ring-amber-500 transition-all outline-none" placeholder="Tu nombre">
                            </div>
                            <div class="space-y-2">
                                <label class="text-xs font-bold uppercase tracking-widest text-zinc-500">Email</label>
                                <input type="email" class="w-full bg-zinc-950 border border-zinc-800 rounded-lg px-4 py-3 text-white focus:border-amber-500 focus:ring-1 focus:ring-amber-500 transition-all outline-none" placeholder="tu@email.com">
                            </div>
                        </div>
                        <div class="space-y-2">
                            <label class="text-xs font-bold uppercase tracking-widest text-zinc-500">Mensaje</label>
                            <textarea rows="4" class="w-full bg-zinc-950 border border-zinc-800 rounded-lg px-4 py-3 text-white focus:border-amber-500 focus:ring-1 focus:ring-amber-500 transition-all outline-none resize-none" placeholder="¿En qué podemos ayudarte?"></textarea>
                        </div>
                        <button type="submit" class="w-full py-4 bg-amber-500 hover:bg-amber-600 text-black font-bold uppercase tracking-widest rounded-lg transition-all transform hover:scale-[1.02]">
                            Enviar mensaje
                        </button>
                    </form>
                </div>
            </div>
        </section>

        <!-- Footer -->
        <footer class="py-12 border-t border-zinc-900 bg-zinc-950">
            <div class="max-w-7xl mx-auto px-6">
                <div class="flex flex-col md:flex-row items-center justify-between gap-8 mb-12">
                    <div class="flex items-center gap-2">
                        <span class="text-2xl font-black tracking-tighter text-white">SUNACHI <span class="text-amber-500">BARBER</span></span>
                    </div>
                    
                    <div class="flex gap-8 text-sm text-zinc-500 uppercase tracking-widest font-bold">
                        <a href="#" class="hover:text-amber-500 transition-colors">Instagram</a>
                        <a href="#" class="hover:text-amber-500 transition-colors">Facebook</a>
                        <a href="#" class="hover:text-amber-500 transition-colors">TikTok</a>
                    </div>
                </div>
                
                <div class="flex flex-col md:flex-row items-center justify-between gap-6 pt-12 border-t border-zinc-900 text-xs text-zinc-600 uppercase tracking-widest">
                    <p>&copy; 2026 Sunachi Barber. Todos los derechos reservados.</p>
                    <p>Laravel v{{ laravelVersion }} (PHP v{{ phpVersion }})</p>
                </div>
            </div>
        </footer>
    </div>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap');

:deep(html) {
    scroll-behavior: smooth;
    font-family: 'Inter', sans-serif;
}
</style>
