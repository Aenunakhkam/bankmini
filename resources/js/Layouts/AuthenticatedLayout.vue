<script setup>
import { ref, watch, onMounted, computed } from 'vue';
import { usePage, Link } from '@inertiajs/vue3';
import Swal from 'sweetalert2';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import SidebarLink from '@/Components/SidebarLink.vue';

const page = usePage();
const showingSidebar = ref(false);
const desktopSidebarCollapsed = ref(false);

const toggleSidebar = () => {
    if (window.innerWidth >= 1024) {
        desktopSidebarCollapsed.value = !desktopSidebarCollapsed.value;
    } else {
        showingSidebar.value = !showingSidebar.value;
    }
};

// Icons as SVG strings
const icons = {
    dashboard: `<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>`,
    transaksi: `<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>`,
    pengeluaran: `<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2zM10 8.5a.5.5 0 11-1 0 .5.5 0 011 0zm5 5a.5.5 0 11-1 0 .5.5 0 011 0z"></path></svg>`,
    kategori: `<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path></svg>`,
    siswa: `<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>`,
    kelas: `<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>`,
    jurusan: `<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>`,
};

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
    <div class="flex h-screen bg-[#F8F9FA] text-slate-800 font-sans overflow-hidden">
        
        <!-- Mobile Sidebar Overlay -->
        <div v-show="showingSidebar" class="fixed inset-0 z-20 bg-black/50 transition-opacity lg:hidden" @click="toggleSidebar"></div>

        <!-- Sidebar -->
        <aside :class="[
            'fixed inset-y-0 left-0 z-30 w-64 bg-[#0f7632] text-white flex flex-col transition-all duration-300',
            'lg:static lg:h-screen lg:shrink-0',
            showingSidebar ? 'translate-x-0' : '-translate-x-full lg:translate-x-0',
            desktopSidebarCollapsed ? 'lg:-ml-64' : 'lg:ml-0'
        ]">
            
            <!-- Sidebar Header / Logo -->
            <div class="h-16 flex items-center px-6 border-b border-white/10 shrink-0">
                <Link :href="route('dashboard')" class="flex items-center gap-3">
                    <img src="/favicon.png" alt="Logo BANKMINI" class="w-10 h-10 rounded-full shadow-lg border border-white/20">
                    <span class="text-2xl font-black tracking-tighter" style="font-family: 'Montserrat', sans-serif;">BANKMINI</span>
                </Link>
            </div>

            <!-- Sidebar Navigation -->
            <nav class="flex-1 overflow-y-auto py-4 space-y-6">
                <!-- Main Dashboard -->
                <div>
                    <SidebarLink :href="route('dashboard')" :active="route().current('dashboard')" :icon="icons.dashboard">
                        Dashboard
                    </SidebarLink>
                </div>

                <!-- Keuangan Section -->
                <div>
                    <div class="px-5 text-[11px] font-bold text-yellow-400/70 uppercase tracking-widest mb-2">Layanan Teller</div>
                    <SidebarLink :href="route('teller.index')" :active="route().current('teller.*')" :icon="`<svg fill='none' stroke='currentColor' viewBox='0 0 24 24'><path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z'></path></svg>`">
                        Setor & Tarik Tunai
                    </SidebarLink>
                </div>

                <!-- Catatan Keuangan Section -->
                <div>
                    <div class="px-5 text-[11px] font-bold text-yellow-400/70 uppercase tracking-widest mb-2 mt-4">Catatan Keuangan</div>
                    <SidebarLink :href="route('catatan-keuangan.pemasukan')" :active="route().current('catatan-keuangan.pemasukan')" :icon="`<svg fill='none' stroke='currentColor' viewBox='0 0 24 24'><path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M13 7h8m0 0v8m0-8l-8 8-4-4-6 6'></path></svg>`">
                        Pemasukan
                    </SidebarLink>
                    <SidebarLink :href="route('catatan-keuangan.pengeluaran')" :active="route().current('catatan-keuangan.pengeluaran')" :icon="`<svg fill='none' stroke='currentColor' viewBox='0 0 24 24'><path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M13 17h8m0 0V9m0 8l-8-8-4 4-6-6'></path></svg>`">
                        Pengeluaran
                    </SidebarLink>
                    <SidebarLink :href="route('catatan-keuangan.laporan')" :active="route().current('catatan-keuangan.laporan')" :icon="`<svg fill='none' stroke='currentColor' viewBox='0 0 24 24'><path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z'></path></svg>`">
                        Laporan Keuangan
                    </SidebarLink>
                </div>

                <!-- Data Master Section -->
                <div>
                    <div class="px-5 text-[11px] font-bold text-yellow-400/70 uppercase tracking-widest mb-2 mt-6">Data Nasabah & Master</div>
                    <SidebarLink :href="route('academic-years.index')" :active="route().current('academic-years.*')" :icon="`<svg fill='none' stroke='currentColor' viewBox='0 0 24 24'><path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z'></path></svg>`">
                        Tahun Ajaran
                    </SidebarLink>
                    <SidebarLink :href="route('students.index')" :active="route().current('students.*')" :icon="icons.siswa">
                        Daftar Nasabah (Siswa)
                    </SidebarLink>
                    <SidebarLink :href="route('classrooms.index')" :active="route().current('classrooms.*')" :icon="icons.kelas">
                        Data Kelas
                    </SidebarLink>
                    <SidebarLink :href="route('majors.index')" :active="route().current('majors.*')" :icon="icons.jurusan">
                        Data Jurusan
                    </SidebarLink>
                </div>
                <!-- Pengaturan Section -->
                <div>
                    <div class="px-5 text-[11px] font-bold text-yellow-400/70 uppercase tracking-widest mb-2 mt-2">Pengaturan & Sistem</div>
                    <SidebarLink :href="route('settings.index')" :active="route().current('settings.*')" :icon="`<svg fill='none' stroke='currentColor' viewBox='0 0 24 24'><path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z'></path><path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M15 12a3 3 0 11-6 0 3 3 0 016 0z'></path></svg>`">
                        Pengaturan Bank
                    </SidebarLink>
                    <SidebarLink :href="route('backup.index')" :active="route().current('backup.*')" :icon="`<svg fill='none' stroke='currentColor' viewBox='0 0 24 24'><path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4'></path></svg>`">
                        Backup Data
                    </SidebarLink>
                    <SidebarLink :href="route('updates')" :active="route().current('updates')" :icon="`<svg fill='none' stroke='currentColor' viewBox='0 0 24 24'><path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z'></path></svg>`">
                        Cek Pembaruan
                    </SidebarLink>
                </div>
            </nav>

            <!-- Sidebar Footer -->
            <div class="p-4 border-t border-white/10 text-xs font-medium text-yellow-200/50 text-center">
                &copy; {{ new Date().getFullYear() }} BANKMINI
            </div>
        </aside>

        <!-- Main Content Area -->
        <div class="flex-1 flex flex-col min-w-0 overflow-hidden">
            
            <!-- Top Navbar -->
            <header class="bg-white shadow-[0_4px_20px_rgb(0,0,0,0.02)] h-16 flex items-center justify-between px-4 sm:px-6 lg:px-8 z-10 shrink-0 border-b border-gray-100">
                
                <!-- Left: Hamburger & Header Title -->
                <div class="flex items-center gap-4 min-w-0">
                    <button @click="toggleSidebar" class="p-2 -ml-2 text-gray-500 hover:text-[#0f7632] hover:bg-green-50 rounded-lg transition-colors focus:outline-none">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                    </button>
                    <div class="truncate" v-if="$slots.header">
                        <slot name="header" />
                    </div>
                </div>

                <!-- Right: Profile Dropdown -->
                <div class="flex items-center shrink-0 ml-4">
                    <div class="hidden sm:flex items-center px-4 py-1.5 mr-4 bg-green-50/50 rounded-full border border-green-100">
                        <svg class="w-4 h-4 text-green-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                        <span class="text-xs font-bold text-green-800">T.A: {{ $page.props.active_academic_year ? $page.props.active_academic_year.name : 'Belum Dipilih' }}</span>
                    </div>

                    <Dropdown align="right" width="48">
                        <template #trigger>
                            <button type="button" class="flex items-center gap-2 bg-gray-50 hover:bg-gray-100 border border-gray-200 px-2 sm:px-3 py-1.5 rounded-xl transition-colors focus:outline-none">
                                <div class="w-7 h-7 sm:w-8 sm:h-8 rounded-full bg-gradient-to-r from-[#0f7632] to-[#f59e0b] flex items-center justify-center text-white shadow-sm">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                                </div>
                                <span class="text-sm font-bold text-gray-700 hidden sm:block">{{ $page.props.auth.user.npsn }}</span>
                                <svg class="w-4 h-4 text-gray-400 hidden sm:block" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                            </button>
                        </template>

                        <template #content>
                            <div class="px-4 py-3 border-b border-gray-100">
                                <p class="text-xs text-gray-500 font-medium">Masuk sebagai</p>
                                <p class="text-sm font-bold text-[#0f7632] truncate">{{ $page.props.auth.user.email }}</p>
                            </div>
                            <DropdownLink :href="route('profile.edit')" class="font-medium text-sm text-gray-700 hover:text-[#0f7632] hover:bg-green-50">
                                Pengaturan Profil
                            </DropdownLink>
                            <DropdownLink :href="route('logout')" method="post" as="button" class="font-medium text-sm text-red-600 hover:text-red-700 hover:bg-red-50 border-t border-gray-50">
                                Keluar Sistem
                            </DropdownLink>
                        </template>
                    </Dropdown>
                </div>
            </header>

            <!-- Page Content -->
            <main class="flex-1 overflow-y-auto p-4 sm:p-6 lg:p-8">
                <slot />
            </main>
        </div>
    </div>
</template>
