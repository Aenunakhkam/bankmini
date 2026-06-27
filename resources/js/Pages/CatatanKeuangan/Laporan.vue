<script setup>
import { ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Modal from '@/Components/Modal.vue';
import TextInput from '@/Components/TextInput.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';

const props = defineProps({
    totalPemasukan: Number,
    totalPengeluaran: Number,
    selisih: Number,
    cycleLabel: String,
    startDate: String,
    endDate: String,
    currentCycle: String,
});

const isCustomCycleModalOpen = ref(false);
const customStartDate = ref(props.startDate);
const customEndDate = ref(props.endDate);

const changeCycle = (cycle) => {
    router.get(route('catatan-keuangan.laporan'), { cycle: cycle }, { preserveState: true });
};

const openCustomCycleModal = () => {
    customStartDate.value = props.startDate;
    customEndDate.value = props.endDate;
    isCustomCycleModalOpen.value = true;
};

const applyCustomCycle = () => {
    isCustomCycleModalOpen.value = false;
    router.get(route('catatan-keuangan.laporan'), {
        cycle: 'custom',
        start_date: customStartDate.value,
        end_date: customEndDate.value
    }, { preserveState: true });
};

const formatRupiah = (number) => {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(number);
};
</script>

<template>
    <Head title="Laporan Keuangan" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-3">
                <div class="p-2 bg-blue-100 rounded-xl shadow-sm">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                </div>
                <div>
                    <h2 class="font-bold text-xl text-gray-800 leading-tight">Laporan Keuangan Bank Mini</h2>
                    <p class="text-sm text-gray-500 font-medium">Ringkasan mutasi dan arus kas</p>
                </div>
            </div>
        </template>

        <div class="py-8">
            <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">
                
                <!-- Filter Section (Ala Brimo) -->
                <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100 flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div>
                        <p class="text-sm text-gray-500 font-medium mb-1">Siklus Laporan Saat Ini</p>
                        <h3 class="text-lg font-bold text-gray-800">{{ cycleLabel }}</h3>
                    </div>
                    
                    <div class="flex flex-wrap items-center gap-2">
                        <button 
                            @click="changeCycle('this_month')" 
                            :class="currentCycle === 'this_month' || !currentCycle ? 'bg-blue-600 text-white shadow-md' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'"
                            class="px-4 py-2 rounded-xl text-sm font-bold transition-all"
                        >Bulan Ini</button>
                        
                        <button 
                            @click="changeCycle('last_month')" 
                            :class="currentCycle === 'last_month' ? 'bg-blue-600 text-white shadow-md' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'"
                            class="px-4 py-2 rounded-xl text-sm font-bold transition-all"
                        >Bulan Lalu</button>
                        
                        <button 
                            @click="changeCycle('last_3_months')" 
                            :class="currentCycle === 'last_3_months' ? 'bg-blue-600 text-white shadow-md' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'"
                            class="px-4 py-2 rounded-xl text-sm font-bold transition-all"
                        >3 Bulan Terakhir</button>
                        
                        <button 
                            @click="openCustomCycleModal" 
                            :class="currentCycle === 'custom' ? 'bg-[#0f7632] text-white shadow-md' : 'bg-white border-2 border-[#0f7632] text-[#0f7632] hover:bg-green-50'"
                            class="px-4 py-2 rounded-xl text-sm font-bold transition-all flex items-center gap-2 md:ml-2"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                            Ubah Siklus
                        </button>
                        
                        <div class="flex items-center gap-2 ml-auto">
                            <a :href="route('catatan-keuangan.laporan.pdf', { cycle: currentCycle, start_date: startDate, end_date: endDate })" target="_blank" class="px-4 py-2 rounded-xl text-sm font-bold bg-red-500 hover:bg-red-600 text-white shadow transition-all flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                PDF (Landscape)
                            </a>
                            <a :href="route('catatan-keuangan.laporan.excel', { cycle: currentCycle, start_date: startDate, end_date: endDate })" target="_blank" class="px-4 py-2 rounded-xl text-sm font-bold bg-green-500 hover:bg-green-600 text-white shadow transition-all flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                Excel
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Summary Cards -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Pemasukan -->
                    <div class="bg-gradient-to-br from-green-50 to-emerald-100 p-6 rounded-2xl shadow-sm border border-green-200 relative overflow-hidden">
                        <div class="absolute -right-4 -top-4 opacity-20">
                            <svg class="w-32 h-32 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                        </div>
                        <div class="relative z-10">
                            <p class="text-sm font-bold text-green-800 uppercase tracking-wider mb-2">Total Pemasukan</p>
                            <h3 class="text-3xl font-black text-green-700">{{ formatRupiah(totalPemasukan) }}</h3>
                            <p class="text-xs text-green-600 mt-2 font-medium">Dana masuk dari setoran tunai nasabah.</p>
                        </div>
                    </div>

                    <!-- Pengeluaran -->
                    <div class="bg-gradient-to-br from-orange-50 to-red-100 p-6 rounded-2xl shadow-sm border border-orange-200 relative overflow-hidden">
                        <div class="absolute -right-4 -top-4 opacity-20">
                            <svg class="w-32 h-32 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6"></path></svg>
                        </div>
                        <div class="relative z-10">
                            <p class="text-sm font-bold text-orange-800 uppercase tracking-wider mb-2">Total Pengeluaran</p>
                            <h3 class="text-3xl font-black text-orange-700">{{ formatRupiah(totalPengeluaran) }}</h3>
                            <p class="text-xs text-orange-600 mt-2 font-medium">Dana keluar dari penarikan tunai nasabah.</p>
                        </div>
                    </div>

                    <!-- Selisih -->
                    <div class="bg-gradient-to-br from-blue-50 to-indigo-100 p-6 rounded-2xl shadow-sm border border-blue-200 relative overflow-hidden">
                        <div class="absolute -right-4 -top-4 opacity-20">
                            <svg class="w-32 h-32 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"></path></svg>
                        </div>
                        <div class="relative z-10">
                            <p class="text-sm font-bold text-blue-800 uppercase tracking-wider mb-2">Selisih (Arus Kas)</p>
                            <h3 class="text-3xl font-black" :class="selisih >= 0 ? 'text-blue-700' : 'text-red-600'">
                                {{ selisih >= 0 ? '+' : '' }}{{ formatRupiah(selisih) }}
                            </h3>
                            <p class="text-xs text-blue-600 mt-2 font-medium">Kalkulasi kas pada periode terpilih.</p>
                        </div>
                    </div>
                </div>

                <div class="bg-blue-50 border border-blue-200 rounded-2xl p-5 text-sm text-blue-800 flex items-start gap-3">
                    <svg class="w-5 h-5 text-blue-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <p>Laporan ini dikalkulasi berdasarkan transaksi yang berhasil dicatat oleh sistem pada rentang waktu <strong>{{ cycleLabel }}</strong>. Selisih positif menandakan pertumbuhan saldo kas yang disimpan di Bank Mini.</p>
                </div>

            </div>
        </div>

        <!-- Modal Custom Date -->
        <Modal :show="isCustomCycleModalOpen" @close="isCustomCycleModalOpen = false">
            <div class="bg-gradient-to-b from-green-50/50 to-white px-6 py-5 border-b border-gray-100">
                <h2 class="text-xl font-bold text-[#0f7632] flex items-center">
                    <svg class="w-6 h-6 mr-2 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                    Tentukan Siklus Kustom
                </h2>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                    <div>
                        <InputLabel for="start_date" value="Dari Tanggal" />
                        <TextInput id="start_date" v-model="customStartDate" type="date" class="mt-1 block w-full" />
                    </div>
                    <div>
                        <InputLabel for="end_date" value="Sampai Tanggal" />
                        <TextInput id="end_date" v-model="customEndDate" type="date" class="mt-1 block w-full" />
                    </div>
                </div>

                <div class="flex justify-end space-x-3 pt-4 border-t border-gray-100">
                    <SecondaryButton @click="isCustomCycleModalOpen = false">Batal</SecondaryButton>
                    <PrimaryButton @click="applyCustomCycle">Terapkan Filter</PrimaryButton>
                </div>
            </div>
        </Modal>

    </AuthenticatedLayout>
</template>
