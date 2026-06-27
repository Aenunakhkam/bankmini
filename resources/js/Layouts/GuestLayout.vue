<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import { watch, onMounted } from 'vue';
import Swal from 'sweetalert2';

const page = usePage();

const showNotification = () => {
    const flash = page.props.flash;
    if (flash?.success) {
        Swal.fire({
            toast: true,
            position: 'top-end',
            icon: 'success',
            title: flash.success,
            showConfirmButton: false,
            timer: 4000,
            timerProgressBar: true,
        });
    }
    if (flash?.status) {
        Swal.fire({
            toast: true,
            position: 'top-end',
            icon: 'success',
            title: flash.status,
            showConfirmButton: false,
            timer: 4000,
            timerProgressBar: true,
        });
    }
};

onMounted(() => {
    showNotification();
});

watch(() => page.props.flash, () => {
    showNotification();
}, { deep: true });
</script>

<template>
    <div class="min-h-screen flex font-sans bg-[#F8F9FA] relative overflow-hidden text-slate-800">
        <!-- Abstract Background Shapes mimicking Coretax -->
        <div class="absolute top-0 left-0 w-full h-full overflow-hidden z-0 pointer-events-none">
            <div class="absolute -left-[20%] top-[-10%] w-[60%] h-[60%] rounded-full bg-gradient-to-br from-blue-100/40 to-transparent blur-3xl"></div>
            <div class="absolute -right-[10%] bottom-[-10%] w-[50%] h-[50%] rounded-full bg-gradient-to-tl from-green-100/50 to-transparent blur-3xl"></div>
        </div>

        <div class="container mx-auto px-4 sm:px-6 lg:px-8 py-8 flex flex-col lg:flex-row relative z-10 w-full h-full min-h-screen">
            
            <!-- Left Side: Branding / Imagery -->
            <div class="w-full lg:w-[55%] flex flex-col justify-between pr-0 lg:pr-12 pt-4">
                
                <!-- Logo Top Left -->
                <div class="flex items-center space-x-3 mb-8 lg:mb-16">
                    <img src="/favicon.png" alt="Logo BANKMINI" class="w-10 h-10 lg:w-12 lg:h-12 rounded-full shadow-xl border border-gray-100">
                    <span class="text-2xl lg:text-3xl font-black text-[#0f7632] tracking-tighter" style="font-family: 'Montserrat', sans-serif;">BANKMINI</span>
                </div>

                <!-- Main Hero Text -->
                <div class="mt-4 lg:mt-auto lg:mb-auto">
                    <p class="text-base sm:text-lg lg:text-xl text-[#f59e0b] font-semibold tracking-wider uppercase mb-2 lg:mb-3 drop-shadow-sm" style="font-family: 'Montserrat', sans-serif;">Selamat Datang di Aplikasi <i>MiniBank App</i></p>
                    <h1 class="text-4xl sm:text-5xl lg:text-[4.5rem] font-extrabold leading-[1.1] lg:leading-[1.05] text-[#0f7632] tracking-tighter mb-4 lg:mb-6 drop-shadow-md" style="font-family: 'Montserrat', sans-serif;">
                        Aplikasi Perbankan <br class="hidden sm:block" /> <span class="text-transparent bg-clip-text bg-gradient-to-r from-[#0f7632] to-[#16a34a]">Mini Sekolah</span>
                    </h1>
                    <div class="w-16 lg:w-24 h-1.5 bg-gradient-to-r from-[#f59e0b] to-yellow-300 rounded-full mb-8 lg:mb-10 shadow-sm"></div>
                    
                    <!-- Promotional Banner Placeholder -->
                    <div class="hidden lg:block w-full max-w-lg bg-white rounded-2xl shadow-xl border border-gray-100/50 p-6 relative overflow-hidden mt-8 group hover:shadow-2xl transition-all duration-300">
                        <div class="absolute top-0 right-0 w-24 h-24 bg-blue-50 rounded-bl-full -z-10 group-hover:scale-110 transition-transform"></div>
                        <h3 class="font-bold text-xl text-blue-900 flex items-center gap-2 mb-4">
                            Update <span class="text-green-500">Tampilan</span>
                            <span class="px-2 py-0.5 bg-red-500 text-white text-xs font-bold rounded-full transform -rotate-12">NEW</span>
                        </h3>
                        
                        <div class="grid grid-cols-2 gap-4">
                            <div class="flex items-start gap-2">
                                <svg class="w-5 h-5 text-green-500 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                <div>
                                    <p class="text-sm font-bold text-gray-800 leading-tight">Fully responsive</p>
                                    <p class="text-xs text-gray-500">semua perangkat</p>
                                </div>
                            </div>
                            <div class="flex items-start gap-2">
                                <svg class="w-5 h-5 text-green-500 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                <div>
                                    <p class="text-sm font-bold text-gray-800 leading-tight">User-friendly</p>
                                    <p class="text-xs text-gray-500">tampilan lebih segar</p>
                                </div>
                            </div>
                            <div class="flex items-start gap-2">
                                <svg class="w-5 h-5 text-green-500 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                <div>
                                    <p class="text-sm font-bold text-gray-800 leading-tight">Dashboard Interaktif</p>
                                    <p class="text-xs text-gray-500">analitik realtime</p>
                                </div>
                            </div>
                            <div class="flex items-start gap-2">
                                <svg class="w-5 h-5 text-green-500 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                <div>
                                    <p class="text-sm font-bold text-gray-800 leading-tight">Automasi BKU</p>
                                    <p class="text-xs text-gray-500">direct posting</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mt-4 pt-4 border-t border-gray-100">
                            <div class="inline-block bg-yellow-400 text-[#0f7632] text-xs font-bold px-3 py-1 rounded-full">
                                Penyempurnaan layanan dalam BANKMINI dilakukan secara bertahap.
                            </div>
                        </div>
                    </div>
                </div>

                <div class="hidden lg:block mt-8 text-sm text-gray-400 font-medium pb-8">
                    &copy; {{ new Date().getFullYear() }} BANKMINI - Aplikasi Perbankan Mini Terpadu
                </div>
            </div>

            <!-- Right Side: Form Area -->
            <div class="w-full lg:w-[45%] flex flex-col justify-center items-center py-6 lg:py-0">
                <div class="w-full max-w-[480px]">
                    <div class="bg-white rounded-3xl shadow-[0_10px_40px_rgb(0,0,0,0.06)] border border-gray-100 p-6 sm:p-10 w-full relative">
                        <slot />
                    </div>
                </div>
            </div>

        </div>
    </div>
</template>
