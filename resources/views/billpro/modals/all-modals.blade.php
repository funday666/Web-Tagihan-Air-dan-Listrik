<!-- MODAL 1: CREATE / EDIT BILL -->
<div id="modal-bill" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-xl w-full max-h-[90vh] overflow-y-auto shadow-2xl border border-slate-100 p-6 space-y-4">
        <div class="flex justify-between items-center border-b border-slate-100 pb-3">
            <h3 class="font-bold text-slate-900 text-lg" id="modal-bill-title">Catat Tagihan Bulanan</h3>
            <button onclick="closeModal('modal-bill')" class="text-slate-400 hover:text-slate-600 text-xl font-bold">&times;</button>
        </div>
        <form id="form-bill" onsubmit="handleSaveBill(event)" class="space-y-4">
            <input type="hidden" id="bill-id">
            
            <!-- FORM SEWA KAMAR DISEMBUNYIKAN AGAR JS TETAP BERJALAN -->
            <input type="hidden" id="bill-room-rent" value="0">

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-600 mb-1">Pilih Pelanggan / No. Reg *</label>
                    <select id="bill-tenant-id" onchange="onBillTenantSelect()" required class="w-full px-3 py-2 border border-slate-200 rounded-xl text-xs font-semibold focus:ring-2 focus:ring-blue-500 focus:outline-none">
                        <option value="">-- Pilih Pelanggan --</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-600 mb-1">Bulan & Tahun Tagihan *</label>
                    <input type="month" id="bill-month-picker" onchange="onBillPeriodChange()" required class="w-full px-3 py-2 border border-slate-200 rounded-xl text-xs font-semibold focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    <input type="hidden" id="bill-period">
                </div>
            </div>

            <div id="selected-tenant-info" class="hidden bg-blue-50/50 p-3 rounded-xl border border-blue-200/60 text-xs flex justify-between items-center">
                <div>
                    <span class="font-bold text-slate-800" id="bill-info-noreg">REG-000</span>
                    <span class="text-slate-500 ml-2" id="bill-info-room">(Kamar -)</span>
                </div>
                <div class="text-right">
                    <span class="text-slate-400">WA Rujukan:</span>
                    <span class="font-semibold text-emerald-700 ml-1" id="bill-info-wa">-</span>
                </div>
            </div>

            <!-- Air Calculator -->
            <div class="bg-blue-50/50 p-4 rounded-xl border border-blue-100 space-y-3">
                <div class="flex justify-between items-center">
                    <span class="font-bold text-xs text-blue-900 uppercase tracking-wider flex items-center gap-1.5">
                        <i class="fa-solid fa-droplet text-blue-500"></i> Meteran Air (m³)
                    </span>
                    <span class="text-xs text-blue-700 font-bold" id="water-subtotal-txt">Subtotal: Rp 0</span>
                </div>
                <div class="grid grid-cols-3 gap-2">
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-500">Awal (Otomatis)</label>
                        <input type="number" id="bill-water-prev" step="0.1" value="0" readonly class="w-full px-2.5 py-1.5 border border-slate-200 rounded-lg text-xs bg-slate-100 font-bold text-slate-600 cursor-not-allowed">
                    </div>
                    <div>
                        <label class="block text-[11px] font-extrabold text-slate-800">Akhir (Isi ini) *</label>
                        <input type="number" id="bill-water-curr" step="0.1" value="0" oninput="calculateBillTotals()" class="w-full px-2.5 py-1.5 border border-blue-300 focus:ring-2 focus:ring-blue-400 rounded-lg text-xs bg-white font-black text-blue-900">
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-500">Tarif / m³</label>
                        <input type="number" id="bill-water-price" value="5000" oninput="calculateBillTotals()" class="w-full px-2.5 py-1.5 border border-slate-200 rounded-lg text-xs bg-white font-bold">
                    </div>
                </div>
            </div>

            <!-- Listrik Calculator -->
            <div class="bg-amber-50/50 p-4 rounded-xl border border-amber-100 space-y-3">
                <div class="flex justify-between items-center">
                    <span class="font-bold text-xs text-amber-900 uppercase tracking-wider flex items-center gap-1.5">
                        <i class="fa-solid fa-bolt text-amber-500"></i> Meteran Listrik (kWh)
                    </span>
                    <span class="text-xs text-amber-700 font-bold" id="elec-subtotal-txt">Subtotal: Rp 0</span>
                </div>
                <div class="grid grid-cols-3 gap-2">
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-500">Awal (Otomatis)</label>
                        <input type="number" id="bill-elec-prev" step="0.1" value="0" readonly class="w-full px-2.5 py-1.5 border border-slate-200 rounded-lg text-xs bg-slate-100 font-bold text-slate-600 cursor-not-allowed">
                    </div>
                    <div>
                        <label class="block text-[11px] font-extrabold text-slate-800">Akhir (Isi ini) *</label>
                        <input type="number" id="bill-elec-curr" step="0.1" value="0" oninput="calculateBillTotals()" class="w-full px-2.5 py-1.5 border border-amber-300 focus:ring-2 focus:ring-amber-400 rounded-lg text-xs bg-white font-black text-amber-900">
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-500">Tarif / kWh</label>
                        <input type="number" id="bill-elec-price" value="1500" oninput="calculateBillTotals()" class="w-full px-2.5 py-1.5 border border-slate-200 rounded-lg text-xs bg-white font-bold">
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-600 mb-1">Biaya Lain-lain (Rp)</label>
                    <input type="number" id="bill-extra-fee" value="0" oninput="calculateBillTotals()" class="w-full px-3 py-2 border border-slate-200 rounded-xl text-xs font-bold focus:ring-2 focus:ring-blue-500 focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-600 mb-1">Keterangan Biaya Lain</label>
                    <input type="text" id="bill-extra-note" placeholder="misal: Kebersihan / WiFi" class="w-full px-3 py-2 border border-slate-200 rounded-xl text-xs font-semibold focus:ring-2 focus:ring-blue-500 focus:outline-none">
                </div>
            </div>

            <div class="bg-red-50 p-4 rounded-xl border border-red-200/80 space-y-2">
                <div class="flex justify-between items-center">
                    <label class="block text-xs font-bold text-red-900 flex items-center gap-1.5">
                        <i class="fa-solid fa-clock-rotate-left text-red-600"></i> Sisa Tunggakan Bulan Lalu
                    </label>
                    <span class="text-xs font-extrabold text-red-700" id="bill-arrears-subtotal-txt">Rp 0</span>
                </div>
                <input type="number" id="bill-arrears-fee" value="0" oninput="calculateBillTotals()" class="w-full px-3 py-2 border border-red-300 rounded-xl text-xs font-black text-red-900 bg-white focus:ring-2 focus:ring-red-500 focus:outline-none">
                <p class="text-[11px] text-red-700 font-medium" id="bill-arrears-periods-txt">Tidak ada tunggakan bulan sebelumnya.</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-600 mb-1">Jatuh Tempo *</label>
                    <input type="date" id="bill-due-date" required class="w-full px-3 py-2 border border-slate-200 rounded-xl text-xs font-semibold focus:ring-2 focus:ring-blue-500 focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-600 mb-1">Status (Otomatis)</label>
                    <select id="bill-status" disabled class="w-full px-3 py-2 border border-slate-200 rounded-xl text-xs font-bold bg-slate-100 text-slate-500 cursor-not-allowed">
                        <option value="Belum Lunas">Belum Lunas</option>
                        <option value="Lunas">Lunas</option>
                    </select>
                </div>
            </div>

            <div class="bg-slate-800 text-white p-4 rounded-xl flex flex-row justify-between items-center mt-2 shadow-inner">
                <div>
                    <span class="text-[10px] text-slate-400 block font-bold tracking-widest uppercase mb-0.5">TOTAL PEMBAYARAN:</span>
                    <span class="text-xl font-black text-emerald-400" id="bill-grand-total">Rp 0</span>
                </div>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold px-5 py-2.5 rounded-lg text-sm shadow-md transition flex items-center gap-2">
                    <i class="fa-solid fa-floppy-disk"></i> Simpan
                </button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL 2: ADD / EDIT TENANT -->
