<section id="view-tenants" class="space-y-6 hidden">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h2 class="text-2xl font-extrabold text-slate-900">Data Pelanggan & Kamar</h2>
            <p class="text-slate-500 text-xs sm:text-sm">Kelola Nomor Registrasi, Nama Pelanggan, dan WA Rujukan
                Notifikasi.</p>
        </div>
        <button onclick="openTenantModal()"
            class="bg-gradient-to-r from-gold-500 to-amber-600 hover:from-gold-600 hover:to-amber-700 text-slate-950 px-4 py-2 rounded-xl text-xs font-extrabold shadow-sm flex items-center gap-2">
            <i class="fa-solid fa-user-plus"></i> Tambah Pelanggan / Kamar
        </button>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5" id="tenants-grid">
        <!-- Dynamic cards populated here -->
    </div>
</section>
