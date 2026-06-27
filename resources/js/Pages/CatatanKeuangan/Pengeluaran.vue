<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import TextInput from '@/Components/TextInput.vue';

const props = defineProps({
    transactions: Object,
    filters: Object,
});

const searchForm = useForm({
    search: props.filters?.search || ''
});

const onSearch = () => {
    searchForm.get(route('catatan-keuangan.pengeluaran'), { preserveState: true });
};

const formatRupiah = (number) => {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(number);
};

const formatDate = (dateString) => {
    const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit' };
    return new Date(dateString).toLocaleDateString('id-ID', options);
};
</script>

<template>
    <Head title="Catatan Pengeluaran" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-3">
                <div class="p-2 bg-orange-100 rounded-xl shadow-sm">
                    <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6"></path></svg>
                </div>
                <div>
                    <h2 class="font-bold text-xl text-gray-800 leading-tight">Daftar Pengeluaran (Penarikan)</h2>
                    <p class="text-sm text-gray-500 font-medium">Riwayat uang keluar / penarikan oleh nasabah Bank Mini</p>
                </div>
            </div>
        </template>

        <div class="py-8">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                <!-- Data Table Container -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-2xl border border-gray-100">
                    <div class="p-6 border-b border-gray-100 flex flex-col md:flex-row justify-between items-center gap-4">
                        <div class="relative w-full md:w-96">
                            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                            </div>
                            <TextInput 
                                v-model="searchForm.search" 
                                @keyup.enter="onSearch"
                                type="text" 
                                class="w-full pl-10 pr-4 py-2 rounded-xl border-gray-200 focus:ring-orange-500 focus:border-orange-500 bg-gray-50 focus:bg-white" 
                                placeholder="Cari nama atau rekening nasabah..." 
                            />
                        </div>
                        <div class="flex items-center gap-2 mt-4 md:mt-0">
                            <a :href="route('catatan-keuangan.pengeluaran.pdf')" target="_blank" class="px-4 py-2 rounded-xl text-sm font-bold bg-red-500 hover:bg-red-600 text-white shadow transition-all flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                PDF (Landscape)
                            </a>
                            <a :href="route('catatan-keuangan.pengeluaran.excel')" target="_blank" class="px-4 py-2 rounded-xl text-sm font-bold bg-green-500 hover:bg-green-600 text-white shadow transition-all flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                Excel
                            </a>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left text-gray-500">
                            <thead class="text-xs text-gray-500 uppercase tracking-wider bg-gray-50 border-b border-gray-100">
                                <tr>
                                    <th scope="col" class="px-6 py-4 font-bold text-gray-600">Waktu Transaksi</th>
                                    <th scope="col" class="px-6 py-4 font-bold text-gray-600">Nama Nasabah</th>
                                    <th scope="col" class="px-6 py-4 font-bold text-gray-600">No. Rekening</th>
                                    <th scope="col" class="px-6 py-4 text-right font-bold text-gray-600">Nominal Penarikan</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="tx in transactions.data" :key="tx.id" class="bg-white border-b last:border-0 hover:bg-orange-50/50 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 font-medium">
                                        {{ formatDate(tx.created_at) }}
                                    </td>
                                    <td class="px-6 py-4 font-medium text-gray-900">
                                        {{ tx.student?.name }}
                                        <div class="text-xs text-gray-400 font-normal mt-0.5">{{ tx.student?.nasabah_type || 'Siswa' }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 py-1 bg-gray-100 rounded text-xs font-mono font-medium text-gray-600 tracking-wider">
                                            {{ tx.student?.nisn }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right font-bold text-orange-600 text-base">
                                        - {{ formatRupiah(tx.amount) }}
                                    </td>
                                </tr>
                                <tr v-if="transactions.data.length === 0">
                                    <td colspan="4" class="px-6 py-8 text-center text-gray-500 bg-gray-50/50">
                                        Tidak ada data pengeluaran ditemukan.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="p-6 border-t border-gray-100 flex flex-col md:flex-row justify-between items-center gap-4" v-if="transactions.links && transactions.data.length > 0">
                        <span class="text-sm text-gray-500">Menampilkan <span class="font-bold">{{ transactions.from }}</span> sampai <span class="font-bold">{{ transactions.to }}</span> dari <span class="font-bold">{{ transactions.total }}</span> data</span>
                        <div class="flex space-x-1">
                            <template v-for="(link, p) in transactions.links" :key="p">
                                <a 
                                    v-if="link.url"
                                    :href="link.url"
                                    class="px-3 py-1.5 border rounded-md text-sm transition-colors"
                                    :class="link.active ? 'bg-orange-600 text-white border-orange-600' : 'bg-white text-gray-700 hover:bg-gray-50 border-gray-300'"
                                    v-html="link.label"
                                ></a>
                                <span v-else class="px-3 py-1.5 border rounded-md text-sm text-gray-400 bg-gray-50 border-gray-200" v-html="link.label"></span>
                            </template>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