<div id="modal-tenant" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-md w-full shadow-2xl border border-slate-100 p-6 space-y-4">
        <div class="flex justify-between items-center border-b border-slate-100 pb-3">
            <h3 class="font-bold text-slate-900 text-lg" id="modal-tenant-title">Tambah Data Pelanggan</h3>
            <button onclick="closeModal('modal-tenant')" class="text-slate-400 hover:text-slate-600 text-xl font-bold">&times;</button>
        </div>
        <form id="form-tenant" onsubmit="handleSaveTenant(event)" class="space-y-4">
            <input type="hidden" id="tenant-id">
            
            <!-- INPUT TERSEMBUNYI AGAR JAVASCRIPT TIDAK ERROR -->
            <input type="hidden" id="tenant-room" value="-">
            <input type="hidden" id="tenant-rent" value="0">

            <div>
                <label class="block text-xs font-bold text-slate-600 mb-1">No. Registrasi *</label>
                <input type="text" id="tenant-noreg" required placeholder="misal: REG-101" class="w-full px-3 py-2 border border-slate-200 rounded-xl text-xs font-mono font-bold uppercase focus:ring-2 focus:ring-blue-500 focus:outline-none">
            </div>
            
            <div>
                <label class="block text-xs font-bold text-slate-600 mb-1">Nama Pelanggan *</label>
                <input type="text" id="tenant-name" required placeholder="Nama Lengkap Pelanggan" class="w-full px-3 py-2 border border-slate-200 rounded-xl text-xs font-bold focus:ring-2 focus:ring-blue-500 focus:outline-none">
            </div>
            
            <div>
                <label class="block text-xs font-bold text-slate-600 mb-1">No. WhatsApp Rujukan Tagihan *</label>
                <input type="tel" id="tenant-phone" required placeholder="081234567890" class="w-full px-3 py-2 border border-slate-200 rounded-xl text-xs font-bold focus:ring-2 focus:ring-blue-500 focus:outline-none">
            </div>
            
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-slate-600 mb-1">Meter Air Awal Baseline</label>
                    <input type="number" id="tenant-water-last" step="0.1" value="0" class="w-full px-3 py-2 border border-slate-200 rounded-xl text-xs font-bold focus:ring-2 focus:ring-blue-500 focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-600 mb-1">Meter Listrik Awal Baseline</label>
                    <input type="number" id="tenant-elec-last" step="0.1" value="0" class="w-full px-3 py-2 border border-slate-200 rounded-xl text-xs font-bold focus:ring-2 focus:ring-blue-500 focus:outline-none">
                </div>
            </div>
            
            <div class="pt-2 flex justify-between items-center border-t border-slate-100">
                <button type="button" id="btn-delete-tenant-in-modal" onclick="requestDeleteTenantFromModal()" class="hidden px-3 py-2 rounded-xl text-xs font-bold text-red-600 hover:bg-red-50 flex items-center gap-1.5">
                    <i class="fa-solid fa-trash"></i> Hapus
                </button>
                <div class="flex gap-2 ml-auto">
                    <button type="button" onclick="closeModal('modal-tenant')" class="px-4 py-2 rounded-xl text-xs font-bold text-slate-600 bg-slate-100 hover:bg-slate-200">Batal</button>
                    <button type="submit" class="px-5 py-2 rounded-xl text-xs font-extrabold text-white bg-blue-600 hover:bg-blue-700 shadow-sm">Simpan</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- MODAL 3: PREVIEW WA -->
