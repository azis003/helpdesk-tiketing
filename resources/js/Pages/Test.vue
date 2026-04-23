<script setup>
import Swal from 'sweetalert2'

const showAlert = () => {
    Swal.fire({
        title: 'Berhasil!',
        text: 'SweetAlert2 berfungsi dengan baik.',
        icon: 'success',
        confirmButtonText: 'OK'
    })
}
</script>

<template>
    <div class="flex items-center justify-center min-h-screen bg-gray-100">
        <div class="text-center">
            <h1 class="text-4xl font-bold text-blue-600 mb-4">
                Hello Inertia — Helpdesk Tiketing
            </h1>
            <p class="text-lg text-gray-600 mb-6">
                Tailwind CSS v4 berhasil terkonfigurasi! ✅
            </p>
            <button
                @click="showAlert"
                class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition"
            >
                Test SweetAlert2
            </button>
        </div>
    </div>
</template>
