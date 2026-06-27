<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { Bar } from 'vue-chartjs';
import { Chart as ChartJS, Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale } from 'chart.js';

ChartJS.register(Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale);

const props = defineProps({
    stats: Object,
    recent_transactions: Array,
    chartData: Object,
});

const formatRupiah = (number) => {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(number);
};

const selectedYear = ref(props.chartData?.selected_year || new Date().getFullYear());

const handleYearChange = () => {
    router.get(route('dashboard'), { year: selectedYear.value }, { preserveState: true, preserveScroll: true });
};

const barChartData = computed(() => ({
    labels: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agt', 'Sep', 'Okt', 'Nov', 'Des'],
    datasets: [
        {
            label: 'Pemasukan',
            backgroundColor: '#16a34a',
            borderRadius: 4,
            data: props.chartData?.income || Array(12).fill(0)
        },
        {
            label: 'Pengeluaran',
            backgroundColor: '#ea580c',
            borderRadius: 4,
            data: props.chartData?.expense || Array(12).fill(0)
        }
    ]
}));

const chartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: { position: 'top' },
        tooltip: {
            callbacks: {
                label: function(context) {
                    let label = context.dataset.label || '';
                    if (label) {
                        label += ': ';
                    }
                    if (context.parsed.y !== null) {
                        label += new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(context.parsed.y);
                    }
                    return label;
                }
            }
        }
    },
    scales: {
        y: {
            beginAtZero: true,
            ticks: {
                callback: function(value) {
                    if (value === 0) return '0';
                    return 'Rp ' + (value / 1000).toLocaleString('id-ID') + 'k';
                }
            }
        }
    }
};
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-extrabold text-xl md:text-2xl text-[#0f7632] leading-tight">Dashboard Utama</h2>
        </template>

        <!-- Welcome Banner & Quick Actions -->
        <div class="bg-white overflow-hidden shadow-[0_8px_30px_rgb(0,0,0,0.04)] rounded-2xl md:rounded-3xl border border-gray-100/60 relative mb-6">
            <div class="absolute top-0 left-0 w-full h-1.5 bg-gradient-to-r from-[#0f7632] to-[#f59e0b]"></div>
            <div class="p-5 md:p-8 text-gray-900 flex flex-col lg:flex-row lg:items-center justify-between gap-6">
                <div>
                    <div class="flex items-center gap-4 mb-3">
                        <div class="w-16 h-16 md:w-20 md:h-20 shrink-0">
                            <img src="/favicon.png" alt="Logo Bank Mini" class="w-full h-full object-contain rounded-xl drop-shadow-sm" />
                        </div>
                        <div>
                            <h2 class="text-2xl md:text-3xl font-extrabold text-[#0f7632] tracking-tight">Selamat Datang di Bank Mini</h2>
                            <p class="text-sm md:text-base text-gray-500 font-medium mt-2">Halo <span class="font-bold text-gray-700">{{ $page.props.auth.user.npsn }}</span>, berikut adalah ringkasan aktivitas perbankan Anda hari ini.</p>
                        </div>
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                    <Link :href="route('teller.index')" class="flex items-center justify-center px-5 py-2.5 rounded-xl font-bold text-sm text-white bg-gradient-to-r from-[#0f7632] to-[#f59e0b] hover:from-[#064e3b] hover:to-[#0f7632] shadow-lg transition-all active:scale-[0.98]">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                        Setor Tunai
                    </Link>
                    <Link :href="route('teller.index')" class="flex items-center justify-center px-5 py-2.5 rounded-xl font-bold text-sm bg-white border border-gray-200 text-gray-700 hover:bg-gray-50 shadow-sm transition-all active:scale-[0.98]">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2zM10 8.5a.5.5 0 11-1 0 .5.5 0 011 0zm5 5a.5.5 0 11-1 0 .5.5 0 011 0z"></path></svg>
                        Tarik Tunai
                    </Link>
                    <a :href="route('reports.bku.pdf')" target="_blank" class="flex items-center justify-center px-5 py-2.5 rounded-xl font-bold text-sm bg-white border border-gray-200 text-gray-700 hover:bg-gray-50 shadow-sm transition-all active:scale-[0.98]">
                        <svg class="w-4 h-4 mr-2 text-[#0f7632]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                        Cetak Mutasi Buku
                    </a>
                </div>
            </div>
        </div>

        <!-- 5 Metric Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-5 gap-4 md:gap-6 mb-6">
            <!-- Total Siswa -->
            <div class="bg-white overflow-hidden shadow-[0_8px_30px_rgb(0,0,0,0.04)] rounded-2xl border border-gray-100/60 p-5 relative group hover:-translate-y-1 transition-all">
                <div class="absolute top-0 right-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
                    <svg class="w-16 h-16 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                </div>
                <div class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Total Nasabah Aktif</div>
                <div class="text-3xl font-black text-indigo-600">{{ stats?.total_students || 0 }}</div>
                <div class="mt-3 text-[10px] font-semibold text-green-800 bg-green-50/80 border border-green-100 inline-block px-2 py-1 rounded-md">Terdaftar di Bank</div>
            </div>

            <!-- Saldo Kas -->
            <div class="bg-white overflow-hidden shadow-[0_8px_30px_rgb(0,0,0,0.04)] rounded-2xl border border-gray-100/60 p-5 relative group hover:-translate-y-1 transition-all">
                <div class="absolute top-0 right-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
                    <svg class="w-16 h-16 text-[#f59e0b]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
                <div class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Total Saldo Kas</div>
                <div class="text-3xl font-black text-[#0f7632]">{{ formatRupiah(stats?.total_kas || 0) }}</div>
                <div class="mt-3 text-[10px] font-semibold text-blue-700 bg-blue-50/80 border border-blue-100 inline-block px-2 py-1 rounded-md">Buku Kas Umum</div>
            </div>

            <!-- Pemasukan -->
            <div class="bg-white overflow-hidden shadow-[0_8px_30px_rgb(0,0,0,0.04)] rounded-2xl border border-gray-100/60 p-5 relative group hover:-translate-y-1 transition-all">
                <div class="absolute top-0 right-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
                    <svg class="w-16 h-16 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                </div>
                <div class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Total Setoran (Bulan Ini)</div>
                <div class="text-3xl font-black text-green-600">{{ formatRupiah(stats?.income_this_month || 0) }}</div>
                <div class="mt-3 text-[10px] font-semibold text-green-700 bg-green-50/80 border border-green-100 inline-block px-2 py-1 rounded-md">Pemasukan Bank</div>
            </div>

            <!-- Pengeluaran -->
            <div class="bg-white overflow-hidden shadow-[0_8px_30px_rgb(0,0,0,0.04)] rounded-2xl border border-gray-100/60 p-5 relative group hover:-translate-y-1 transition-all">
                <div class="absolute top-0 right-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
                    <svg class="w-16 h-16 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6"></path></svg>
                </div>
                <div class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Total Penarikan (Bulan Ini)</div>
                <div class="text-3xl font-black text-orange-600">{{ formatRupiah(stats?.expense_this_month || 0) }}</div>
                <div class="mt-3 text-[10px] font-semibold text-orange-700 bg-orange-50/80 border border-orange-100 inline-block px-2 py-1 rounded-md">Pengeluaran Bank</div>
            </div>

            <!-- Tunggakan -->
            <div class="bg-white overflow-hidden shadow-[0_8px_30px_rgb(0,0,0,0.04)] rounded-2xl border border-gray-100/60 p-5 relative group hover:-translate-y-1 transition-all">
                <div class="absolute top-0 right-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
                    <svg class="w-16 h-16 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                </div>
                <div class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Target Tabungan (Tahunan)</div>
                <div class="text-3xl font-black text-blue-600">{{ formatRupiah(stats?.total_tunggakan || 0) }}</div>
                <div class="mt-3 text-[10px] font-semibold text-blue-700 bg-blue-50/80 border border-blue-100 inline-block px-2 py-1 rounded-md">Estimasi Saldo</div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
            <!-- Chart Area (Mockup) -->
            <div class="lg:col-span-2 bg-white overflow-hidden shadow-[0_8px_30px_rgb(0,0,0,0.04)] rounded-2xl border border-gray-100/60 p-6 flex flex-col">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-bold text-[#0f7632] text-lg">Grafik Arus Kas (Bulanan)</h3>
                    <select v-model="selectedYear" @change="handleYearChange" class="text-sm border-gray-200 rounded-lg text-gray-600 focus:ring-[#0f7632] focus:border-[#0f7632]">
                        <option :value="new Date().getFullYear()">{{ new Date().getFullYear() }}</option>
                        <option :value="new Date().getFullYear() - 1">{{ new Date().getFullYear() - 1 }}</option>
                        <option :value="new Date().getFullYear() - 2">{{ new Date().getFullYear() - 2 }}</option>
                    </select>
                </div>
                <div class="flex-1 bg-white min-h-[300px] relative">
                    <Bar v-if="props.chartData" :data="barChartData" :options="chartOptions" />
                </div>
            </div>

            <!-- Recent Transactions (Mockup) -->
            <div class="bg-white overflow-hidden shadow-[0_8px_30px_rgb(0,0,0,0.04)] rounded-2xl border border-gray-100/60 p-6 flex flex-col">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-bold text-[#0f7632] text-lg">Transaksi Terakhir</h3>
                    <Link :href="route('transactions.index')" class="text-xs font-bold text-blue-600 hover:text-blue-800">Lihat Semua</Link>
                </div>
                
                <div v-if="recent_transactions?.length === 0" class="flex-1 flex flex-col justify-center items-center bg-gray-50/50 rounded-xl border border-gray-100 border-dashed min-h-[250px]">
                    <svg class="w-10 h-10 text-gray-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path></svg>
                    <p class="text-sm font-medium text-gray-400">Belum ada transaksi tercatat.</p>
                </div>
                <div v-else class="flex-1">
                    <ul class="space-y-4">
                        <li v-for="tx in recent_transactions" :key="tx.id" class="flex items-center justify-between p-3 hover:bg-gray-50 rounded-xl transition-colors">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-green-50 text-green-600 flex items-center justify-center">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                                </div>
                                <div>
                                    <p class="font-bold text-gray-800 text-sm">{{ tx.type === 'deposit' ? 'Setoran Tunai' : 'Penarikan Tunai' }}</p>
                                    <p class="text-xs text-gray-500">{{ tx.student?.name || 'Nasabah' }}</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="font-bold text-sm" :class="tx.type === 'deposit' ? 'text-green-600' : 'text-orange-600'">
                                    {{ tx.type === 'deposit' ? '+' : '-' }}{{ formatRupiah(tx.amount) }}
                                </p>
                                <p class="text-[10px] text-gray-400">{{ new Date(tx.created_at).toLocaleDateString('id-ID') }}</p>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Informasi Aplikasi -->
        <div class="bg-white overflow-hidden shadow-[0_8px_30px_rgb(0,0,0,0.04)] rounded-2xl md:rounded-3xl border border-gray-100/60 flex flex-col mb-6 relative">
            <div class="absolute top-0 right-0 w-64 h-64 bg-green-50 rounded-full blur-3xl opacity-50 -mr-20 -mt-20 pointer-events-none"></div>
            <div class="p-6 md:p-8 border-b border-gray-50 relative z-10 flex items-center justify-between">
                <div>
                    <h3 class="font-extrabold text-[#0f7632] text-lg md:text-xl flex items-center">
                        <svg class="w-6 h-6 mr-2 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        Informasi Aplikasi
                    </h3>
                    <p class="text-xs text-gray-500 mt-1">Detail sistem dan kontak dukungan</p>
                </div>
            </div>
            <div class="p-6 md:p-8 relative z-10">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-6">
                    <div class="flex items-start">
                        <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center shrink-0 mr-4">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                        </div>
                        <div>
                            <div class="text-xs font-bold text-gray-400 uppercase tracking-wider">Nama Aplikasi</div>
                            <div class="text-sm font-bold text-gray-800 mt-1">Aplikasi Bank Mini Sekolah</div>
                        </div>
                    </div>

                    <div class="flex items-start">
                        <div class="w-10 h-10 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center shrink-0 mr-4">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"></path></svg>
                        </div>
                        <div>
                            <div class="text-xs font-bold text-gray-400 uppercase tracking-wider">Versi Sistem</div>
                            <div class="text-sm font-bold text-gray-800 mt-1 flex items-center">
                                v1.0.0 <span class="ml-2 px-2 py-0.5 bg-green-100 text-green-700 text-[10px] rounded-full">Stabil</span>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-start">
                        <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0 mr-4">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"></path></svg>
                        </div>
                        <div>
                            <div class="text-xs font-bold text-gray-400 uppercase tracking-wider">Versi Database</div>
                            <div class="text-sm font-bold text-gray-800 mt-1">v1.0.0</div>
                        </div>
                    </div>

                    <div class="flex items-start">
                        <div class="w-10 h-10 rounded-xl bg-green-50 text-green-600 flex items-center justify-center shrink-0 mr-4">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path></svg>
                        </div>
                        <div>
                            <div class="text-xs font-bold text-gray-400 uppercase tracking-wider">WhatsApp Support</div>
                            <div class="text-sm font-bold mt-1">
                                <a href="https://wa.me/6285201166815" target="_blank" class="text-green-600 hover:text-green-800 hover:underline transition-colors">0852-0116-6815</a>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-start">
                        <div class="w-10 h-10 rounded-xl bg-red-50 text-red-600 flex items-center justify-center shrink-0 mr-4">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                        </div>
                        <div>
                            <div class="text-xs font-bold text-gray-400 uppercase tracking-wider">Email Developer</div>
                            <div class="text-sm font-bold mt-1">
                                <a href="mailto:matatech.indonesia@gmail.com" class="text-red-600 hover:text-red-800 hover:underline transition-colors">matatech.indonesia@gmail.com</a>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-start">
                        <div class="w-10 h-10 rounded-xl bg-gray-100 text-gray-700 flex items-center justify-center shrink-0 mr-4">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"></path></svg>
                        </div>
                        <div>
                            <div class="text-xs font-bold text-gray-400 uppercase tracking-wider">Pengembang Utama</div>
                            <div class="text-sm font-bold mt-1">
                                <a href="https://github.com/Aenunakhkam" target="_blank" class="text-gray-800 hover:text-indigo-600 hover:underline transition-colors">Aenun Akhkam (GitHub)</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="p-6 bg-gradient-to-r from-green-50/80 to-blue-50/80 border-t border-green-100/50 text-sm text-center md:text-left text-gray-600 font-medium">
                Aplikasi ini dikembangkan oleh <a href="https://github.com/Aenunakhkam" target="_blank" class="text-[#0f7632] font-black hover:underline transition-colors">AENUN AKHKAM</a> bersama Tim <a href="https://github.com/Aenunakhkam" target="_blank" class="text-[#0f7632] font-black hover:underline transition-colors">MATATECH</a> untuk mempermudah administrasi keuangan sekolah di seluruh Indonesia.
            </div>
        </div>

    </AuthenticatedLayout>
</template>
