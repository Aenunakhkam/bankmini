<script setup>
import { ref, computed } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import Swal from 'sweetalert2';
import axios from 'axios';

const props = defineProps({
    transactions: Object,
    filters: Object,
});

const searchForm = useForm({
    search: props.filters?.search || '',
    per_page: props.filters?.per_page || '10'
});

const onSearch = () => {
    searchForm.get(route('transactions.index'), { preserveState: true });
};

const formatRupiah = (value) => {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(value);
};

const formatDate = (dateString) => {
    const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit' };
    return new Date(dateString).toLocaleDateString('id-ID', options);
};

const deleteTransaction = (tx) => {
    Swal.fire({
        title: 'Hapus Transaksi Permanen?',
        text: `Apakah Anda yakin ingin menghapus transaksi ${tx.transaction_number} secara permanen? Saldo akan dikembalikan secara otomatis.`,
        icon: 'error',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Ya, Hapus Permanen!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            axios.delete(route('teller.destroy', tx.id))
                .then(response => {
                    Swal.fire('Berhasil!', response.data.message, 'success');
                    router.reload({ only: ['transactions'] });
                })
                .catch(error => {
                    const message = error.response?.data?.error || 'Terjadi kesalahan sistem';
                    Swal.fire('Gagal!', message, 'error');
                });
        }
    });
};

