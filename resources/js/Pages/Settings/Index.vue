<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import Swal from 'sweetalert2';

const props = defineProps({
    settings: Object
});

const form = useForm({
    admin_fee_active: props.settings.admin_fee_active === '1' || props.settings.admin_fee_active === true || props.settings.admin_fee_active === 'true',
    admin_fee_amount: props.settings.admin_fee_amount || 0,
});

const saveSettings = () => {
    form.post(route('settings.update'), {
        preserveScroll: true,
        onSuccess: () => {
            Swal.fire({
                icon: 'success',
                title: 'Berhasil',
                text: 'Pengaturan berhasil disimpan!',
                timer: 1500,
                showConfirmButton: false
            });
        }
    });
};

const displayAmount = ref(parseInt(props.settings.admin_fee_amount || 0).toLocaleString('id-ID'));

const formatAmount = (e) => {
    let val = e.target.value.replace(/\D/g, '');
    if (!val) val = '0';
    form.admin_fee_amount = parseInt(val, 10);
    displayAmount.value = parseInt(val, 10).toLocaleString('id-ID');
};

const applyAdminFee = () => {
    if (!form.admin_fee_active) {
        Swal.fire('Oops', 'Fitur potongan admin belum diaktifkan!', 'error');
        return;
    }
    if (form.admin_fee_amount <= 0) {
        Swal.fire('Oops', 'Nominal potongan harus lebih dari 0!', 'error');
        return;
    }

    Swal.fire({
        title: 'Terapkan Potongan Admin?',
        text: `Sistem akan memotong Rp${parseInt(form.admin_fee_amount).toLocaleString('id-ID')} dari saldo setiap nasabah aktif untuk bulan ini. Lanjutkan?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Ya, Terapkan Sekarang!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            router.post(route('settings.apply-admin-fee'), {}, {
                preserveScroll: true,
                onSuccess: (page) => {
                    if (page.props.flash.error) {
                        Swal.fire('Gagal!', page.props.flash.error, 'error');
                    } else if (page.props.flash.message) {
                        Swal.fire('Berhasil!', page.props.flash.message, 'success');
                    }
                }
            });
        }
    });
};
</script>

<template>
    <Head title="Pengaturan Bank" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Pengaturan Bank Mini</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                
                <!-- Admin Fee Settings -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6 text-gray-900">
                        <h3 class="text-lg font-bold mb-4 border-b pb-2 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            Pengaturan Potongan Admin Bulanan
                        </h3>
                        
                        <form @submit.prevent="saveSettings" class="max-w-xl">
                            <div class="mb-4 flex items-center">
                                <input type="checkbox" id="admin_fee_active" v-model="form.admin_fee_active" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500 mr-2">
                                <label for="admin_fee_active" class="text-sm font-medium text-gray-700">Aktifkan Fitur Potongan Admin Bulanan</label>
                            </div>

                            <div class="mb-6" v-if="form.admin_fee_active">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Nominal Potongan Admin (Rp)</label>
                                <input type="text" :value="displayAmount" @input="formatAmount" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="Contoh: 2.000">
                                <p class="mt-1 text-xs text-gray-500">Nominal ini akan ditarik dari saldo nasabah saat dieksekusi.</p>
                            </div>

                            <div class="flex items-center space-x-3 mb-6 border-b pb-6">
                                <button type="submit" :disabled="form.processing" class="inline-flex justify-center rounded-md border border-transparent bg-indigo-600 py-2 px-4 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                                    Simpan Pengaturan
                                </button>
                            </div>
                        </form>

                        <div class="mt-4 bg-gray-50 p-4 rounded-lg border border-gray-200">
                            <h4 class="text-md font-bold text-gray-800 mb-2">Eksekusi Potongan Bulanan</h4>
                            <div class="text-sm text-gray-600 mb-4 bg-blue-50 p-3 rounded border border-blue-200">
                                <strong>💡 Cara Kerja Pemotongan:</strong>
                                <ul class="list-disc ml-5 mt-1 space-y-1">
                                    <li>Pemotongan admin dilakukan <strong>satu bulan sekali</strong> dengan menekan tombol di bawah ini.</li>
                                    <li>Sistem hanya akan memotong saldo jika <strong>saldo nasabah cukup</strong> (minimal Rp{{ parseInt(form.admin_fee_amount || 0).toLocaleString('id-ID') }}).</li>
                                    <li>Jika nasabah sudah dipotong pada bulan ini, sistem akan <strong>mencegah pemotongan ganda</strong> (tidak akan terpotong kembali).</li>
                                </ul>
                            </div>
                            <button @click="applyAdminFee" :disabled="!form.admin_fee_active || form.admin_fee_amount <= 0" class="inline-flex justify-center rounded-md border border-transparent bg-red-600 py-2 px-4 text-sm font-medium text-white shadow-sm hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 disabled:bg-red-300 disabled:cursor-not-allowed">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                                Terapkan Potongan Admin Bulan Ini
                            </button>
                        </div>

                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