<div id="modal-wa-preview" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-lg w-full shadow-2xl border border-slate-100 p-6 space-y-4">
        <div class="flex justify-between items-center border-b border-slate-100 pb-3">
            <h3 class="font-bold text-slate-900 text-base flex items-center gap-2">
                <i class="fa-brands fa-whatsapp text-wa-light text-xl"></i> Kirim Notifikasi via WhatsApp
            </h3>
            <button onclick="closeModal('modal-wa-preview')" class="text-slate-400 hover:text-slate-600 text-xl font-bold">&times;</button>
        </div>
        <div>
            <label class="block text-xs font-bold text-slate-600 mb-1">WhatsApp Rujukan Pelanggan:</label>
            <p id="wa-recipient-info" class="text-xs font-bold text-slate-800 bg-slate-100 px-3 py-2 rounded-lg"></p>
        </div>
        <div>
            <label class="block text-xs font-bold text-slate-600 mb-1">Pratinjau Pesan yang akan Dikirim:</label>
            <textarea id="wa-preview-text" rows="11" class="w-full p-3 font-mono text-xs leading-relaxed border border-slate-200 rounded-xl focus:ring-2 focus:ring-wa-light focus:outline-none bg-emerald-50/20"></textarea>
        </div>
        <div class="flex justify-between items-center pt-2">
            <button onclick="copyWaText()" class="text-xs font-bold text-slate-600 hover:text-slate-900 flex items-center gap-1">
                <i class="fa-solid fa-copy"></i> Salin Teks
            </button>
            <div class="flex gap-2">
                <button onclick="closeModal('modal-wa-preview')" class="px-4 py-2 rounded-xl text-xs font-bold text-slate-600 bg-slate-100 hover:bg-slate-200">Batal</button>
                <a id="wa-send-btn" target="_blank" onclick="closeModal('modal-wa-preview')" class="px-5 py-2 rounded-xl text-xs font-extrabold text-white bg-wa-light hover:bg-emerald-600 shadow-lg flex items-center gap-2">
                    <i class="fa-brands fa-whatsapp text-sm"></i> Buka WA & Kirim
                </a>
            </div>
        </div>
    </div>
