<div id="view-dashboard" class="space-y-6 animate-fadeIn">
    <!-- Header & Controls -->
    <div class="flex flex-col md:flex-row justify-between md:items-end gap-4">
        <div>
            <h2 class="text-2xl font-black text-slate-900 tracking-tight">Dashboard Keuangan</h2>
            <p class="text-sm text-slate-500 mt-1">Rangkuman keseluruhan arus kas, pemasukan, dan sisa piutang tagihan.
            </p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <div
                class="flex items-center border border-slate-200 bg-white rounded-lg px-3 py-1.5 text-xs font-bold text-slate-600 shadow-sm">
                <i class="fa-regular fa-calendar mr-2"></i> Periode:
                <input type="month" id="dash-period-input" onchange="renderDashboard()"
                    class="ml-2 bg-transparent outline-none cursor-pointer">
            </div>
            <button onclick="resetDashboardPeriod()"
                class="bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 px-4 py-2 rounded-lg text-xs font-bold shadow-sm transition">
                Semua Periode
            </button>
            <button onclick="openGenerateModal()"
                class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-lg text-xs font-bold shadow-sm transition flex items-center gap-1.5">
                <i class="fa-solid fa-plus"></i> Buat Tagihan
            </button>
        </div>
    </div>

    <!-- Filter Alert -->
    <div
        class="inline-flex items-center gap-2 px-3 py-1.5 bg-blue-50 text-blue-700 text-xs font-bold rounded-lg border border-blue-100">
        <i class="fa-solid fa-filter"></i> Menampilkan Laporan: <span id="dash-period-label">Semua Periode</span>
    </div>

    <!-- Metric Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Pemasukan -->
        <div
            class="bg-white border border-slate-200 border-l-4 border-l-emerald-500 rounded-xl p-5 shadow-sm flex justify-between items-center transition hover:shadow-md">
            <div>
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-wider mb-1 flex items-center gap-1">
                    <i class="fa-regular fa-file-lines"></i> Pemasukan Dibayar</p>
                <h4 class="text-2xl font-black text-emerald-600" id="dash-income-total">Rp 0</h4>
                <p class="text-xs text-slate-500 font-medium mt-1" id="dash-income-count">0 Tagihan Telah Lunas</p>
            </div>
            <div class="w-10 h-10 rounded-full bg-emerald-50 flex items-center justify-center text-emerald-500 text-lg">
                <i class="fa-solid fa-money-bill-wave"></i>
            </div>
        </div>

        <!-- Total Tagihan -->
        <div
            class="bg-white border border-slate-200 border-l-4 border-l-blue-500 rounded-xl p-5 shadow-sm flex justify-between items-center transition hover:shadow-md">
            <div>
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-wider mb-1 flex items-center gap-1">
                    <i class="fa-solid fa-building-columns"></i> Total Nilai Tagihan</p>
                <h4 class="text-2xl font-black text-blue-600" id="dash-bill-total">Rp 0</h4>
                <p class="text-xs text-slate-500 font-medium mt-1" id="dash-bill-count">0 Dokumen Tagihan</p>
            </div>
            <div class="w-10 h-10 rounded-full bg-blue-50 flex items-center justify-center text-blue-500 text-lg">
                <i class="fa-solid fa-credit-card"></i>
            </div>
        </div>

        <!-- Piutang -->
        <div
            class="bg-white border border-slate-200 border-l-4 border-l-red-500 rounded-xl p-5 shadow-sm flex justify-between items-center transition hover:shadow-md">
            <div>
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-wider mb-1 flex items-center gap-1">
                    <i class="fa-solid fa-clock"></i> Piutang Belum Dibayar</p>
                <h4 class="text-2xl font-black text-red-600" id="dash-unpaid-total">Rp 0</h4>
                <p class="text-xs text-slate-500 font-medium mt-1" id="dash-unpaid-count">0 Status Belum Lunas</p>
            </div>
            <div class="w-10 h-10 rounded-full bg-red-50 flex items-center justify-center text-red-500 text-lg">
                <i class="fa-solid fa-hand-holding-dollar"></i>
            </div>
        </div>

        <!-- Audit -->
        <div
            class="bg-slate-900 border border-slate-800 rounded-xl p-5 shadow-sm flex justify-between items-center text-white transition hover:shadow-md">
            <div>
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-wider mb-1">Perhatian Audit</p>
                <h4 class="text-3xl font-black" id="dash-audit-count">0</h4>
                <p class="text-xs text-slate-400 font-medium mt-1">Pelanggan Belum Ditagih</p>
            </div>
            <div class="w-10 h-10 rounded-full bg-slate-800 flex items-center justify-center text-slate-300 text-lg">
                <i class="fa-solid fa-arrow-right-arrow-left"></i>
            </div>
        </div>
    </div>

    <!-- Action Table -->
    <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden mt-6">
        <div class="p-5 border-b border-slate-100 flex justify-between items-center">
            <div>
                <h3 class="font-bold text-slate-800 text-base">Tagihan Membutuhkan Tindakan</h3>
                <p class="text-xs text-slate-500 mt-0.5">Daftar piutang aktif yang belum dibayar lunas oleh pelanggan.
                </p>
            </div>
            <button onclick="switchTab('history')" class="text-xs font-bold text-blue-600 hover:text-blue-700">Lihat
                Semua <i class="fa-solid fa-arrow-right ml-1"></i></button>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm whitespace-nowrap">
                <thead
                    class="bg-slate-50 text-slate-500 text-[10px] uppercase font-black tracking-wider border-b border-slate-100">
                    <tr>
                        <th class="p-4">Pelanggan & No. Reg</th>
                        <th class="p-4">Periode</th>
                        <th class="p-4">Total Tagihan</th>
                        <th class="p-4">Sisa Piutang</th>
                        <th class="p-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody id="dash-action-tbody" class="divide-y divide-slate-100">
                    <!-- Disuntikkan JS -->
                </tbody>
            </table>
        </div>
    </div>
</div>
