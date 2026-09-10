<section id="view-dashboard" class="space-y-6">
    <!-- Header Dashboard & Filter Action -->
    <div class="flex flex-col lg:flex-row justify-between items-start lg:items-end gap-4 mb-2">
        <div>
            <h2 class="text-3xl font-extrabold text-slate-800">Dashboard Keuangan</h2>
            <p class="text-slate-500 text-sm mt-1">Rangkuman keseluruhan arus kas, pemasukan, dan sisa piutang tagihan.
            </p>
        </div>

        <!-- Filter Box (Gaya mirip gambar referensi) -->
        <div
            class="bg-slate-200/50 p-1.5 rounded-xl flex flex-col sm:flex-row items-center gap-2 border border-slate-200">
            <div class="flex items-center bg-white px-3 py-2 rounded-lg border border-slate-200 shadow-sm gap-2">
                <i class="fa-solid fa-calendar-days text-slate-400 text-xs"></i>
                <label class="text-xs font-bold text-slate-500 mr-1">Periode:</label>
                <input type="month" id="dashboard-month-picker" onchange="renderDashboard()"
                    class="text-xs font-extrabold text-slate-800 bg-transparent focus:outline-none cursor-pointer">
            </div>
            <div class="flex gap-2 w-full sm:w-auto">
                <button onclick="resetDashboardPeriodFilter()"
                    class="flex-1 bg-white hover:bg-slate-50 border border-slate-200 text-slate-700 font-bold px-4 py-2 rounded-lg text-xs transition shadow-sm">
                    Semua Periode
                </button>
                <button onclick="openGenerateModal()"
                    class="flex-1 bg-emerald-600 hover:bg-emerald-700 text-white font-bold px-4 py-2 rounded-lg text-xs shadow-md transition flex items-center justify-center gap-2 whitespace-nowrap">
                    <i class="fa-solid fa-plus"></i> Buat Tagihan
                </button>
            </div>
        </div>
    </div>

    <!-- Menampilkan Label Filter Aktif -->
    <div
        class="text-xs font-bold text-slate-500 flex items-center gap-2 bg-blue-50 text-blue-700 w-max px-3 py-1 rounded-md border border-blue-100">
        <i class="fa-solid fa-filter"></i> Menampilkan Laporan: <span id="dashboard-period-label"
            class="font-extrabold">Semua Periode</span>
    </div>

    <!-- Stats Grid (Gaya Clean Card dengan Left Border) -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 md:gap-6">

        <!-- Card 1: Total Pemasukan -->
        <div
            class="bg-white p-5 rounded-xl border border-slate-200 border-l-4 border-l-emerald-500 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider flex items-center gap-1.5">
                    <i class="fa-regular fa-folder"></i> PEMASUKAN DIBAYAR
                </p>
                <h3 class="text-2xl font-black text-emerald-600 mt-1" id="stat-paid-amount">Rp 0</h3>
                <p class="text-xs text-slate-500 mt-1 font-medium"><span id="stat-paid-count"
                        class="font-bold text-slate-700">0</span> Tagihan Telah Lunas</p>
            </div>
            <div class="w-12 h-12 bg-emerald-50 text-emerald-500 rounded-lg flex items-center justify-center text-xl">
                <i class="fa-solid fa-money-bill-wave"></i>
            </div>
        </div>

        <!-- Card 2: Total Tagihan Terbuat -->
        <div
            class="bg-white p-5 rounded-xl border border-slate-200 border-l-4 border-l-blue-500 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider flex items-center gap-1.5">
                    <i class="fa-solid fa-building-columns"></i> TOTAL NILAI TAGIHAN
                </p>
                <h3 class="text-2xl font-black text-blue-600 mt-1" id="stat-total-amount">Rp 0</h3>
                <p class="text-xs text-slate-500 mt-1 font-medium"><span id="stat-total-bills-count"
                        class="font-bold text-slate-700">0</span> Dokumen Tagihan</p>
            </div>
            <div class="w-12 h-12 bg-blue-50 text-blue-500 rounded-lg flex items-center justify-center text-xl">
                <i class="fa-solid fa-credit-card"></i>
            </div>
        </div>

        <!-- Card 3: Sisa Tunggakan -->
        <div
            class="bg-white p-5 rounded-xl border border-slate-200 border-l-4 border-l-red-500 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider flex items-center gap-1.5">
                    <i class="fa-solid fa-clock"></i> PIUTANG BELUM DIBAYAR
                </p>
                <h3 class="text-2xl font-black text-red-600 mt-1" id="stat-unpaid-amount">Rp 0</h3>
                <p class="text-xs text-slate-500 mt-1 font-medium"><span id="stat-unpaid-count"
                        class="font-bold text-slate-700">0</span> Status Belum Lunas</p>
            </div>
            <div class="w-12 h-12 bg-red-50 text-red-500 rounded-lg flex items-center justify-center text-xl">
                <i class="fa-solid fa-hand-holding-dollar"></i>
            </div>
        </div>

        <!-- Card 4: Action Bias -->
        <div class="bg-slate-800 p-5 rounded-xl border border-slate-700 shadow-md flex items-center justify-between cursor-pointer hover:bg-slate-900 transition"
            onclick="switchTab('history')">
            <div>
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider flex items-center gap-1.5">
                    PERHATIAN AUDIT
                </p>
                <h3 class="text-2xl font-black text-white mt-1" id="stat-unbilled-count">0</h3>
                <p class="text-xs text-slate-300 mt-1 font-medium">Pelanggan Belum Ditagih</p>
            </div>
            <div
                class="w-12 h-12 border border-slate-600 text-white rounded-lg flex items-center justify-center text-xl">
                <i class="fa-solid fa-arrow-right-arrow-left"></i>
            </div>
        </div>
    </div>

    <!-- Tabel Unpaid Transaksi Ringkas -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden mt-6">
        <div class="p-5 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
            <div>
                <h3 class="font-extrabold text-slate-800 text-base">Tagihan Membutuhkan Tindakan</h3>
                <p class="text-xs text-slate-500 mt-0.5">Daftar piutang aktif yang belum dibayar lunas oleh pelanggan.
                </p>
            </div>
            <button onclick="switchTab('bills')"
                class="text-xs font-bold text-blue-600 hover:text-blue-700 flex items-center gap-1">
                Lihat Semua <i class="fa-solid fa-arrow-right"></i>
            </button>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-xs sm:text-sm text-left">
                <thead
                    class="bg-white text-slate-400 font-bold text-[10px] uppercase tracking-wider border-b border-slate-200">
                    <tr>
                        <th class="px-5 py-3.5">Pelanggan</th>
                        <th class="px-5 py-3.5">Lokasi</th>
                        <th class="px-5 py-3.5">Total Tagihan</th>
                        <th class="px-5 py-3.5">Sisa Piutang</th>
                        <th class="px-5 py-3.5 text-center">Aksi (Bayar / WA)</th>
                    </tr>
                </thead>
                <tbody id="dashboard-unpaid-tbody" class="divide-y divide-slate-100 bg-white">
                    <!-- Populated dynamically -->
                </tbody>
            </table>
        </div>
    </div>
</section>