</div>

<!-- MODAL 4: INVOICE PRINT -->
<div id="modal-invoice" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-xl w-full shadow-2xl border border-slate-100 p-6 space-y-6">
        <div class="flex justify-between items-center no-print">
            <h3 class="font-bold text-slate-900 text-lg">Pratinjau Invois & Struk Pembayaran</h3>
            <div class="flex gap-2">
                <button onclick="window.print()" class="bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold px-3 py-1.5 rounded-lg flex items-center gap-1.5">
                    <i class="fa-solid fa-print"></i> Cetak / PDF
                </button>
                <button onclick="closeModal('modal-invoice')" class="text-slate-400 hover:text-slate-600 text-xl font-bold">&times;</button>
            </div>
        </div>

        <div id="printable-invoice" class="bg-white p-6 rounded-xl border border-slate-200 space-y-6">
            <div class="flex justify-between items-start border-b border-slate-200 pb-4">
                <div>
                    <h2 class="text-lg font-luxury font-extrabold text-slate-900 tracking-wider" id="inv-owner-name">
                        PANDE MESARI BILLPRO</h2>
                    <p class="text-xs text-slate-500">Kuitansi Resmi Tagihan Air, Listrik & Kos</p>
                </div>
                <div class="text-right">
                    <span id="inv-status-badge" class="px-3 py-1 text-xs font-bold rounded-full"></span>
                    <p class="text-xs text-slate-400 mt-1" id="inv-date"></p>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4 text-xs">
                <div>
                    <p class="text-slate-400 uppercase font-bold text-[10px]">Pelanggan:</p>
                    <p class="font-extrabold text-amber-700 text-xs" id="inv-no-reg">REG-000</p>
                    <p class="font-bold text-slate-900 text-sm" id="inv-tenant-name">-</p>
                    <p class="text-slate-600" id="inv-room-no">-</p>
                    <p class="text-slate-500" id="inv-phone">-</p>
                </div>
                <div>
                    <p class="text-slate-400 uppercase font-bold text-[10px]">Periode Utama:</p>
                    <p class="font-bold text-slate-900 text-sm mt-0.5" id="inv-period">-</p>
                    <p class="text-slate-600" id="inv-due-date">-</p>
                </div>
            </div>

            <table class="w-full text-xs text-left border-collapse">
                <thead>
                    <tr class="bg-slate-100 font-bold text-slate-700">
                        <th class="p-2.5 rounded-l-lg">Deskripsi Rincian</th>
                        <th class="p-2.5 text-center">Pemakaian</th>
                        <th class="p-2.5 text-right rounded-r-lg">Subtotal</th>
                    </tr>
                </thead>
                <tbody id="inv-items-tbody" class="divide-y divide-slate-100"></tbody>
            </table>

            <div id="inv-payment-history-box" class="bg-slate-50 p-3 rounded-xl border border-slate-100 text-xs space-y-1.5 hidden">
                <p class="font-bold text-slate-800 flex items-center gap-1.5">
                    <i class="fa-solid fa-receipt text-blue-600"></i> Histori Penerimaan Pembayaran:
                </p>
                <div id="inv-payment-history-items" class="space-y-1 text-slate-600"></div>
            </div>

            <div class="flex justify-between items-end border-t border-slate-200 pt-4">
                <div class="text-xs text-slate-500 max-w-xs space-y-0.5">
                    <p class="font-bold text-slate-700">Metode Pembayaran Transfer:</p>
                    <p id="inv-payment-bank"></p>
                    <p id="inv-payment-no"></p>
                    <p id="inv-payment-holder"></p>
                </div>
                <div class="text-right space-y-1">
                    <div>
                        <span class="text-xs text-slate-400 font-semibold uppercase">Total Tagihan: </span>
                        <span class="text-sm font-bold text-slate-800" id="inv-total-amount">Rp 0</span>
                    </div>
                    <div>
                        <span class="text-xs text-emerald-600 font-semibold uppercase">Total Dibayar: </span>
                        <span class="text-sm font-bold text-emerald-700" id="inv-paid-amount">Rp 0</span>
                    </div>
                    <div class="border-t border-slate-200 pt-1">
                        <p class="text-xs text-slate-400 font-semibold uppercase">Sisa Kewajiban</p>
                        <p class="text-xl font-black text-amber-700" id="inv-remaining-amount">Rp 0</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- MODAL 5: CONFIRMATION -->
