@extends('layouts.app')

@section('title', 'PANDE MESARI Billpro - Dashboard')

@push('styles')
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        .font-luxury {
            font-family: 'Cinzel', serif;
        }

        @media print {
            body * {
                visibility: hidden;
            }

            #printable-invoice,
            #printable-invoice * {
                visibility: visible;
            }

            #printable-invoice {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
            }

            .no-print {
                display: none !important;
            }
        }

        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }

        ::-webkit-scrollbar-track {
            background: #f1f5f9;
        }

        ::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 9999px;
        }
    </style>
@endpush

@section('content')
    @include('billpro.partials.dashboard')
    @include('billpro.partials.bills')
    @include('billpro.partials.history')
    @include('billpro.partials.tenants')
    @include('billpro.partials.settings')
    @if (Auth::user()->role === 'admin')
        @include('billpro.partials.admins')
    @endif
@endsection

@push('modals')
    @include('billpro.modals.all-modals')
@endpush

@push('scripts')
    <script>
        // ==========================================
        // 1. PENGATURAN GLOBAL & VARIABEL STATE
        // ==========================================
        const DEFAULT_SETTINGS = {
            ownerName: 'PANDE MESARI Billpro',
            bankName: 'Bank BCA / Mandiri',
            accountNumber: '8410-2938-47',
            accountHolder: 'Pande Mesari Management',
            defaultRateWater: 8000,
            defaultRateElec: 3000,
            // Default template diatur sama persis seperti di pengaturan agar aman
            waTemplate: `Halo {nama},\n\nBerikut rincian tagihan kos/kontrakan untuk periode *{periode}*:\n\n` +
                `• Sewa Kamar: Rp {sewa}\n` +
                `• Air ({air_awal} ➔ {air_akhir} = {air_m3} m³): Rp {air}\n` +
                `• Listrik ({listrik_awal} ➔ {listrik_akhir} = {listrik_kwh} kWh): Rp {listrik}\n` +
                `• Biaya Lain-lain: Rp {biaya_lain}\n` +
                `• Tunggakan Bulan Lalu: Rp {tunggakan}\n` +
                `==============================\n` +
                `*TOTAL KESELURUHAN DIBAYAR: Rp {total}*\n\n` +
                `Jatuh Tempo: *{jatuh_tempo}*\n` +
                `Transfer via {bank} No. Rek: *{rekening}* a.n *{pemilik}*.\n\n` +
                `Mohon kirimkan bukti pembayaran jika sudah transfer. Terima kasih! 🙏`
        };

        let settings = DEFAULT_SETTINGS;
        let tenants = [];
        let bills = [];
        let activePayBill = null;

        // ==========================================
        // 2. FUNGSI FETCH DATA MYSQL (Pelanggan & Tagihan)
        // ==========================================
        async function fetchTenantsFromDatabase() {
            try {
                const response = await fetch("{{ route('api.tenants') }}");
                if (response.ok) {
                    tenants = await response.json();
                    renderTenantsGrid();
                    renderHistoryTab();
                    renderDashboard();
                }
            } catch (error) {
                console.error("Error tenants:", error);
            }
        }

        async function fetchBillsFromDatabase() {
            try {
                const response = await fetch("{{ route('api.bills') }}");
                if (response.ok) {
                    bills = await response.json();
                    renderBillsTable();
                    renderHistoryTab();
                    renderDashboard();
                }
            } catch (error) {
                console.error("Error bills:", error);
            }
        }

        // ==========================================
        // 3. FUNGSI UTILITAS UMUM
        // ==========================================
        function formatRupiah(number) {
            return new Intl.NumberFormat('id-ID', {
                style: 'currency',
                currency: 'IDR',
                maximumFractionDigits: 0
            }).format(number || 0);
        }

        // Format angka murni (tanpa Rp) khusus untuk Template WA
        function formatAngka(number) {
            return new Intl.NumberFormat('id-ID').format(number || 0);
        }

        function formatMonthKeyToIndonesian(ymString) {
            if (!ymString) return '';
            const [year, month] = ymString.split('-');
            const months = ["Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September",
                "Oktober", "November", "Desember"
            ];
            return `${months[parseInt(month, 10) - 1]} ${year}`;
        }

        function getCurrentMonthKey() {
            const today = new Date();
            return `${today.getFullYear()}-${String(today.getMonth() + 1).padStart(2, '0')}`;
        }

        function showToast(message, type = 'success') {
            const container = document.getElementById('toast-container');
            if (!container) return;
            const bgClass = type === 'success' ? 'bg-slate-900 text-white border-gold-500' :
                'bg-red-900 text-white border-red-500';
            const iconClass = type === 'success' ? 'fa-circle-check text-gold-400' : 'fa-circle-exclamation text-red-400';
            const toast = document.createElement('div');
            toast.className =
                `pointer-events-auto flex items-center gap-3 px-4 py-3 rounded-2xl border-l-4 shadow-2xl text-xs font-bold transition-all duration-300 transform translate-x-5 opacity-0 ${bgClass}`;
            toast.innerHTML = `<i class="fa-solid ${iconClass} text-base"></i> <span>${message}</span>`;
            container.appendChild(toast);
            setTimeout(() => toast.classList.remove('translate-x-5', 'opacity-0'), 10);
            setTimeout(() => {
                toast.classList.add('translate-x-5', 'opacity-0');
                setTimeout(() => toast.remove(), 300);
            }, 3500);
        }

        function switchTab(tabName) {
            const tabs = ['dashboard', 'bills', 'history', 'tenants', 'settings', 'admins'];
            const activeClass =
                'w-full text-left px-4 py-3 bg-blue-600 text-white rounded-xl flex items-center gap-3 font-bold transition shadow-md shadow-blue-500/20';
            const inactiveClass =
                'w-full text-left px-4 py-3 text-slate-500 hover:bg-slate-50 hover:text-slate-800 rounded-xl flex items-center gap-3 font-medium transition';

            tabs.forEach(t => {
                const viewElement = document.getElementById(`view-${t}`);
                if (viewElement) viewElement.classList.add('hidden');
                const navDesktop = document.getElementById(`nav-${t}`);
                if (navDesktop) navDesktop.className = (t === tabName) ? activeClass : inactiveClass;
            });

            const targetElement = document.getElementById(`view-${tabName}`);
            if (targetElement) targetElement.classList.remove('hidden');

            if (tabName === 'settings') loadSettingsForm();
            if (tabName === 'bills') renderBillsTable();
            if (tabName === 'history') renderHistoryTab();
            if (tabName === 'dashboard') renderDashboard();
        }

        function openModal(id) {
            const el = document.getElementById(id);
            if (el) el.classList.remove('hidden');
        }

        function closeModal(id) {
            const el = document.getElementById(id);
            if (el) el.classList.add('hidden');
        }

        function openTenantModal() {
            const form = document.querySelector('#modal-tenant form');
            if (form) form.reset();
            openModal('modal-tenant');
        }

        function renderTenantsGrid() {
            const grid = document.getElementById('tenants-grid');
            if (!grid) return;
            grid.innerHTML = '';
            if (tenants.length === 0) {
                grid.innerHTML =
                    `<div class="col-span-full py-12 text-center text-slate-400">Belum ada pelanggan terdaftar di Database.</div>`;
                return;
            }
            tenants.forEach(t => {
                grid.innerHTML += `
                <div class="bg-white p-5 rounded-3xl border border-slate-200/80 shadow-sm space-y-4">
                    <div class="flex justify-between items-start">
                        <div>
                            <span class="bg-amber-100 text-amber-900 text-xs font-mono font-black px-2.5 py-0.5 rounded-lg border border-amber-300">${t.no_reg || 'REG-000'}</span>
                            <h3 class="text-lg font-bold text-slate-900 mt-2">${t.name}</h3>
                        </div>
                    </div>
                    <div class="flex justify-between items-center pt-2 border-t border-slate-100">
                        <button onclick="openGenerateModalForTenant('${t.id}')" class="text-xs font-extrabold text-blue-600 hover:text-blue-700 flex items-center gap-1"><i class="fa-solid fa-plus-circle"></i> Catat Tagihan Baru</button>
                    </div>
                </div>`;
            });
        }

        function renderBillsTable() {
            const viewBills = document.getElementById('view-bills');
            if (!viewBills) return;
            const tbody = viewBills.querySelector('tbody');
            if (!tbody) return;

            tbody.innerHTML = '';
            if (bills.length === 0) {
                tbody.innerHTML =
                    `<tr><td colspan="8" class="text-center py-10 font-medium text-slate-400 text-sm">Belum ada data tagihan di database.</td></tr>`;
                return;
            }

            bills.forEach(b => {
                const remaining = b.grand_total - b.paid_amount;
                const statusBadge = remaining <= 0 ?
                    `<span class="bg-emerald-100 text-emerald-700 px-3 py-1 rounded-full text-[10px] font-black uppercase">Lunas</span>` :
                    `<span class="bg-amber-100 text-amber-700 px-3 py-1 rounded-full text-[10px] font-black uppercase">Belum Lunas</span>`;

                tbody.innerHTML += `
                <tr class="border-b border-slate-100 hover:bg-slate-50 transition">
                    <td class="p-4 align-middle"><span class="text-amber-600 text-[11px] font-black px-2 py-0.5">${b.no_reg}</span></td>
                    <td class="p-4 align-middle"><div class="font-bold text-slate-800 text-xs">${b.tenant_name}</div></td>
                    <td class="p-4 align-middle font-semibold text-slate-600 text-xs">${b.period}</td>
                    <td class="p-4 align-middle text-xs text-slate-600">
                        <div>Air: <span class="font-bold text-blue-600">${b.water_usage} m³</span> <span class="text-[10px] text-slate-400">(${b.water_end} m³)</span></div>
                        <div>Listrik: <span class="font-bold text-amber-600">${b.elec_usage} kWh</span> <span class="text-[10px] text-slate-400">(${b.elec_end} kWh)</span></div>
                    </td>
                    <td class="p-4 align-middle text-xs">
                        <div class="font-bold text-slate-900">${formatRupiah(b.grand_total)}</div>
                        <div class="font-semibold text-emerald-600">Dibayar: ${formatRupiah(b.paid_amount)}</div>
                    </td>
                    <td class="p-4 align-middle font-black text-amber-700 text-xs">${formatRupiah(remaining)}</td>
                    <td class="p-4 align-middle">${statusBadge}</td>
                    
                    <td class="p-4 align-middle text-right">
                        <div class="flex items-center justify-end gap-1.5">
                            <button onclick="openPayModal(${b.id})" class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1.5 rounded-lg text-xs font-bold shadow-sm flex items-center gap-1.5 transition">
                                <i class="fa-solid fa-money-bill-wave"></i> Bayar
                            </button>
                            <button onclick="openWaModal(${b.id})" class="bg-emerald-50 text-emerald-600 hover:bg-emerald-100 w-8 h-8 rounded-lg flex items-center justify-center transition" title="Kirim WA">
                                <i class="fa-brands fa-whatsapp text-sm"></i>
                            </button>
                            <button onclick="openInvoiceModal(${b.id})" class="bg-slate-100 text-slate-600 hover:bg-slate-200 w-8 h-8 rounded-lg flex items-center justify-center transition" title="Cetak Invois">
                                <i class="fa-solid fa-print text-sm"></i>
                            </button>
                            <button onclick="editBill(${b.id})" class="bg-blue-50 text-blue-600 hover:bg-blue-100 w-8 h-8 rounded-lg flex items-center justify-center transition" title="Edit Tagihan">
                                <i class="fa-solid fa-pen text-sm"></i>
                            </button>
                            <button onclick="deleteBill(${b.id})" class="bg-red-50 text-red-600 hover:bg-red-100 w-8 h-8 rounded-lg flex items-center justify-center transition" title="Hapus Tagihan">
                                <i class="fa-solid fa-trash text-sm"></i>
                            </button>
                        </div>
                    </td>
                </tr>`;
            });
        }

        // ==========================================
        // 5.X FUNGSI FITUR BARU: WA, INVOICE & EDIT
        // ==========================================
        function openWaModal(billId) {
            const bill = bills.find(b => b.id == billId);
            const tenant = tenants.find(t => t.id == bill.tenant_id);
            if (!bill || !tenant) return showToast('Data pelanggan tidak ditemukan', 'error');

            const remaining = bill.grand_total - bill.paid_amount;
            let phone = tenant.phone || '';

            if (phone.startsWith('0')) {
                phone = '62' + phone.substring(1);
            }

            document.getElementById('wa-recipient-info').innerText = `${tenant.name} - ${phone}`;

            // Kalkulasi variabel tambahan
            const waterPrice = parseFloat(settings.defaultRateWater) || 8000;
            const elecPrice = parseFloat(settings.defaultRateElec) || 3000;
            const waterCost = bill.water_usage * waterPrice;
            const elecCost = bill.elec_usage * elecPrice;

            const tenantBills = bills.filter(b => b.tenant_id == bill.tenant_id).sort((a, b) => new Date(b.created_at) -
                new Date(a.created_at));
            let pastArrears = 0;
            if (tenantBills.length > 1 && tenantBills[0].id == bill.id) {
                const prevBill = tenantBills[1];
                pastArrears = Math.max(0, prevBill.grand_total - prevBill.paid_amount);
            }

            const baseTotal = waterCost + elecCost;
            const biayaLain = bill.grand_total - baseTotal - pastArrears;
            const totalPeriodeIni = bill.grand_total - pastArrears;

            // Ambil template dari pengaturan, jika kosong gunakan bawaan
            let msg = settings.waTemplate;
            if (!msg) msg = DEFAULT_SETTINGS.waTemplate;

            // Lakukan Replace Kustomisasi WA berdasarkan tag
            msg = msg.replace(/{noreg}/g, bill.no_reg)
                .replace(/{nama}/g, tenant.name)
                .replace(/{kamar}/g, '-')
                .replace(/{periode}/g, bill.period)
                .replace(/{sewa}/g, formatAngka(0))
                .replace(/{air_awal}/g, (bill.water_end - bill.water_usage))
                .replace(/{air_akhir}/g, bill.water_end)
                .replace(/{air_m3}/g, bill.water_usage)
                .replace(/{air}/g, formatAngka(waterCost))
                .replace(/{listrik_awal}/g, (bill.elec_end - bill.elec_usage))
                .replace(/{listrik_akhir}/g, bill.elec_end)
                .replace(/{listrik_kwh}/g, bill.elec_usage)
                .replace(/{listrik}/g, formatAngka(elecCost))
                .replace(/{biaya_lain}/g, formatAngka(biayaLain > 0 ? biayaLain : 0))
                .replace(/{total_periode}/g, formatAngka(totalPeriodeIni))
                .replace(/{tunggakan}/g, formatAngka(pastArrears))
                .replace(/{total}/g, formatAngka(bill.grand_total))
                .replace(/{jatuh_tempo}/g, 'Tanggal 10') // Secara default
                .replace(/{bank}/g, settings.bankName)
                .replace(/{rekening}/g, settings.accountNumber)
                .replace(/{pemilik}/g, settings.accountHolder);

            document.getElementById('wa-preview-text').value = msg;

            const sendBtn = document.getElementById('wa-send-btn');
            sendBtn.href = `https://wa.me/${phone}?text=${encodeURIComponent(msg)}`;

            openModal('modal-wa-preview');
        }

        function copyWaText() {
            const copyText = document.getElementById("wa-preview-text");
            copyText.select();
            copyText.setSelectionRange(0, 99999);
            navigator.clipboard.writeText(copyText.value);
            showToast('Teks WA berhasil disalin!');
        }

        function openInvoiceModal(billId) {
            const bill = bills.find(b => b.id == billId);
            const tenant = tenants.find(t => t.id == bill.tenant_id);
            if (!bill || !tenant) return;

            const remaining = bill.grand_total - bill.paid_amount;

            document.getElementById('inv-owner-name').innerText = settings.ownerName || 'PANDE MESARI';

            const badge = document.getElementById('inv-status-badge');
            if (remaining <= 0) {
                badge.className = 'px-3 py-1 text-xs font-bold rounded-full bg-emerald-100 text-emerald-700';
                badge.innerText = 'LUNAS';
            } else {
                badge.className = 'px-3 py-1 text-xs font-bold rounded-full bg-amber-100 text-amber-700';
                badge.innerText = 'BELUM LUNAS';
            }

            document.getElementById('inv-date').innerText = 'Dicetak: ' + new Date().toLocaleDateString('id-ID');
            document.getElementById('inv-no-reg').innerText = bill.no_reg;
            document.getElementById('inv-tenant-name').innerText = bill.tenant_name;
            document.getElementById('inv-room-no').innerText = 'WA:';
            document.getElementById('inv-phone').innerText = tenant.phone || '-';

            document.getElementById('inv-period').innerText = bill.period;
            document.getElementById('inv-due-date').innerText = '';

            const tbody = document.getElementById('inv-items-tbody');
            tbody.innerHTML = '';

            const waterPrice = settings.defaultRateWater || 8000;
            const elecPrice = settings.defaultRateElec || 3000;

            // Generate rows
            tbody.innerHTML += `
                <tr>
                    <td class="p-2.5 border-b border-slate-50">Pemakaian Air (${bill.water_usage} m³)</td>
                    <td class="p-2.5 border-b border-slate-50 text-center">${formatRupiah(waterPrice)} / m³</td>
                    <td class="p-2.5 border-b border-slate-50 text-right">${formatRupiah(bill.water_usage * waterPrice)}</td>
                </tr>
                <tr>
                    <td class="p-2.5 border-b border-slate-50">Pemakaian Listrik (${bill.elec_usage} kWh)</td>
                    <td class="p-2.5 border-b border-slate-50 text-center">${formatRupiah(elecPrice)} / kWh</td>
                    <td class="p-2.5 border-b border-slate-50 text-right">${formatRupiah(bill.elec_usage * elecPrice)}</td>
                </tr>
            `;

            // Hitung jika ada tambahan biaya (Tunggakan/Lainnya)
            const baseAmount = (bill.water_usage * waterPrice) + (bill.elec_usage * elecPrice);
            const extraFee = bill.grand_total - baseAmount;
            if (extraFee > 0) {
                tbody.innerHTML += `
                    <tr>
                        <td class="p-2.5 border-b border-slate-50 font-bold">Biaya Lain / Sisa Tunggakan Sebelumnya</td>
                        <td class="p-2.5 border-b border-slate-50 text-center">-</td>
                        <td class="p-2.5 border-b border-slate-50 text-right font-bold">${formatRupiah(extraFee)}</td>
                    </tr>
                `;
            }

            document.getElementById('inv-payment-bank').innerText = settings.bankName;
            document.getElementById('inv-payment-no').innerText = settings.accountNumber;
            document.getElementById('inv-payment-holder').innerText = 'a/n ' + settings.accountHolder;

            document.getElementById('inv-total-amount').innerText = formatRupiah(bill.grand_total);
            document.getElementById('inv-paid-amount').innerText = formatRupiah(bill.paid_amount);
            document.getElementById('inv-remaining-amount').innerText = formatRupiah(remaining);

            openModal('modal-invoice');
        }

        function editBill(billId) {
            const bill = bills.find(b => b.id == billId);
            if (!bill) return;

            document.getElementById('modal-bill-title').innerText = 'Edit Tagihan Bulanan';

            // Set Hidden Input (Penanda Edit)
            const idInput = document.getElementById('bill-id');
            if (idInput) idInput.value = bill.id;

            const select = document.getElementById('bill-tenant-id');
            select.innerHTML = '<option value="">-- Pilih Pelanggan / No. Reg --</option>';
            tenants.forEach(t => {
                const isSelected = t.id == bill.tenant_id ? 'selected' : '';
                select.innerHTML +=
                    `<option value="${t.id}" ${isSelected}>${t.no_reg || 'REG'} - ${t.name}</option>`;
            });

            document.getElementById('bill-month-picker').value = bill.period_key;
            document.getElementById('bill-period').value = bill.period;

            document.getElementById('bill-water-curr').value = bill.water_end;
            document.getElementById('bill-elec-curr').value = bill.elec_end;

            document.getElementById('bill-water-prev').value = bill.water_end - bill.water_usage;
            document.getElementById('bill-elec-prev').value = bill.elec_end - bill.elec_usage;

            const waterPrice = settings.defaultRateWater || 8000;
            const elecPrice = settings.defaultRateElec || 3000;
            const baseAmount = (bill.water_usage * waterPrice) + (bill.elec_usage * elecPrice);
            const extraFee = bill.grand_total - baseAmount;

            document.getElementById('bill-arrears-fee').value = extraFee > 0 ? extraFee : 0;
            document.getElementById('bill-water-price').value = waterPrice;
            document.getElementById('bill-elec-price').value = elecPrice;

            const infoBox = document.getElementById('selected-tenant-info');
            if (infoBox) infoBox.classList.remove('hidden');
            if (document.getElementById('bill-info-noreg')) document.getElementById('bill-info-noreg').innerText = bill
                .no_reg;

            calculateBillTotals();
            openModal('modal-bill');
        }

        // ==========================================
        // 5.B LOGIKA PEMBAYARAN TAGIHAN (PAYMENT)
        // ==========================================
        function openPayModal(billId) {
            activePayBill = bills.find(b => b.id == billId);
            if (!activePayBill) return;

            const tenantBills = bills.filter(b => b.tenant_id == activePayBill.tenant_id).sort((a, b) => new Date(b
                .created_at) - new Date(a.created_at));
            let pastArrears = 0;
            if (tenantBills.length > 1 && tenantBills[0].id == activePayBill.id) {
                const prevBill = tenantBills[1];
                pastArrears = Math.max(0, prevBill.grand_total - prevBill.paid_amount);
            }

            const remaining = activePayBill.grand_total - activePayBill.paid_amount;

            document.getElementById('pay-bill-id').value = activePayBill.id;
            document.getElementById('pay-tenant-name').innerText = activePayBill.tenant_name;
            document.getElementById('pay-noreg-room').innerText = activePayBill.no_reg;
            document.getElementById('pay-period').innerText = activePayBill.period;

            document.getElementById('pay-bill-total').innerText = formatRupiah(activePayBill.grand_total);
            document.getElementById('pay-already-paid').innerText = formatRupiah(activePayBill.paid_amount);

            const arrearsInfoEl = document.getElementById('pay-arrears-info');
            if (arrearsInfoEl) {
                if (pastArrears > 0) {
                    arrearsInfoEl.innerHTML =
                        `<span class="text-[10px] text-red-500 font-bold block">(Termasuk Tunggakan Sebelumnya: ${formatRupiah(pastArrears)})</span>`;
                } else {
                    arrearsInfoEl.innerHTML = '';
                }
            }

            document.getElementById('pay-remaining-amount').innerText = formatRupiah(remaining);

            const amountInput = document.getElementById('pay-amount-input');
            amountInput.value = '';
            amountInput.max = remaining;

            document.getElementById('pay-date-input').value = new Date().toISOString().split('T')[0];

            openModal('modal-pay');
        }

        function setPayAmountFull() {
            if (!activePayBill) return;
            const remaining = activePayBill.grand_total - activePayBill.paid_amount;
            document.getElementById('pay-amount-input').value = remaining;
        }

        async function handleSavePayment(event) {
            event.preventDefault();
            if (!activePayBill) return;

            const payAmount = parseFloat(document.getElementById('pay-amount-input').value) || 0;
            if (payAmount <= 0) return showToast('Nominal pembayaran harus lebih dari 0', 'error');

            const payloadData = {
                amount: payAmount,
                method: document.getElementById('pay-method-select').value,
                date: document.getElementById('pay-date-input').value,
                note: document.getElementById('pay-note-input').value
            };

            try {
                const response = await fetch(`/bills/pay/${activePayBill.id}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute(
                            'content'),
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(payloadData)
                });

                if (response.ok) {
                    closeModal('modal-pay');
                    showToast('Pembayaran berhasil dicatat!');
                    fetchBillsFromDatabase();
                }
            } catch (e) {
                showToast('Gagal memproses pembayaran.', 'error');
            }
        }

        async function deleteBill(id) {
            if (confirm('Yakin ingin menghapus tagihan ini dari Database?')) {
                try {
                    const response = await fetch(`/bills/${id}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute(
                                'content'),
                            'Accept': 'application/json'
                        }
                    });
                    if (response.ok) {
                        fetchBillsFromDatabase();
                        showToast('Tagihan berhasil dihapus!');
                    }
                } catch (e) {
                    showToast('Gagal menghapus data.', 'error');
                }
            }
        }

        // ==========================================
        // 5.C LOGIKA MEMBUAT TAGIHAN BARU / EDIT
        // ==========================================
        function onBillTenantSelect(isAutoFill = true) {
            const tenantId = document.getElementById('bill-tenant-id').value;
            const tenant = tenants.find(t => t.id == tenantId);

            if (tenant) {
                const infoBox = document.getElementById('selected-tenant-info');
                if (infoBox) infoBox.classList.remove('hidden');
                if (document.getElementById('bill-info-noreg')) document.getElementById('bill-info-noreg').innerText =
                    tenant.no_reg || 'REG-000';

                // AUTO FILL Meteran lama hanya dilakukan jika membuat baru
                if (isAutoFill) {
                    const tenantBills = bills.filter(b => b.tenant_id == tenantId).sort((a, b) => new Date(b.created_at) -
                        new Date(a.created_at));

                    if (tenantBills.length > 0) {
                        const lastBill = tenantBills[0];

                        document.getElementById('bill-water-prev').value = parseFloat(lastBill.water_end) || 0;
                        document.getElementById('bill-elec-prev').value = parseFloat(lastBill.elec_end) || 0;

                        const lastArrears = Math.max(0, parseFloat(lastBill.grand_total) - parseFloat(lastBill
                        .paid_amount));
                        document.getElementById('bill-arrears-fee').value = lastArrears;

                    } else {
                        document.getElementById('bill-water-prev').value = 0;
                        document.getElementById('bill-elec-prev').value = 0;
                        document.getElementById('bill-arrears-fee').value = 0;
                    }
                }
            } else {
                document.getElementById('bill-water-prev').value = 0;
                document.getElementById('bill-elec-prev').value = 0;
                document.getElementById('bill-arrears-fee').value = 0;
                const infoBox = document.getElementById('selected-tenant-info');
                if (infoBox) infoBox.classList.add('hidden');
            }

            calculateBillTotals();
        }

        function onBillPeriodChange() {
            const periodInput = document.getElementById('bill-month-picker').value;
            if (document.getElementById('bill-period')) {
                document.getElementById('bill-period').value = formatMonthKeyToIndonesian(periodInput);
            }
        }

        function openGenerateModal(billId = null, initialMonthKey = null) {
            const form = document.getElementById('form-bill');
            if (form) form.reset();

            document.getElementById('modal-bill-title').innerText = 'Catat Tagihan Bulanan';
            const idInput = document.getElementById('bill-id');
            if (idInput) idInput.value = '';

            const select = document.getElementById('bill-tenant-id');
            if (select) {
                select.innerHTML = '<option value="">-- Pilih Pelanggan / No. Reg --</option>';
                tenants.forEach(t => {
                    select.innerHTML += `<option value="${t.id}">${t.no_reg || 'REG'} - ${t.name}</option>`;
                });
            }

            const targetMonthKey = initialMonthKey || getCurrentMonthKey();
            if (document.getElementById('bill-month-picker')) document.getElementById('bill-month-picker').value =
                targetMonthKey;
            if (document.getElementById('bill-period')) document.getElementById('bill-period').value =
                formatMonthKeyToIndonesian(targetMonthKey);
            if (document.getElementById('bill-water-price')) document.getElementById('bill-water-price').value = settings
                .defaultRateWater;
            if (document.getElementById('bill-elec-price')) document.getElementById('bill-elec-price').value = settings
                .defaultRateElec;

            onBillTenantSelect(true);
            openModal('modal-bill');
        }

        function openGenerateModalForTenant(tenantId, monthKey = null) {
            openGenerateModal(null, monthKey);
            const select = document.getElementById('bill-tenant-id');
            if (select) {
                select.value = tenantId;
                onBillTenantSelect(true);
            }
        }

        function calculateBillTotals() {
            const waterPrev = parseFloat(document.getElementById('bill-water-prev').value) || 0;
            const waterCurr = parseFloat(document.getElementById('bill-water-curr').value) || 0;
            const waterPrice = parseFloat(document.getElementById('bill-water-price').value) || 0;
            const waterUsage = Math.max(0, waterCurr - waterPrev);
            const waterSubtotal = waterUsage * waterPrice;
            if (document.getElementById('water-subtotal-txt')) document.getElementById('water-subtotal-txt').innerText =
                'Subtotal: ' + formatRupiah(waterSubtotal);

            const elecPrev = parseFloat(document.getElementById('bill-elec-prev').value) || 0;
            const elecCurr = parseFloat(document.getElementById('bill-elec-curr').value) || 0;
            const elecPrice = parseFloat(document.getElementById('bill-elec-price').value) || 0;
            const elecUsage = Math.max(0, elecCurr - elecPrev);
            const elecSubtotal = elecUsage * elecPrice;
            if (document.getElementById('elec-subtotal-txt')) document.getElementById('elec-subtotal-txt').innerText =
                'Subtotal: ' + formatRupiah(elecSubtotal);

            const extraFee = parseFloat(document.getElementById('bill-extra-fee')?.value || 0);
            const arrearsFee = parseFloat(document.getElementById('bill-arrears-fee')?.value || 0);

            const grandTotal = waterSubtotal + elecSubtotal + extraFee + arrearsFee;
            if (document.getElementById('bill-grand-total')) document.getElementById('bill-grand-total').innerText =
                formatRupiah(grandTotal);
        }

        async function handleSaveBill(event) {
            event.preventDefault();

            const tenantId = document.getElementById('bill-tenant-id').value;
            const tenant = tenants.find(t => t.id == tenantId);
            if (!tenant) return showToast('Pilih pelanggan dulu!', 'error');

            const waterCurr = parseFloat(document.getElementById('bill-water-curr').value) || 0;
            const waterPrev = parseFloat(document.getElementById('bill-water-prev').value) || 0;
            const elecCurr = parseFloat(document.getElementById('bill-elec-curr').value) || 0;
            const elecPrev = parseFloat(document.getElementById('bill-elec-prev').value) || 0;

            const waterUsage = Math.max(0, waterCurr - waterPrev);
            const elecUsage = Math.max(0, elecCurr - elecPrev);

            const grandTotal =
                (waterUsage * (parseFloat(document.getElementById('bill-water-price').value) || 0)) +
                (elecUsage * (parseFloat(document.getElementById('bill-elec-price').value) || 0)) +
                (parseFloat(document.getElementById('bill-extra-fee')?.value || 0)) +
                (parseFloat(document.getElementById('bill-arrears-fee')?.value || 0));

            const payloadData = {
                tenant_id: tenant.id,
                tenant_name: tenant.name,
                no_reg: tenant.no_reg || 'REG-000',
                period_key: document.getElementById('bill-month-picker').value,
                period: document.getElementById('bill-period').value,
                water_end: waterCurr,
                elec_end: elecCurr,
                water_usage: waterUsage,
                elec_usage: elecUsage,
                grand_total: grandTotal
            };

            const billId = document.getElementById('bill-id')?.value;
            const isEdit = billId && billId !== '';

            const fetchUrl = isEdit ? `/bills/${billId}` : "{{ route('bills.store') }}";
            const fetchMethod = isEdit ? 'PUT' : 'POST';

            try {
                const response = await fetch(fetchUrl, {
                    method: fetchMethod,
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute(
                            'content'),
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(payloadData)
                });

                if (response.ok) {
                    closeModal('modal-bill');
                    showToast(isEdit ? 'Tagihan sukses diperbarui!' : 'Tagihan berhasil masuk ke Database!');
                    fetchBillsFromDatabase();
                    switchTab('bills');
                }
            } catch (e) {
                showToast('Gagal terhubung ke Database!', 'error');
            }
        }

        // ==========================================
        // 6. FUNGSI PENGATURAN
        // ==========================================
        function loadSettingsForm() {
            const saved = localStorage.getItem('billpro_settings');
            if (saved) settings = JSON.parse(saved);

            if (document.getElementById('cfg-owner-name')) document.getElementById('cfg-owner-name').value = settings
                .ownerName;
            if (document.getElementById('cfg-bank-name')) document.getElementById('cfg-bank-name').value = settings
            .bankName;
            if (document.getElementById('cfg-account-no')) document.getElementById('cfg-account-no').value = settings
                .accountNumber;
            if (document.getElementById('cfg-account-holder')) document.getElementById('cfg-account-holder').value =
                settings.accountHolder;
            if (document.getElementById('cfg-rate-water')) document.getElementById('cfg-rate-water').value = settings
                .defaultRateWater;
            if (document.getElementById('cfg-rate-elec')) document.getElementById('cfg-rate-elec').value = settings
                .defaultRateElec;
            if (document.getElementById('cfg-wa-template')) document.getElementById('cfg-wa-template').value = settings
                .waTemplate;
        }

        function saveSettings() {
            settings.ownerName = document.getElementById('cfg-owner-name').value || '';
            settings.bankName = document.getElementById('cfg-bank-name').value || '';
            settings.accountNumber = document.getElementById('cfg-account-no').value || '';
            settings.accountHolder = document.getElementById('cfg-account-holder').value || '';
            settings.defaultRateWater = document.getElementById('cfg-rate-water').value || 0;
            settings.defaultRateElec = document.getElementById('cfg-rate-elec').value || 0;
            settings.waTemplate = document.getElementById('cfg-wa-template').value || '';

            localStorage.setItem('billpro_settings', JSON.stringify(settings));
            showToast('Pengaturan berhasil disimpan!');
        }

        // ==========================================
        // 8. FUNGSI TAB REKAPAN & AUDIT (HISTORY)
        // ==========================================
        function renderHistoryTab() {
            const auditPeriodInput = document.getElementById('audit-period-input');
            if (auditPeriodInput && !auditPeriodInput.value) {
                auditPeriodInput.value = getCurrentMonthKey();
            }
            renderUnbilledTenants();
            renderUnpaidBills();
        }

        function renderUnbilledTenants() {
            const tbody = document.getElementById('unbilled-tenants-tbody');
            const periodKey = document.getElementById('audit-period-input')?.value;
            if (!tbody || !periodKey) return;

            tbody.innerHTML = '';

            const unbilledTenants = tenants.filter(t => {
                return !bills.some(b => b.tenant_id == t.id && b.period_key === periodKey);
            });

            if (unbilledTenants.length === 0) {
                tbody.innerHTML =
                    `<tr><td colspan="6" class="text-center py-8 text-slate-400 text-sm font-medium">Semua pelanggan sudah dicatat tagihannya untuk periode ini. Mantap! 🎉</td></tr>`;
                return;
            }

            unbilledTenants.forEach(t => {
                tbody.innerHTML += `
                <tr class="border-b border-slate-100 hover:bg-slate-50 transition">
                    <td class="p-4 align-middle"><span class="text-amber-600 text-[11px] font-black px-2 py-0.5">${t.no_reg || 'REG-000'}</span></td>
                    <td class="p-4 align-middle"><div class="font-bold text-slate-800 text-xs">${t.name}</div></td>
                    <td class="p-4 align-middle text-xs text-slate-600">-</td>
                    <td class="p-4 align-middle text-xs text-slate-600">${t.phone || '-'}</td>
                    <td class="p-4 align-middle">
                        <span class="bg-red-100 text-red-700 px-3 py-1 rounded-full text-[10px] font-black uppercase">Belum Dicatat</span>
                    </td>
                    <td class="p-4 align-middle text-right">
                        <button onclick="openGenerateModalForTenant('${t.id}', '${periodKey}')" class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1.5 rounded-lg text-xs font-bold shadow-sm flex items-center justify-end gap-1.5 ml-auto transition">
                            <i class="fa-solid fa-plus"></i> Catat Sekarang
                        </button>
                    </td>
                </tr>`;
            });
        }

        function renderUnpaidBills() {
            const tbody = document.getElementById('unpaid-bills-tbody');
            if (!tbody) return;

            tbody.innerHTML = '';

            const unpaidBills = bills.filter(b => b.status === 'Belum Lunas' || (b.grand_total - b.paid_amount) > 0);

            if (unpaidBills.length === 0) {
                tbody.innerHTML =
                    `<tr><td colspan="6" class="text-center py-8 text-slate-400 text-sm font-medium">Tidak ada tunggakan. Semua kewajiban sudah lunas! 💸</td></tr>`;
                return;
            }

            unpaidBills.forEach(b => {
                const remaining = b.grand_total - b.paid_amount;
                tbody.innerHTML += `
                <tr class="border-b border-slate-100 hover:bg-slate-50 transition">
                    <td class="p-4 align-middle"><span class="text-amber-600 text-[11px] font-black px-2 py-0.5">${b.no_reg}</span></td>
                    <td class="p-4 align-middle">
                        <div class="font-bold text-slate-800 text-xs">${b.tenant_name}</div>
                    </td>
                    <td class="p-4 align-middle font-semibold text-slate-600 text-xs">${b.period}</td>
                    <td class="p-4 align-middle text-xs text-slate-600">
                        <div>Air: <span class="font-bold">${b.water_usage} m³</span></div>
                        <div>Listrik: <span class="font-bold">${b.elec_usage} kWh</span></div>
                    </td>
                    <td class="p-4 align-middle font-black text-red-600 text-sm">${formatRupiah(remaining)}</td>
                    <td class="p-4 align-middle text-right">
                        <div class="flex items-center justify-end gap-1.5">
                            <button onclick="openPayModal(${b.id})" class="bg-emerald-500 hover:bg-emerald-600 text-white px-3 py-1.5 rounded-lg text-xs font-bold shadow-sm flex items-center gap-1.5 transition">
                                <i class="fa-solid fa-money-bill-wave"></i> Bayar
                            </button>
                        </div>
                    </td>
                </tr>`;
            });
        }

        // ==========================================
        // 9. FUNGSI LOGIKA DASHBOARD KEUANGAN
        // ==========================================
        function resetDashboardPeriod() {
            const periodInput = document.getElementById('dash-period-input');
            if (periodInput) periodInput.value = '';
            renderDashboard();
        }

        function renderDashboard() {
            const periodInput = document.getElementById('dash-period-input');
            const selectedPeriod = periodInput ? periodInput.value : '';

            if (document.getElementById('dash-period-label')) {
                document.getElementById('dash-period-label').innerText = selectedPeriod ? formatMonthKeyToIndonesian(
                    selectedPeriod) : 'Semua Periode';
            }

            let filteredBills = bills;
            if (selectedPeriod) {
                filteredBills = bills.filter(b => b.period_key === selectedPeriod);
            }

            let incomeTotal = 0;
            let billTotal = 0;
            let unpaidTotal = 0;
            let lunasCount = 0;
            let belumLunasCount = 0;

            filteredBills.forEach(b => {
                incomeTotal += b.paid_amount;
                billTotal += b.grand_total;

                let remaining = b.grand_total - b.paid_amount;
                unpaidTotal += remaining;

                if (remaining <= 0) {
                    lunasCount++;
                } else {
                    belumLunasCount++;
                }
            });

            const targetPeriod = selectedPeriod || getCurrentMonthKey();
            const auditCount = tenants.filter(t => !bills.some(b => b.tenant_id == t.id && b.period_key === targetPeriod))
                .length;

            if (document.getElementById('dash-income-total')) document.getElementById('dash-income-total').innerText =
                formatRupiah(incomeTotal);
            if (document.getElementById('dash-income-count')) document.getElementById('dash-income-count').innerText =
                `${lunasCount} Tagihan Telah Lunas`;

            if (document.getElementById('dash-bill-total')) document.getElementById('dash-bill-total').innerText =
                formatRupiah(billTotal);
            if (document.getElementById('dash-bill-count')) document.getElementById('dash-bill-count').innerText =
                `${filteredBills.length} Dokumen Tagihan`;

            if (document.getElementById('dash-unpaid-total')) document.getElementById('dash-unpaid-total').innerText =
                formatRupiah(unpaidTotal);
            if (document.getElementById('dash-unpaid-count')) document.getElementById('dash-unpaid-count').innerText =
                `${belumLunasCount} Status Belum Lunas`;

            if (document.getElementById('dash-audit-count')) document.getElementById('dash-audit-count').innerText =
                auditCount;

            const tbody = document.getElementById('dash-action-tbody');
            if (tbody) {
                tbody.innerHTML = '';

                let actionBills = bills.filter(b => (b.grand_total - b.paid_amount) > 0);
                if (selectedPeriod) {
                    actionBills = actionBills.filter(b => b.period_key === selectedPeriod);
                }

                if (actionBills.length === 0) {
                    tbody.innerHTML =
                        `<tr><td colspan="5" class="text-center py-8 text-slate-400 text-sm font-medium">Luar biasa! Tidak ada tagihan yang tertunggak pada periode ini. 🥳</td></tr>`;
                } else {
                    actionBills.forEach(b => {
                        const remaining = b.grand_total - b.paid_amount;
                        tbody.innerHTML += `
                        <tr class="border-b border-slate-100 hover:bg-slate-50 transition">
                            <td class="p-4 align-middle">
                                <div class="font-bold text-slate-800 text-sm">${b.tenant_name}</div>
                                <div class="text-[11px] font-bold text-amber-600 mt-0.5">${b.no_reg}</div>
                            </td>
                            <td class="p-4 align-middle text-xs font-semibold text-slate-600">${b.period}</td>
                            <td class="p-4 align-middle font-bold text-slate-800 text-sm">${formatRupiah(b.grand_total)}</td>
                            <td class="p-4 align-middle font-black text-red-600 text-sm">${formatRupiah(remaining)}</td>
                            <td class="p-4 align-middle text-right">
                                <button onclick="openPayModal(${b.id})" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-xs font-bold shadow-md transition flex items-center justify-end gap-1.5 ml-auto">
                                    <i class="fa-solid fa-money-bill-wave"></i> Bayar
                                </button>
                            </td>
                        </tr>`;
                    });
                }
            }
        }

        // ==========================================
        // 10. INISIALISASI SAAT HALAMAN DIBUKA
        // ==========================================
        window.onload = function() {
            loadSettingsForm();

            @if (session('success_admin'))
                setTimeout(() => switchTab('admins'), 100);
            @endif
            @if (session('success_tenant'))
                setTimeout(() => switchTab('tenants'), 100);
                showToast("{{ session('success_tenant') }}");
            @endif

            fetchTenantsFromDatabase();
            fetchBillsFromDatabase();
        };
    </script>
@endpush