const voidTransaction = (tx) => {
    Swal.fire({
        title: 'Batalkan Transaksi?',
        text: `Apakah Anda yakin ingin membatalkan transaksi ${tx.transaction_number}? Sistem akan membuat Jurnal Pembalik otomatis.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#f59e0b',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Ya, Void Transaksi!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            axios.post(route('teller.void', tx.id))
                .then(response => {
                    Swal.fire('Berhasil!', response.data.message, 'success');
                    router.reload({ only: ['transactions'] });
                })
                .catch(error => {
                    const message = error.response?.data?.error || 'Terjadi kesalahan sistem';
                    Swal.fire('Gagal!', message, 'error');
                });
        }
    });
};

const editTransaction = async (tx) => {
    const { value: newAmount } = await Swal.fire({
        title: 'Edit Nominal Transaksi',
        input: 'number',
        inputLabel: 'Masukkan nominal baru yang benar:',
        inputValue: tx.amount,
        showCancelButton: true,
        inputValidator: (value) => {
            if (!value || value < 1000) {
                return 'Nominal tidak boleh kurang dari Rp 1.000!';
            }
        }
    });

    if (newAmount) {
        axios.put(route('teller.update', tx.id), { amount: newAmount })
            .then(response => {
                Swal.fire('Berhasil!', response.data.message, 'success');
                router.reload({ only: ['transactions'] });
            })
            .catch(error => {
                const message = error.response?.data?.error || 'Terjadi kesalahan sistem';
                Swal.fire('Gagal!', message, 'error');
            });
    }
};

const paginationLinks = computed(() => {
    if (!props.transactions.links) return [];
    return props.transactions.links;
});
</script>

<template>
    <Head title="Riwayat Seluruh Transaksi" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Riwayat Seluruh Transaksi</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-[0_8px_30px_rgb(0,0,0,0.04)] sm:rounded-2xl border border-gray-100">
                    <div class="p-6 md:px-8 border-b border-gray-100 flex flex-col md:flex-row justify-between items-center gap-4">
                        <div class="flex items-center space-x-3 w-full md:w-auto">
                            <span class="text-sm font-medium text-gray-500">Tampilkan</span>
                            <select v-model="searchForm.per_page" @change="onSearch" class="border-gray-200 rounded-lg text-sm text-gray-700 focus:ring-[#0f7632] focus:border-[#0f7632]">
                                <option value="10">10</option>
                                <option value="25">25</option>
                                <option value="50">50</option>
                                <option value="100">100</option>
                                <option value="all">Semua</option>
                            </select>
                            <span class="text-sm font-medium text-gray-500">data</span>
                        </div>
                        
                        <div class="w-full md:w-96 flex gap-2">
                            <div class="relative flex-1">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                                </div>
                                <input 
                                    v-model="searchForm.search" 
                                    @keyup.enter="onSearch"
                                    type="text" 
                                    class="block w-full pl-10 pr-3 py-2 border border-gray-200 rounded-xl leading-5 bg-gray-50 placeholder-gray-400 focus:outline-none focus:bg-white focus:ring-2 focus:ring-[#0f7632]/20 focus:border-[#0f7632] sm:text-sm transition-colors"
                                    placeholder="Cari no referensi, NISN, atau nama..."
                                >
                            </div>
                            <button @click="onSearch" class="px-4 py-2 bg-gray-800 text-white font-medium rounded-xl hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-900 transition-colors">
                                Cari
                            </button>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Tanggal & Referensi</th>
                                    <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Nasabah</th>
                                    <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Jenis</th>
                                    <th class="px-6 py-4 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Nominal</th>
                                    <th class="px-6 py-4 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Saldo Terakhir</th>
                                    <th class="px-6 py-4 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-100">
                                <tr v-for="trx in transactions.data" :key="trx.id" class="hover:bg-gray-50/50 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">{{ formatDate(trx.created_at) }}</div>
                                        <div class="text-xs text-gray-500 font-mono mt-1">{{ trx.transaction_number }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="h-8 w-8 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center font-bold text-xs mr-3">
                                                {{ trx.student?.name?.charAt(0) || '?' }}
                                            </div>
                                            <div>
                                                <div class="text-sm font-bold text-gray-900">{{ trx.student?.name || 'Nasabah Tidak Ditemukan' }}</div>
                                                <div class="text-xs text-gray-500">{{ trx.student?.nisn || '-' }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex flex-col gap-1 items-start">
                                            <span :class="trx.type === 'deposit' ? 'bg-green-100 text-green-800 border-green-200' : 'bg-orange-100 text-orange-800 border-orange-200'" class="px-2.5 py-0.5 inline-flex text-xs leading-5 font-bold rounded-full border">
                                                {{ trx.type === 'deposit' ? 'Setoran' : 'Penarikan' }}
                                            </span>
                                            <span v-if="trx.is_voided" class="bg-red-100 text-red-800 border-red-200 px-2 py-0.5 inline-flex text-[10px] leading-4 font-bold rounded-full border">
                                                DIBATALKAN
                                            </span>
                                            <span v-if="trx.description?.startsWith('Koreksi Pembatalan')" class="bg-gray-100 text-gray-800 border-gray-200 px-2 py-0.5 inline-flex text-[10px] leading-4 font-bold rounded-full border">
                                                JURNAL KOREKSI
                                            </span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-right" :class="[trx.type === 'deposit' ? 'text-green-600' : 'text-orange-600', trx.is_voided ? 'line-through opacity-50' : '']">
                                        {{ trx.type === 'deposit' ? '+' : '-' }} {{ formatRupiah(trx.amount) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900 text-right">
                                        {{ formatRupiah(trx.balance_after) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center space-x-1">
                                        <button @click="editTransaction(trx)" class="inline-flex items-center text-xs font-bold text-gray-500 bg-gray-50 hover:bg-blue-100 hover:text-blue-600 p-2 rounded-lg border border-gray-200 hover:border-blue-200 transition-colors" title="Edit Nominal">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                        </button>
                                        <button @click="deleteTransaction(trx)" class="inline-flex items-center text-xs font-bold text-gray-500 bg-gray-50 hover:bg-red-100 hover:text-red-600 p-2 rounded-lg border border-gray-200 hover:border-red-200 transition-colors" title="Hapus Permanen">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                        </button>
                                        <button v-if="!trx.description?.startsWith('Koreksi Pembatalan') && !trx.is_voided" @click="voidTransaction(trx)" class="inline-flex items-center text-xs font-bold text-orange-600 bg-orange-50 hover:bg-orange-100 p-2 rounded-lg border border-orange-200 transition-colors" title="Jurnal Pembalik (Void)">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"></path></svg>
                                        </button>
                                        <a :href="route('teller.receipt', trx.id)" target="_blank" class="inline-flex items-center text-xs font-bold text-[#005fb8] bg-blue-50 hover:bg-blue-100 p-2 rounded-lg border border-blue-200 transition-colors" title="Cetak Struk">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                                        </a>
                                    </td>
                                </tr>
                                <tr v-if="transactions.data.length === 0">
                                    <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                                        <svg class="w-12 h-12 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path></svg>
                                        Tidak ada data transaksi yang ditemukan.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div v-if="transactions.links && transactions.links.length > 3" class="px-6 py-4 border-t border-gray-100 bg-gray-50 flex items-center justify-between">
                        <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                            <div>
                                <p class="text-sm text-gray-700">
                                    Menampilkan <span class="font-medium">{{ transactions.from || 0 }}</span> sampai <span class="font-medium">{{ transactions.to || 0 }}</span> dari <span class="font-medium">{{ transactions.total }}</span> hasil
                                </p>
                            </div>
                            <div>
                                <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                                    <template v-for="(link, k) in paginationLinks" :key="k">
                                        <div v-if="link.url === null" class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-500 cursor-not-allowed" v-html="link.label"></div>
                                        <Link v-else :href="link.url" class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium hover:bg-gray-50" :class="{'z-10 bg-green-50 border-green-500 text-green-600': link.active, 'text-gray-700': !link.active}" v-html="link.label"></Link>
                                    </template>
                                </nav>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
