<div id="view-tenants" class="hidden space-y-6 animate-fadeIn">
    <div class="flex justify-between items-end mb-6">
        <div>
            <h2 class="text-2xl font-black text-slate-900 tracking-tight">Data Pelanggan & Kamar</h2>
            <p class="text-sm text-slate-500 mt-1">Kelola Nomor Registrasi, Nama Pelanggan, dan WA Rujukan Notifikasi.
            </p>
        </div>
        <button onclick="openTenantModal()"
            class="bg-slate-900 hover:bg-slate-800 text-white px-4 py-2 rounded-lg text-xs font-bold shadow-sm transition flex items-center gap-1.5">
            <i class="fa-solid fa-user-plus"></i> Tambah Pelanggan / Kamar
        </button>
    </div>

    <!-- Bagian ini sangat penting, ID "tenants-grid" dibaca oleh JS -->
    <div id="tenants-grid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        <!-- Kotak Data Pelanggan akan disuntikkan oleh JavaScript di sini -->
    </div>
</div>