<div id="modal-confirm" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-md w-full shadow-2xl border border-slate-100 p-6 space-y-4 text-center">
        <div class="w-14 h-14 bg-red-100 text-red-600 rounded-full flex items-center justify-center text-2xl mx-auto">
            <i class="fa-solid fa-triangle-exclamation"></i>
        </div>
        <div>
            <h3 class="font-bold text-slate-900 text-lg" id="confirm-title">Konfirmasi</h3>
            <p class="text-xs text-slate-500 mt-1" id="confirm-message">Apakah Anda yakin?</p>
        </div>
        <div class="flex justify-center gap-3 pt-2">
            <button onclick="closeModal('modal-confirm')" class="px-5 py-2 rounded-xl text-xs font-bold text-slate-600 bg-slate-100 hover:bg-slate-200">Batal</button>
            <button id="confirm-action-btn" class="px-5 py-2 rounded-xl text-xs font-extrabold text-white bg-red-600 hover:bg-red-700 shadow-sm">Ya, Eksekusi</button>
        </div>
    </div>
</div>

<!-- MODAL 6: PAYMENT ENTRY -->
<div id="modal-pay" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-md w-full shadow-2xl border border-slate-100 p-6 space-y-4">
        <div class="flex justify-between items-center border-b border-slate-100 pb-3">
            <h3 class="font-bold text-slate-900 text-lg flex items-center gap-2">
                <i class="fa-solid fa-money-bill-wave text-blue-600"></i> Catat Pembayaran
            </h3>
            <button onclick="closeModal('modal-pay')" class="text-slate-400 hover:text-slate-600 text-xl font-bold">&times;</button>
        </div>
        <form id="form-pay" onsubmit="handleSavePayment(event)" class="space-y-4">
            <input type="hidden" id="pay-bill-id">

            <div class="bg-slate-50 p-3.5 rounded-xl border border-slate-200/80 space-y-2 text-xs">
                <div class="flex justify-between">
                    <span class="text-slate-500">Pelanggan:</span>
                    <span class="font-extrabold text-slate-900" id="pay-tenant-name">-</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-500">No. Reg / Kamar:</span>
                    <span class="font-bold text-amber-700" id="pay-noreg-room">-</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-500">Periode Tagihan:</span>
                    <span class="font-semibold text-slate-800" id="pay-period">-</span>
                </div>
                <hr class="border-slate-200">
                <div class="flex justify-between">
                    <span class="text-slate-500">Total Tagihan Periode Ini:</span>
                    <span class="font-bold text-slate-900" id="pay-bill-total">Rp 0</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-500">Sudah Dibayar Sebelumnya:</span>
                    <span class="font-bold text-emerald-600" id="pay-already-paid">Rp 0</span>
                </div>
                <div class="flex justify-between text-sm pt-1 border-t border-slate-200">
                    <span class="font-bold text-slate-700">Sisa Kekurangan:</span>
                    <span class="font-extrabold text-red-600" id="pay-remaining-amount">Rp 0</span>
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-600 mb-1">Jumlah Uang Dibayarkan saat Ini (Rp) *</label>
                <input type="number" id="pay-amount-input" required min="1" placeholder="Masukkan nominal uang" class="w-full px-3 py-2.5 border border-blue-500 rounded-xl text-base font-black text-slate-900 focus:ring-2 focus:ring-blue-500 focus:outline-none">
                <div class="flex gap-1.5 mt-2">
                    <button type="button" onclick="setPayAmountFull()" class="px-2.5 py-1 bg-blue-100 hover:bg-blue-200 text-blue-800 font-extrabold rounded-lg text-xs">Bayar Lunas (Pas)</button>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-slate-600 mb-1">Metode Bayar</label>
                    <select id="pay-method-select" class="w-full px-3 py-2 border border-slate-200 rounded-xl text-xs font-bold focus:ring-2 focus:ring-blue-500 focus:outline-none">
                        <option value="Transfer Bank">Transfer Bank</option>
                        <option value="Cash / Tunai">Cash / Tunai</option>
                        <option value="E-Wallet / QRIS">E-Wallet / QRIS</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-600 mb-1">Tanggal Bayar</label>
                    <input type="date" id="pay-date-input" required class="w-full px-3 py-2 border border-slate-200 rounded-xl text-xs font-semibold focus:ring-2 focus:ring-blue-500 focus:outline-none">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-600 mb-1">Catatan (Opsional)</label>
                <input type="text" id="pay-note-input" placeholder="misal: Bukti TF BCA" class="w-full px-3 py-2 border border-slate-200 rounded-xl text-xs font-semibold focus:ring-2 focus:ring-blue-500 focus:outline-none">
            </div>

            <div class="pt-2 flex justify-end gap-2">
                <button type="button" onclick="closeModal('modal-pay')" class="px-4 py-2 rounded-xl text-xs font-bold text-slate-600 bg-slate-100 hover:bg-slate-200">Batal</button>
                <button type="submit" class="px-5 py-2 rounded-xl text-xs font-extrabold text-white bg-blue-600 hover:bg-blue-700 shadow-md flex items-center gap-1.5">
                    <i class="fa-solid fa-check-circle"></i> Simpan Pembayaran
                </button>
            </div>
        </form>
    </div>
</div>