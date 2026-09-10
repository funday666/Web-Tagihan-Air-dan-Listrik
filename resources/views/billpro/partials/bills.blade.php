<section id="view-bills" class="space-y-6 hidden">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h2 class="text-2xl font-extrabold text-slate-900">Kelola Tagihan Bulanan</h2>
            <p class="text-slate-500 text-xs sm:text-sm">Daftar tagihan air, listrik, dan kos beserta pencatatan
                pembayaran.</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <button onclick="openGenerateModal()"
                class="bg-gradient-to-r from-gold-500 to-amber-600 hover:from-gold-600 hover:to-amber-700 text-slate-950 px-4 py-2 rounded-xl text-xs font-extrabold shadow-sm flex items-center gap-2">
                <i class="fa-solid fa-circle-plus"></i> Catat Tagihan Baru
            </button>
        </div>
    </div>

    <div
        class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm flex flex-col md:flex-row gap-4 items-center justify-between">
        <div class="relative w-full md:w-80">
            <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-3 text-slate-400"></i>
            <input type="text" id="bill-search" oninput="renderBillsTable()"
                placeholder="Cari No. Reg, Nama Pelanggan, atau Kamar..."
                class="w-full pl-10 pr-4 py-2 border border-slate-200 rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-gold-500">
        </div>
        <div class="flex items-center gap-3 w-full md:w-auto overflow-x-auto">
            <label class="text-xs font-bold text-slate-500 whitespace-nowrap">Filter Status:</label>
            <select id="bill-status-filter" onchange="renderBillsTable()"
                class="border border-slate-200 rounded-xl px-3 py-2 text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-gold-500">
                <option value="all">Semua Status</option>
                <option value="Belum Lunas">Belum Lunas / Dicicil</option>
                <option value="Lunas">Lunas</option>
            </select>
        </div>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-xs sm:text-sm text-left">
                <thead class="bg-slate-50 text-slate-500 font-bold text-[11px] uppercase tracking-wider">
                    <tr>
                        <th class="px-5 py-3.5">No. Registrasi</th>
                        <th class="px-5 py-3.5">Nama Pelanggan</th>
                        <th class="px-5 py-3.5">Periode</th>
                        <th class="px-5 py-3.5">Air / Listrik</th>
                        <th class="px-5 py-3.5">Total / Dibayar</th>
                        <th class="px-5 py-3.5">Sisa Kewajiban</th>
                        <th class="px-5 py-3.5">Status</th>
                        <th class="px-5 py-3.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody id="bills-tbody" class="divide-y divide-slate-100">
                    <!-- Populated dynamically -->
                </tbody>
            </table>
        </div>
    </div>
</section>
