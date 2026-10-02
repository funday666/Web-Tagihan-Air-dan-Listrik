<div id="view-history" class="hidden space-y-6 animate-fadeIn">
    <div class="mb-6">
        <h2 class="text-2xl font-black text-slate-900 tracking-tight">Rekapan & Audit Pelanggan</h2>
        <p class="text-sm text-slate-500 mt-1">Histori pencatatan, pelanggan yang belum dibuatkan tagihan, dan daftar
            tunggakan kewajiban.</p>
    </div>

    <!-- Section 1: Belum Dibuatkan Tagihan -->
    <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex flex-col md:flex-row justify-between items-center gap-4">
            <div>
                <h3 class="font-bold text-slate-800 text-lg flex items-center gap-2">
                    <i class="fa-solid fa-user-xmark text-red-500"></i> Pelanggan Belum Dibuatkan Tagihan
                </h3>
                <p class="text-xs text-slate-500 mt-0.5">Pilih periode bulan untuk mengecek pelanggan yang belum dicatat
                    meterannya</p>
            </div>
            <div class="flex items-center gap-3">
                <label class="text-xs font-bold text-slate-600">Periode Audit:</label>
                <input type="month" id="audit-period-input" onchange="renderUnbilledTenants()"
                    class="px-3 py-2 border border-slate-200 rounded-xl text-xs font-bold focus:ring-2 focus:ring-blue-500 outline-none cursor-pointer">
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm whitespace-nowrap">
                <thead class="bg-slate-50 text-slate-500 text-[10px] uppercase font-black tracking-wider">
                    <tr>
                        <th class="p-4">No. Registrasi</th>
                        <th class="p-4">Nama Pelanggan</th>
                        <th class="p-4">Kamar</th>
                        <th class="p-4">WhatsApp Rujukan</th>
                        <th class="p-4">Status Pencatatan</th>
                        <th class="p-4 text-right">Aksi Cepat</th>
                    </tr>
                </thead>
                <tbody id="unbilled-tenants-tbody" class="divide-y divide-slate-100">
                    <!-- Data disuntikkan oleh JavaScript -->
                </tbody>
            </table>
        </div>
    </div>

    <!-- Section 2: Kewajiban Belum Lunas -->
    <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden mt-6">
        <div class="p-5 border-b border-slate-100">
            <h3 class="font-bold text-slate-800 text-lg flex items-center gap-2">
                <i class="fa-solid fa-file-invoice text-amber-500"></i> Rekapan Semua Kewajiban Belum Lunas
            </h3>
            <p class="text-xs text-slate-500 mt-0.5">Daftar akumulasi seluruh tagihan aktif yang belum dilunasi oleh
                pelanggan</p>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm whitespace-nowrap">
                <thead class="bg-slate-50 text-slate-500 text-[10px] uppercase font-black tracking-wider">
                    <tr>
                        <th class="p-4">No. Registrasi</th>
                        <th class="p-4">Pelanggan & Kamar</th>
                        <th class="p-4">Periode Tagihan</th>
                        <th class="p-4">Rincian Komponen</th>
                        <th class="p-4">Sisa Tunggakan</th>
                        <th class="p-4 text-right">Input Bayar / WA</th>
                    </tr>
                </thead>
                <tbody id="unpaid-bills-tbody" class="divide-y divide-slate-100">
                    <!-- Data disuntikkan oleh JavaScript -->
                </tbody>
            </table>
        </div>
    </div>
</div>
