<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
</script>

<template>
    <GuestLayout>
        <Head title="Lupa Kata Sandi" />

        <div class="mb-6">
            <h2 class="text-3xl font-extrabold text-[#0f7632] tracking-tight mb-2">Lupa Kata Sandi?</h2>
            <p class="text-[15px] text-gray-500 font-medium">Bantuan Pemulihan Akun BANKMINI</p>
        </div>

        <div class="mb-6 p-5 bg-blue-50 border border-blue-100 rounded-2xl flex flex-col items-center text-center">
            <div class="w-16 h-16 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center mb-4">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
            <h3 class="text-lg font-bold text-blue-900 mb-2">Panduan Pemulihan Akun</h3>
            <div class="text-sm text-blue-700 leading-relaxed text-left inline-block w-full">
                <p class="mb-3 text-center">Karena aplikasi Bankmini berjalan secara lokal (offline), pemulihan kata sandi Administrator dilakukan melalui perangkat server.</p>
                <div class="bg-blue-100/50 p-4 rounded-xl border border-blue-200">
                    <p class="font-bold text-blue-900 mb-2">Langkah-langkah Reset Sandi (Khusus Admin):</p>
                    <ol class="list-decimal list-inside space-y-1.5 text-blue-800">
                        <li>Buka folder instalasi Bankmini (misalnya <code class="font-bold bg-white px-1.5 py-0.5 rounded text-blue-900 shadow-sm border border-blue-100">C:\Bankmini\</code> atau <code class="font-bold bg-white px-1.5 py-0.5 rounded text-blue-900 shadow-sm border border-blue-100">D:\program\Bankmini\</code>) di komputer server.</li>
                        <li>Cari dan klik dua kali file <code class="font-bold bg-white px-1.5 py-0.5 rounded text-blue-900 shadow-sm border border-blue-100">Reset-Password-Admin.bat</code>.</li>
                        <li>Tekan <kbd class="font-bold bg-white px-1.5 py-0.5 rounded text-blue-900 shadow-sm border border-blue-100">Enter</kbd> pada jendela hitam yang muncul.</li>
                        <li>Setelah sukses, kata sandi Anda akan kembali menjadi: <code class="font-bold bg-white px-1.5 py-0.5 rounded text-green-700 shadow-sm border border-green-100">password</code></li>
                    </ol>
                </div>
            </div>
        </div>

        <div class="pt-2">
            <Link
                :href="route('login')"
                class="w-full flex justify-center items-center py-3.5 px-4 rounded-xl shadow-sm text-[15px] font-bold text-gray-700 bg-white border border-gray-200 hover:bg-gray-50 hover:text-[#0f7632] transition-all duration-300"
            >
                Kembali ke Halaman Masuk
            </Link>
        </div>
    </GuestLayout>
</template>
