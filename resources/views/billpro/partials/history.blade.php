<section id="view-history" class="space-y-6 hidden">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h2 class="text-2xl font-extrabold text-slate-900">Rekapan & Audit Pelanggan</h2>
            <p class="text-slate-500 text-xs sm:text-sm">Histori pencatatan, pelanggan yang belum dibuatkan tagihan, dan
                daftar tunggakan kewajiban.</p>
        </div>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-5 space-y-4">
        <div
            class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 border-b border-slate-100 pb-4">
            <div>
                <h3 class="font-bold text-slate-900 text-base sm:text-lg flex items-center gap-2">
                    <i class="fa-solid fa-user-slash text-red-500"></i> Pelanggan Belum Dibuatkan Tagihan
                </h3>
                <p class="text-xs text-slate-500">Pilih periode bulan untuk mengecek pelanggan yang belum dicatat
                    meterannya</p>
            </div>
            <div class="flex items-center gap-2">
                <label class="text-xs font-bold text-slate-600">Periode Audit:</label>
                <input type="month" id="audit-month-picker" onchange="renderHistoryView()"
                    class="border border-slate-200 rounded-xl px-3 py-1.5 text-xs font-bold focus:ring-2 focus:ring-gold-500 focus:outline-none">
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-xs sm:text-sm text-left">
                <thead class="bg-slate-50 text-slate-500 font-bold text-[11px] uppercase tracking-wider">
                    <tr>
                        <th class="px-5 py-3">No. Registrasi</th>
                        <th class="px-5 py-3">Nama Pelanggan</th>
                        <th class="px-5 py-3">Kamar</th>
                        <th class="px-5 py-3">WhatsApp Rujukan</th>
                        <th class="px-5 py-3 text-center">Status Pencatatan</th>
                        <th class="px-5 py-3 text-right">Aksi Cepat</th>
                    </tr>
                </thead>
                <tbody id="unbilled-tenants-tbody" class="divide-y divide-slate-100">
                    <!-- Dynamic -->
                </tbody>
            </table>
        </div>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-5 space-y-4">
        <div class="border-b border-slate-100 pb-3">
            <h3 class="font-bold text-slate-900 text-base sm:text-lg flex items-center gap-2">
                <i class="fa-solid fa-file-invoice text-amber-500"></i> Rekapan Semua Kewajiban Belum Lunas
            </h3>
            <p class="text-xs text-slate-500">Daftar akumulasi seluruh tagihan aktif yang belum dilunasi oleh pelanggan
            </p>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-xs sm:text-sm text-left">
                <thead class="bg-slate-50 text-slate-500 font-bold text-[11px] uppercase tracking-wider">
                    <tr>
                        <th class="px-5 py-3">No. Registrasi</th>
                        <th class="px-5 py-3">Pelanggan & Kamar</th>
                        <th class="px-5 py-3">Periode Tagihan</th>
                        <th class="px-5 py-3">Rincian Komponen</th>
                        <th class="px-5 py-3">Sisa Tunggakan</th>
                        <th class="px-5 py-3">Jatuh Tempo</th>
                        <th class="px-5 py-3 text-center">Input Bayar / WA</th>
                    </tr>
                </thead>
                <tbody id="history-unpaid-tbody" class="divide-y divide-slate-100">
                    <!-- Dynamic -->
                </tbody>
            </table>
        </div>
    </div>
</section>
