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

        ::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }
    </style>
@endpush

@section('content')
    <!-- Panggil Partials / Menu -->
    @include('billpro.partials.dashboard')
    @include('billpro.partials.bills')
    @include('billpro.partials.history')
    @include('billpro.partials.tenants')
    @include('billpro.partials.settings')
    @if(Auth::user()->role === 'admin')
        @include('billpro.partials.admins') <!-- TAMBAHKAN BARIS INI -->
    @endif
@endsection

@push('modals')
    <!-- Panggil semua Modals -->
    @include('billpro.modals.all-modals')
@endpush

@push('scripts')
    <script>
        const appId = typeof __app_id !== 'undefined' ? __app_id : 'pande-mesari-billpro-app';

        // TARI DEFAULT SUDAH DIUBAH DI SINI
        const DEFAULT_SETTINGS = {
            ownerName: 'PANDE MESARI Billpro',
            bankName: 'Bank BCA / Mandiri',
            accountNumber: '8410-2938-47',
            accountHolder: 'Pande Mesari Management',
            defaultRateWater: 8000,
            defaultRateElec: 3000,
            waTemplate: `Halo Kak {nama} (No. Reg: {noreg} / {kamar}), berikut rincian tagihan air, listrik & sewa periode *{periode}*:\n\n` +
                `• Sewa Kamar: Rp {sewa}\n` +
                `• Air ({air_awal} ➔ {air_akhir} = {air_m3} m³): Rp {air}\n` +
                `• Listrik ({listrik_awal} ➔ {listrik_akhir} = {listrik_kwh} kWh): Rp {listrik}\n` +
                `• Biaya Lain-lain: Rp {biaya_lain}\n` +
                `• Tunggakan Bulan Lalu: Rp {tunggakan}\n` +
                `=========================================\n` +
                `*TOTAL KESELURUHAN DIBAYAR: Rp {total}*\n\n` +
                `Jatuh Tempo: *{jatuh_tempo}*\n` +
                `Transfer via {bank} No. Rek: *{rekening}* a.n *{pemilik}*.\n\n` +
                `Mohon kirimkan bukti pembayaran jika sudah transfer. Terima kasih! 🙏`
        };

        const INITIAL_TENANTS = [{
            id: 't-101',
            noReg: 'REG-101',
            roomNo: 'Kamar 01',
            name: 'Andi Pratama',
            phone: '081234567890',
            baseRent: 1200000,
            lastWater: 45.0,
            lastElec: 320.0
        }, {
            id: 't-102',
            noReg: 'REG-102',
            roomNo: 'Kamar 02',
            name: 'Budi Santoso',
            phone: '082198765432',
            baseRent: 1000000,
            lastWater: 30.5,
            lastElec: 210.0
        }, {
            id: 't-103',
            noReg: 'REG-103',
            roomNo: 'Kamar 03',
            name: 'Citra Dewi',
            phone: '085611223344',
            baseRent: 1500000,
            lastWater: 62.0,
            lastElec: 450.5
        }];

        const INITIAL_BILLS = [{
            id: 'b-001',
            tenantId: 't-101',
            noReg: 'REG-101',
            roomNo: 'Kamar 01',
            tenantName: 'Andi Pratama',
            phone: '081234567890',
            monthKey: '2026-03',
            period: 'Maret 2026',
            waterPrev: 35.0,
            waterCurr: 45.0,
            waterPrice: 8000,
            waterTotal: 80000,
            elecPrev: 280.0,
            elecCurr: 320.0,
            elecPrice: 3000,
            elecTotal: 120000,
            roomRent: 1200000,
            extraFee: 15000,
            extraNote: 'Biaya Kebersihan',
            arrearsFee: 0,
            arrearsNotes: '',
            totalAmount: 1415000,
            paidAmount: 500000,
            payments: [{
                id: 'p-1',
                amount: 500000,
                method: 'Transfer Bank',
                date: '2026-03-02',
                note: 'DP Awal'
            }],
            status: 'Belum Lunas',
            dueDate: '2026-03-10',
            createdAt: new Date().toISOString()
        }];

        let settings = JSON.parse(localStorage.getItem('pande_settings')) || DEFAULT_SETTINGS;
        let tenants = JSON.parse(localStorage.getItem('pande_tenants')) || INITIAL_TENANTS;
        let bills = JSON.parse(localStorage.getItem('pande_bills')) || INITIAL_BILLS;
        let deleteConfirmAction = null;
        let activePayBill = null;
        let db = null;
        let auth = null;
        let isOnlineConnected = false;

        function initOnlineCloudSync() {
            if (typeof __firebase_config !== 'undefined' && __firebase_config) {
                try {
                    const firebaseConfig = JSON.parse(__firebase_config);
                    firebase.initializeApp(firebaseConfig);
                    auth = firebase.auth();
                    db = firebase.firestore();
                    const initAuth = async () => {
                        if (typeof __initial_auth_token !== 'undefined' && __initial_auth_token) {
                            await auth.signInWithCustomToken(__initial_auth_token);
                        } else {
                            await auth.signInAnonymously();
                        }
                    };
                    initAuth().then(() => {
                        document.getElementById('online-sync-indicator').classList.remove('hidden');
                        isOnlineConnected = true;
                        setupFirestoreListeners();
                    }).catch(err => console.log('Firebase fallback'));
                } catch (e) {
                    console.log('Local storage fallback');
                }
            }
        }

        function setupFirestoreListeners() {
            if (!db || !auth.currentUser) return;
            db.collection('artifacts').doc(appId).collection('public').doc('data').collection('tenants')
                .onSnapshot(snapshot => {
                    if (!snapshot.empty) {
                        tenants = snapshot.docs.map(doc => ({
                            id: doc.id,
                            ...doc.data()
                        }));
                        localStorage.setItem('pande_tenants', JSON.stringify(tenants));
                        renderTenantsGrid();
                        renderDashboard();
                    }
                });

            db.collection('artifacts').doc(appId).collection('public').doc('data').collection('bills')
                .onSnapshot(snapshot => {
                    if (!snapshot.empty) {
                        bills = snapshot.docs.map(doc => ({
                            id: doc.id,
                            ...doc.data()
                        }));
                        bills.sort((a, b) => new Date(b.createdAt) - new Date(a.createdAt));
                        localStorage.setItem('pande_bills', JSON.stringify(bills));
                        renderDashboard();
                        renderBillsTable();
                        renderHistoryView();
                    }
                });

            db.collection('artifacts').doc(appId).collection('public').doc('data').collection('settings').doc('config')
                .onSnapshot(doc => {
                    if (doc.exists) {
                        settings = doc.data();
                        localStorage.setItem('pande_settings', JSON.stringify(settings));
                        loadSettingsForm();
                    }
                });
        }

        function saveState() {
            localStorage.setItem('pande_settings', JSON.stringify(settings));
            localStorage.setItem('pande_tenants', JSON.stringify(tenants));
            localStorage.setItem('pande_bills', JSON.stringify(bills));

            if (isOnlineConnected && db && auth.currentUser) {
                try {
                    db.collection('artifacts').doc(appId).collection('public').doc('data').collection('settings').doc(
                        'config').set(settings, {
                        merge: true
                    });
                    tenants.forEach(t => db.collection('artifacts').doc(appId).collection('public').doc('data').collection(
                        'tenants').doc(t.id).set(t, {
                        merge: true
                    }));
                    bills.forEach(b => db.collection('artifacts').doc(appId).collection('public').doc('data').collection(
                        'bills').doc(b.id).set(b, {
                        merge: true
                    }));
                } catch (e) {}
            }
        }

        function formatRupiah(number) {
            return new Intl.NumberFormat('id-ID', {
                style: 'currency',
                currency: 'IDR',
                maximumFractionDigits: 0
            }).format(number || 0);
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

        function formatWaPhone(phone) {
            if (!phone) return '';
            let cleaned = phone.replace(/[^0-9]/g, '');
            if (cleaned.startsWith('0')) cleaned = '62' + cleaned.substring(1);
            return cleaned;
        }

        function switchTab(tabName) {
            const tabs = ['dashboard', 'bills', 'history', 'tenants', 'settings', 'admins'];

            const activeClass =
                'w-full text-left px-4 py-3 bg-blue-600 text-white rounded-xl flex items-center gap-3 font-bold transition shadow-md shadow-blue-500/20';
            const inactiveClass =
                'w-full text-left px-4 py-3 text-slate-500 hover:bg-slate-50 hover:text-slate-800 rounded-xl flex items-center gap-3 font-medium transition';

            tabs.forEach(t => {
                // CEK DULU: Apakah elemen halamannya ada? (Mencegah blank error untuk user biasa)
                const viewElement = document.getElementById(`view-${t}`);
                if (viewElement) {
                    viewElement.classList.add('hidden');
                }

                const navDesktop = document.getElementById(`nav-${t}`);
                if (navDesktop) {
                    navDesktop.className = (t === tabName) ? activeClass : inactiveClass;
                }
            });

            // Tampilkan halaman yang dituju (jika ada)
            const targetElement = document.getElementById(`view-${tabName}`);
            if (targetElement) {
                targetElement.classList.remove('hidden');
            }

            if (tabName === 'dashboard') renderDashboard();
            if (tabName === 'bills') renderBillsTable();
            if (tabName === 'history') renderHistoryView();
            if (tabName === 'tenants') renderTenantsGrid();
            if (tabName === 'settings') loadSettingsForm();
        }

        function openModal(id) {
            document.getElementById(id).classList.remove('hidden');
        }

        function closeModal(id) {
            document.getElementById(id).classList.add('hidden');
        }

        function openConfirmModal(title, message, onConfirm) {
            document.getElementById('confirm-title').innerText = title;
            document.getElementById('confirm-message').innerText = message;
            deleteConfirmAction = onConfirm;
            document.getElementById('confirm-action-btn').onclick = function() {
                if (deleteConfirmAction) deleteConfirmAction();
                closeModal('modal-confirm');
            };
            openModal('modal-confirm');
        }

        function resetDashboardPeriodFilter() {
            document.getElementById('dashboard-month-picker').value = '';
            renderDashboard();
        }

        function renderDashboard() {
            const selectedMonthKey = document.getElementById('dashboard-month-picker').value;
            const periodLabel = document.getElementById('dashboard-period-label');
            let filteredBills = bills;

            if (selectedMonthKey) {
                periodLabel.innerText = formatMonthKeyToIndonesian(selectedMonthKey);
                filteredBills = bills.filter(b => b.monthKey === selectedMonthKey);
            } else {
                periodLabel.innerText = 'Semua Periode';
            }

            const totalAmount = filteredBills.reduce((acc, b) => acc + b.totalAmount, 0);
            const paidAmount = filteredBills.reduce((acc, b) => acc + (b.paidAmount || 0), 0);
            const unpaidAmount = Math.max(0, totalAmount - paidAmount);
            const paidBills = filteredBills.filter(b => (b.paidAmount || 0) >= b.totalAmount && b.totalAmount > 0);
            const unpaidBills = filteredBills.filter(b => (b.paidAmount || 0) < b.totalAmount || b.totalAmount === 0);

            const targetMonthKey = selectedMonthKey || getCurrentMonthKey();
            const billedTenantIds = new Set(bills.filter(b => b.monthKey === targetMonthKey).map(b => b.tenantId));
            const unbilledCount = tenants.filter(t => !billedTenantIds.has(t.id)).length;

            document.getElementById('stat-total-amount').innerText = formatRupiah(totalAmount);
            document.getElementById('stat-total-bills-count').innerText = filteredBills.length;
            document.getElementById('stat-paid-amount').innerText = formatRupiah(paidAmount);
            document.getElementById('stat-paid-count').innerText = paidBills.length;
            document.getElementById('stat-unpaid-amount').innerText = formatRupiah(unpaidAmount);
            document.getElementById('stat-unpaid-count').innerText = unpaidBills.length;
            document.getElementById('stat-unbilled-count').innerText = unbilledCount;

            const tbody = document.getElementById('dashboard-unpaid-tbody');
            tbody.innerHTML = '';
            if (unpaidBills.length === 0) {
                tbody.innerHTML =
                    `<tr><td colspan="6" class="px-5 py-8 text-center text-slate-400">Tidak ada tagihan belum lunas. 🎉</td></tr>`;
                return;
            }

            unpaidBills.forEach(b => {
                const paid = b.paidAmount || 0;
                const remaining = Math.max(0, b.totalAmount - paid);
                tbody.innerHTML += `
                <tr class="hover:bg-slate-50 transition">
                    <td class="px-5 py-4"><div class="font-extrabold text-amber-700 text-xs">${b.noReg || '-'}</div><div class="font-bold text-slate-900">${b.tenantName}</div><div class="text-xs text-slate-500"><i class="fa-brands fa-whatsapp text-wa-light"></i> ${b.phone}</div></td>
                    <td class="px-5 py-4 font-bold text-slate-700">${b.roomNo}</td>
                    <td class="px-5 py-4 font-bold text-slate-800">${formatRupiah(b.totalAmount)}</td>
                    <td class="px-5 py-4 text-xs font-bold text-emerald-600">${formatRupiah(paid)}</td>
                    <td class="px-5 py-4 text-xs font-black text-amber-700">${formatRupiah(remaining)}</td>
                    <td class="px-5 py-4 text-center whitespace-nowrap">
                        <button onclick="openPaymentModal('${b.id}')" class="px-2.5 py-1.5 bg-blue-600 text-white rounded-lg text-xs font-bold hover:bg-blue-700 transition shadow-sm inline-flex items-center gap-1"><i class="fa-solid fa-money-bill-wave"></i> Bayar</button>
                        <button onclick="openWaPreview('${b.id}')" class="bg-wa-light text-white font-bold px-2.5 py-1.5 rounded-xl text-xs shadow-sm inline-flex items-center gap-1"><i class="fa-brands fa-whatsapp"></i> WA</button>
                    </td>
                </tr>
            `;
            });
        }

        function renderBillsTable() {
            const tbody = document.getElementById('bills-tbody');
            tbody.innerHTML = '';
            const searchKey = document.getElementById('bill-search').value.toLowerCase();
            const statusFilter = document.getElementById('bill-status-filter').value;

            const filtered = bills.filter(b => {
                const paid = b.paidAmount || 0;
                const isActuallyLunas = (paid >= b.totalAmount && b.totalAmount > 0);

                const matchSearch = (b.noReg || '').toLowerCase().includes(searchKey) || (b.roomNo || '')
                    .toLowerCase().includes(searchKey) || (b.tenantName || '').toLowerCase().includes(searchKey);
                
                const matchStatus = statusFilter === 'all' || 
                                    (statusFilter === 'Lunas' && isActuallyLunas) ||
                                    (statusFilter === 'Belum Lunas' && !isActuallyLunas);
                return matchSearch && matchStatus;
            });

            if (filtered.length === 0) {
                tbody.innerHTML =
                    `<tr><td colspan="8" class="px-5 py-8 text-center text-slate-400">Tidak ada data tagihan ditemukan.</td></tr>`;
                return;
            }

            filtered.forEach(b => {
                const waterDelta = (b.waterCurr - b.waterPrev).toFixed(1);
                const elecDelta = (b.elecCurr - b.elecPrev).toFixed(1);
                const paid = b.paidAmount || 0;
                const remaining = Math.max(0, b.totalAmount - paid);
                
                const isActuallyLunas = (paid >= b.totalAmount && b.totalAmount > 0);
                const badgeClass = isActuallyLunas ? 'bg-emerald-100 text-emerald-800' : (paid > 0 ?
                    'bg-blue-100 text-blue-800' : 'bg-amber-100 text-amber-800');
                const statusLabel = isActuallyLunas ? 'Lunas' : (paid > 0 ? 'Dicicil' : 'Belum Lunas');

                tbody.innerHTML += `
                <tr class="hover:bg-slate-50 transition">
                    <td class="px-5 py-4 font-mono font-extrabold text-amber-700 text-xs">${b.noReg || '-'}</td>
                    <td class="px-5 py-4"><div class="font-bold text-slate-900">${b.tenantName}</div><div class="text-xs text-slate-500">${b.roomNo} &bull; ${b.phone}</div></td>
                    <td class="px-5 py-4 text-xs font-semibold text-slate-600">${b.period}</td>
                    <td class="px-5 py-4 text-xs"><div>Air: <span class="font-bold text-blue-700">${waterDelta} m³</span></div><div>Listrik: <span class="font-bold text-amber-700">${elecDelta} kWh</span></div></td>
                    <td class="px-5 py-4 text-xs"><div class="font-bold text-slate-900">${formatRupiah(b.totalAmount)}</div><div class="text-emerald-600 font-semibold">Dibayar: ${formatRupiah(paid)}</div></td>
                    <td class="px-5 py-4 font-black text-amber-700 text-xs">${formatRupiah(remaining)}</td>
                    <td class="px-5 py-4"><span class="px-2.5 py-1 rounded-full text-[10px] font-extrabold ${badgeClass}">${statusLabel}</span></td>
                    <td class="px-5 py-4 text-right whitespace-nowrap space-x-1">
                        <button onclick="openPaymentModal('${b.id}')" class="px-2.5 py-1.5 bg-blue-600 text-white rounded-lg text-xs font-bold hover:bg-blue-700 transition shadow-sm"><i class="fa-solid fa-money-bill-wave"></i> Bayar</button>
                        <button onclick="openWaPreview('${b.id}')" class="p-2 bg-emerald-50 text-emerald-600 rounded-lg text-xs"><i class="fa-brands fa-whatsapp"></i></button>
                        <button onclick="openInvoiceModal('${b.id}')" class="p-2 bg-slate-100 text-slate-600 rounded-lg text-xs"><i class="fa-solid fa-print"></i></button>
                        <button onclick="openGenerateModal('${b.id}')" class="p-2 bg-blue-50 text-blue-600 rounded-lg text-xs"><i class="fa-solid fa-pen"></i></button>
                        <button onclick="deleteBill('${b.id}')" class="p-2 bg-red-50 text-red-600 rounded-lg text-xs"><i class="fa-solid fa-trash"></i></button>
                    </td>
                </tr>
            `;
            });
        }

        function renderHistoryView() {
            let selectedMonthKey = document.getElementById('audit-month-picker').value || getCurrentMonthKey();
            document.getElementById('audit-month-picker').value = selectedMonthKey;

            const unbilledTbody = document.getElementById('unbilled-tenants-tbody');
            unbilledTbody.innerHTML = '';
            const billedTenantIds = new Set(bills.filter(b => b.monthKey === selectedMonthKey).map(b => b.tenantId));
            const unbilledTenants = tenants.filter(t => !billedTenantIds.has(t.id));

            if (unbilledTenants.length === 0) {
                unbilledTbody.innerHTML =
                    `<tr><td colspan="6" class="px-5 py-6 text-center text-slate-400">Semua pelanggan terdaftar sudah dibuatkan tagihan pada periode ${formatMonthKeyToIndonesian(selectedMonthKey)}! 🎉</td></tr>`;
            } else {
                unbilledTenants.forEach(t => {
                    unbilledTbody.innerHTML += `
                    <tr class="hover:bg-slate-50 transition">
                        <td class="px-5 py-3.5 font-mono font-extrabold text-amber-700 text-xs">${t.noReg || '-'}</td>
                        <td class="px-5 py-3.5 font-bold text-slate-900">${t.name}</td>
                        <td class="px-5 py-3.5 text-xs text-slate-600 font-semibold">${t.roomNo}</td>
                        <td class="px-5 py-3.5 text-xs text-slate-600"><i class="fa-brands fa-whatsapp text-wa-light"></i> ${t.phone}</td>
                        <td class="px-5 py-3.5 text-center"><span class="px-2.5 py-1 text-[10px] font-bold bg-red-100 text-red-700 rounded-full">Belum Dicatat</span></td>
                        <td class="px-5 py-3.5 text-right"><button onclick="openGenerateModalForTenant('${t.id}', '${selectedMonthKey}')" class="bg-blue-600 hover:bg-blue-700 text-white font-bold px-3 py-1.5 rounded-xl text-xs shadow-sm"><i class="fa-solid fa-plus-circle"></i> Buat Tagihan</button></td>
                    </tr>
                `;
                });
            }

            const unpaidTbody = document.getElementById('history-unpaid-tbody');
            unpaidTbody.innerHTML = '';
            const unpaidBills = bills.filter(b => (b.paidAmount || 0) < b.totalAmount || b.totalAmount === 0);
            if (unpaidBills.length === 0) {
                unpaidTbody.innerHTML =
                    `<tr><td colspan="7" class="px-5 py-6 text-center text-slate-400">Luar biasa! Tidak ada tunggakan kewajiban saat ini. 👏</td></tr>`;
            } else {
                unpaidBills.forEach(b => {
                    const remaining = Math.max(0, b.totalAmount - (b.paidAmount || 0));
                    unpaidTbody.innerHTML += `
                    <tr class="hover:bg-slate-50 transition">
                        <td class="px-5 py-3.5 font-mono font-extrabold text-amber-700 text-xs">${b.noReg || '-'}</td>
                        <td class="px-5 py-3.5"><div class="font-bold text-slate-900">${b.tenantName}</div><div class="text-xs text-slate-500">${b.roomNo} &bull; ${b.phone}</div></td>
                        <td class="px-5 py-3.5 text-xs font-semibold text-slate-700">${b.period}</td>
                        <td class="px-5 py-3.5 text-xs text-slate-600 space-y-0.5"><div>Total: ${formatRupiah(b.totalAmount)}</div><div class="text-emerald-600">Dibayar: ${formatRupiah(b.paidAmount || 0)}</div></td>
                        <td class="px-5 py-3.5 font-black text-amber-700">${formatRupiah(remaining)}</td>
                        <td class="px-5 py-3.5 text-xs text-red-600 font-semibold">${b.dueDate || '-'}</td>
                        <td class="px-5 py-3.5 text-center space-x-1">
                            <button onclick="openPaymentModal('${b.id}')" class="px-2.5 py-1.5 bg-blue-600 text-white rounded-lg text-xs font-bold hover:bg-blue-700 transition shadow-sm"><i class="fa-solid fa-money-bill-wave"></i> Bayar</button>
                            <button onclick="openWaPreview('${b.id}')" class="bg-wa-light text-white font-bold px-2.5 py-1 rounded-xl text-xs shadow-sm"><i class="fa-brands fa-whatsapp"></i> WA</button>
                        </td>
                    </tr>
                `;
                });
            }
        }

        function renderTenantsGrid() {
            const grid = document.getElementById('tenants-grid');
            grid.innerHTML = '';
            if (tenants.length === 0) {
                grid.innerHTML =
                    `<div class="col-span-full py-12 text-center text-slate-400">Belum ada pelanggan terdaftar. Klik Tambah Pelanggan.</div>`;
                return;
            }
            tenants.forEach(t => {
                grid.innerHTML += `
                <div class="bg-white p-5 rounded-3xl border border-slate-200/80 shadow-sm space-y-4">
                    <div class="flex justify-between items-start">
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="bg-amber-100 text-amber-900 text-xs font-mono font-black px-2.5 py-0.5 rounded-lg border border-amber-300">${t.noReg || 'REG-000'}</span>
                            </div>
                            <h3 class="text-lg font-bold text-slate-900 mt-2">${t.name}</h3>
                            <p class="text-xs text-slate-500 mt-1 font-semibold"><i class="fa-brands fa-whatsapp text-wa-light text-sm"></i> ${t.phone}</p>
                        </div>
                    </div>
                    <div class="bg-slate-50 p-3 rounded-2xl grid grid-cols-2 gap-2 text-xs">
                        <div><span class="text-slate-400 block font-medium">Air Baseline</span><span class="font-bold text-slate-800">${t.lastWater || 0} m³</span></div>
                        <div><span class="text-slate-400 block font-medium">Listrik Baseline</span><span class="font-bold text-slate-800">${t.lastElec || 0} kWh</span></div>
                    </div>
                    <div class="flex justify-between items-center pt-2 border-t border-slate-100">
                        <button onclick="openGenerateModalForTenant('${t.id}')" class="text-xs font-extrabold text-blue-600 hover:text-blue-700 flex items-center gap-1"><i class="fa-solid fa-plus-circle"></i> Catat Tagihan</button>
                        <div class="space-x-1 flex items-center">
                            <button onclick="openTenantModal('${t.id}')" class="p-1.5 text-slate-400 hover:text-blue-600 text-xs"><i class="fa-solid fa-pen"></i></button>
                            <button onclick="deleteTenant('${t.id}')" class="p-1.5 text-slate-400 hover:text-red-600 text-xs"><i class="fa-solid fa-trash"></i></button>
                        </div>
                    </div>
                </div>
            `;
            });
        }

        function openPaymentModal(billId) {
            const bill = bills.find(b => b.id === billId);
            if (!bill) return;
            activePayBill = bill;
            const paid = bill.paidAmount || 0;
            const remaining = Math.max(0, bill.totalAmount - paid);

            document.getElementById('pay-bill-id').value = bill.id;
            document.getElementById('pay-tenant-name').innerText = bill.tenantName;
            document.getElementById('pay-noreg-room').innerText = `${bill.noReg} (${bill.roomNo})`;
            document.getElementById('pay-period').innerText = bill.period;
            document.getElementById('pay-bill-total').innerText = formatRupiah(bill.totalAmount);
            document.getElementById('pay-already-paid').innerText = formatRupiah(paid);
            document.getElementById('pay-remaining-amount').innerText = formatRupiah(remaining);

            document.getElementById('pay-amount-input').value = remaining > 0 ? remaining : '';
            document.getElementById('pay-date-input').value = new Date().toISOString().split('T')[0];
            document.getElementById('pay-note-input').value = '';

            openModal('modal-pay');
        }

        function setPayAmountFull() {
            if (!activePayBill) return;
            document.getElementById('pay-amount-input').value = Math.max(0, activePayBill.totalAmount - (activePayBill
                .paidAmount || 0));
        }

        function handleSavePayment(e) {
            e.preventDefault();
            const billId = document.getElementById('pay-bill-id').value;
            const currentBill = bills.find(b => b.id === billId);
            if (!currentBill) return;

            const inputAmount = parseFloat(document.getElementById('pay-amount-input').value) || 0;
            const method = document.getElementById('pay-method-select').value;
            const date = document.getElementById('pay-date-input').value;
            const note = document.getElementById('pay-note-input').value.trim();

            if (inputAmount <= 0) {
                showToast('Nominal harus lebih dari 0', 'error');
                return;
            }

            let remainingAmount = inputAmount;

            // 1. SISTEM DISTRIBUSI OTOMATIS KE TUNGGAKAN LAMA (SMART PAYMENT)
            // Cari tagihan bulan sebelumnya yang belum lunas
            const pastUnpaidBills = bills.filter(b => 
                b.noReg === currentBill.noReg && 
                b.monthKey < currentBill.monthKey && 
                b.status !== 'Lunas'
            ).sort((a, b) => a.monthKey.localeCompare(b.monthKey)); 

            pastUnpaidBills.forEach(pastBill => {
                if (remainingAmount > 0) {
                    const pastShortfall = pastBill.totalAmount - (pastBill.paidAmount || 0);
                    
                    if (pastShortfall > 0) {
                        // Ambil uang secukupnya untuk melunasi tagihan lama
                        const amountToApply = Math.min(pastShortfall, remainingAmount);

                        if (!pastBill.payments) pastBill.payments = [];
                        pastBill.payments.push({
                            id: 'pay-' + Date.now() + Math.floor(Math.random() * 1000),
                            amount: amountToApply,
                            method: method,
                            date: date,
                            note: 'Pelunasan otomatis dari pembayaran ' + currentBill.period
                        });
                        
                        pastBill.paidAmount = (pastBill.paidAmount || 0) + amountToApply;
                        pastBill.status = (pastBill.paidAmount >= pastBill.totalAmount) ? 'Lunas' : 'Belum Lunas';
                        
                        // Kurangi sisa uang di tangan kasir
                        remainingAmount -= amountToApply;
                        
                        // Hapus angka tunggakan dari tagihan bulan ini agar bersih
                        currentBill.arrearsFee -= amountToApply;
                        currentBill.totalAmount -= amountToApply;
                    }
                }
            });

            // 2. MASUKKAN SISA UANG KE TAGIHAN SAAT INI
            if (remainingAmount > 0) {
                if (!currentBill.payments) currentBill.payments = [];
                currentBill.payments.push({
                    id: 'pay-' + Date.now(),
                    amount: remainingAmount,
                    method: method,
                    date: date,
                    note: note
                });
                currentBill.paidAmount = (currentBill.paidAmount || 0) + remainingAmount;
            }
            
            // Perbarui status tagihan saat ini
            currentBill.status = (currentBill.paidAmount >= currentBill.totalAmount) ? 'Lunas' : 'Belum Lunas';

            saveState();
            closeModal('modal-pay');
            renderDashboard();
            renderBillsTable();
            renderHistoryView();
            showToast(`Pembayaran berhasil dicatat & didistribusikan otomatis!`);
            
            // Tampilkan WA Preview dengan total pembayaran asli
            const displayPayment = { amount: inputAmount, method: method, date: date, note: note };
            openWaThankYouPreview(currentBill, displayPayment);
        }   

        function openWaThankYouPreview(bill, paymentRecord) {
            const formattedPhone = formatWaPhone(bill.phone);
            const remaining = Math.max(0, bill.totalAmount - bill.paidAmount);
            const statusText = remaining === 0 ? '*LUNAS* ✅' : `*SISA KEWAJIBAN: ${formatRupiah(remaining)}*`;

            const thankYouMsg =
                `Yth. Bpk/Ibu *${bill.tenantName}* (${bill.noReg} / ${bill.roomNo}),\n\nTerima kasih! Pembayaran Anda untuk tagihan kos/utilitas periode *${bill.period}* telah kami terima dengan rincian berikut:\n\n• Jumlah Dibayar: *${formatRupiah(paymentRecord.amount)}*\n• Metode Pembayaran: ${paymentRecord.method}\n• Tanggal Terima: ${paymentRecord.date}\n• Catatan / Ref: ${paymentRecord.note || '-'}\n-----------------------------------------\n• Total Tagihan Periode Ini: ${formatRupiah(bill.totalAmount)}\n• Total Pembayaran Diterima: ${formatRupiah(bill.paidAmount)}\n• Status Pembayaran: ${statusText}\n\nTerima kasih atas kerja samanya. Bukti pembayaran resmi ini sah diterbitkan oleh *${settings.ownerName}*. 🙏`;

            document.getElementById('wa-recipient-info').innerText =
                `${bill.tenantName} (${bill.noReg}) ➔ WA: +${formattedPhone}`;
            document.getElementById('wa-preview-text').value = thankYouMsg;
            document.getElementById('wa-send-btn').setAttribute('href',
                `https://wa.me/${formattedPhone}?text=${encodeURIComponent(thankYouMsg)}`);
            openModal('modal-wa-preview');
        }

        function openTenantModal(tenantId = null) {
            document.getElementById('form-tenant').reset();
            document.getElementById('tenant-id').value = '';
            document.getElementById('modal-tenant-title').innerText = 'Tambah Data Pelanggan';
            document.getElementById('btn-delete-tenant-in-modal').classList.add('hidden');

            if (tenantId) {
                const t = tenants.find(x => x.id === tenantId);
                if (t) {
                    document.getElementById('modal-tenant-title').innerText = 'Edit Data Pelanggan';
                    document.getElementById('tenant-id').value = t.id;
                    document.getElementById('tenant-noreg').value = t.noReg || '';
                    document.getElementById('tenant-room').value = t.roomNo;
                    document.getElementById('tenant-name').value = t.name;
                    document.getElementById('tenant-phone').value = t.phone;
                    document.getElementById('tenant-rent').value = t.baseRent;
                    document.getElementById('tenant-water-last').value = t.lastWater || 0;
                    document.getElementById('tenant-elec-last').value = t.lastElec || 0;
                    document.getElementById('btn-delete-tenant-in-modal').classList.remove('hidden');
                }
            } else {
                document.getElementById('tenant-noreg').value = 'REG-' + (100 + tenants.length + 1);
            }
            openModal('modal-tenant');
        }

        function handleSaveTenant(e) {
            e.preventDefault();
            const id = document.getElementById('tenant-id').value || 't-' + Date.now();
            const tenantObj = {
                id,
                noReg: document.getElementById('tenant-noreg').value.trim().toUpperCase(),
                roomNo: document.getElementById('tenant-room').value.trim(),
                name: document.getElementById('tenant-name').value.trim(),
                phone: document.getElementById('tenant-phone').value.trim(),
                baseRent: parseFloat(document.getElementById('tenant-rent').value) || 0,
                lastWater: parseFloat(document.getElementById('tenant-water-last').value) || 0,
                lastElec: parseFloat(document.getElementById('tenant-elec-last').value) || 0
            };

            const existingIndex = tenants.findIndex(t => t.id === id);
            if (existingIndex >= 0) {
                tenants[existingIndex] = tenantObj;
                showToast('Data pelanggan diperbarui');
            } else {
                tenants.push(tenantObj);
                showToast('Pelanggan baru ditambahkan');
            }

            saveState();
            closeModal('modal-tenant');
            renderTenantsGrid();
            renderDashboard();
            renderHistoryView();
        }

        function requestDeleteTenantFromModal() {
            const tenantId = document.getElementById('tenant-id').value;
            if (tenantId) {
                closeModal('modal-tenant');
                deleteTenant(tenantId);
            }
        }

        function deleteTenant(id) {
            const tenant = tenants.find(t => t.id === id);
            if (!tenant) return;
            openConfirmModal('Hapus Data Pelanggan?', `Yakin ingin menghapus ${tenant.noReg} (${tenant.name})?`,
                function() {
                    tenants = tenants.filter(t => t.id !== id);
                    if (isOnlineConnected && db && auth.currentUser) db.collection('artifacts').doc(appId).collection(
                        'public').doc('data').collection('tenants').doc(id).delete();
                    saveState();
                    renderTenantsGrid();
                    renderDashboard();
                    renderHistoryView();
                    showToast('Pelanggan dihapus!');
                });
        }

        function getLatestPrevMeterByNoReg(noReg, currentMonthKey, tenantId) {
            const tenant = tenants.find(t => t.id === tenantId || t.noReg === noReg);
            const previousBills = bills.filter(b => (b.noReg === noReg || b.tenantId === tenantId) && b.monthKey <
                currentMonthKey).sort((a, b) => a.monthKey.localeCompare(b.monthKey));
            if (previousBills.length > 0) {
                const lastBill = previousBills[previousBills.length - 1];
                return {
                    waterPrev: lastBill.waterCurr,
                    elecPrev: lastBill.elecCurr
                };
            }
            return {
                waterPrev: tenant ? (tenant.lastWater || 0) : 0,
                elecPrev: tenant ? (tenant.lastElec || 0) : 0
            };
        }

        function getUnpaidArrearsByNoReg(noReg, currentMonthKey, currentBillId = null) {
            const unpaidPriorBills = bills.filter(b => b.noReg === noReg && b.monthKey < currentMonthKey && b.status !==
                'Lunas' && b.id !== currentBillId);
            const totalArrears = unpaidPriorBills.reduce((acc, b) => acc + Math.max(0, b.totalAmount - (b.paidAmount || 0)),
                0);
            return {
                totalArrears,
                periodNames: unpaidPriorBills.map(b => b.period).join(', ')
            };
        }

        function openGenerateModal(billId = null, initialMonthKey = null) {
            document.getElementById('form-bill').reset();
            document.getElementById('bill-id').value = '';
            document.getElementById('modal-bill-title').innerText = 'Catat Tagihan Bulanan';
            document.getElementById('selected-tenant-info').classList.add('hidden');

            const select = document.getElementById('bill-tenant-id');
            select.innerHTML = '<option value="">-- Pilih Pelanggan / No. Reg --</option>';
            tenants.forEach(t => select.innerHTML +=
                `<option value="${t.id}">${t.noReg || 'REG'} - ${t.name} (${t.roomNo})</option>`);

            const targetMonthKey = initialMonthKey || getCurrentMonthKey();
            document.getElementById('bill-month-picker').value = targetMonthKey;
            document.getElementById('bill-period').value = formatMonthKeyToIndonesian(targetMonthKey);

            const [year, month] = targetMonthKey.split('-');
            document.getElementById('bill-due-date').value = new Date(parseInt(year), parseInt(month) - 1, 10).toISOString()
                .split('T')[0];

            document.getElementById('bill-water-price').value = settings.defaultRateWater;
            document.getElementById('bill-elec-price').value = settings.defaultRateElec;

            if (billId) {
                const b = bills.find(x => x.id === billId);
                if (b) {
                    document.getElementById('modal-bill-title').innerText = 'Edit Tagihan Bulanan';
                    document.getElementById('bill-id').value = b.id;
                    document.getElementById('bill-tenant-id').value = b.tenantId;
                    document.getElementById('bill-month-picker').value = b.monthKey || targetMonthKey;
                    document.getElementById('bill-period').value = b.period;
                    document.getElementById('bill-room-rent').value = b.roomRent;
                    document.getElementById('bill-water-prev').value = b.waterPrev;
                    document.getElementById('bill-water-curr').value = b.waterCurr;
                    document.getElementById('bill-water-price').value = b.waterPrice;
                    document.getElementById('bill-elec-prev').value = b.elecPrev;
                    document.getElementById('bill-elec-curr').value = b.elecCurr;
                    document.getElementById('bill-elec-price').value = b.elecPrice;
                    document.getElementById('bill-extra-fee').value = b.extraFee || 0;
                    document.getElementById('bill-extra-note').value = b.extraNote || '';
                    document.getElementById('bill-arrears-fee').value = b.arrearsFee || 0;
                    document.getElementById('bill-due-date').value = b.dueDate || '';
                    onBillTenantSelect(false);
                }
            }
            calculateBillTotals();
            openModal('modal-bill');
        }

        function openGenerateModalForTenant(tenantId, monthKey = null) {
            openGenerateModal(null, monthKey);
            document.getElementById('bill-tenant-id').value = tenantId;
            onBillTenantSelect();
        }

        function onBillTenantSelect(autoFillReadings = true) {
            const tenantId = document.getElementById('bill-tenant-id').value;
            const t = tenants.find(x => x.id === tenantId);
            if (!t) {
                document.getElementById('selected-tenant-info').classList.add('hidden');
                return;
            }

            document.getElementById('bill-info-noreg').innerText = t.noReg || 'REG-000';
            document.getElementById('bill-info-room').innerText = `(${t.roomNo})`;
            document.getElementById('bill-info-wa').innerText = t.phone;
            document.getElementById('selected-tenant-info').classList.remove('hidden');
            document.getElementById('bill-room-rent').value = t.baseRent;

            if (autoFillReadings) updatePreviousMeterReadings();
            else calculateBillTotals();
        }

        function onBillPeriodChange() {
            document.getElementById('bill-period').value = formatMonthKeyToIndonesian(document.getElementById(
                'bill-month-picker').value);
            updatePreviousMeterReadings();
        }

        function updatePreviousMeterReadings() {
            const tenantId = document.getElementById('bill-tenant-id').value;
            const monthKey = document.getElementById('bill-month-picker').value;
            if (!tenantId || !monthKey) return;

            const t = tenants.find(x => x.id === tenantId);
            const prevMeters = getLatestPrevMeterByNoReg(t ? t.noReg : '', monthKey, tenantId);

            document.getElementById('bill-water-prev').value = prevMeters.waterPrev;
            document.getElementById('bill-elec-prev').value = prevMeters.elecPrev;

            const currentWater = parseFloat(document.getElementById('bill-water-curr').value) || 0;
            const currentElec = parseFloat(document.getElementById('bill-elec-curr').value) || 0;
            if (currentWater <= prevMeters.waterPrev) document.getElementById('bill-water-curr').value = (prevMeters
                .waterPrev + 5).toFixed(1);
            if (currentElec <= prevMeters.elecPrev) document.getElementById('bill-elec-curr').value = (prevMeters.elecPrev +
                40).toFixed(1);

            const arrearsData = getUnpaidArrearsByNoReg(t ? t.noReg : '', monthKey, document.getElementById('bill-id')
                .value);
            document.getElementById('bill-arrears-fee').value = arrearsData.totalArrears;
            document.getElementById('bill-arrears-periods-txt').innerText = arrearsData.totalArrears > 0 ?
                `Menarik sisa kewajiban dari periode: ${arrearsData.periodNames}` :
                `Tidak ada sisa tunggakan dari bulan-bulan sebelumnya.`;

            calculateBillTotals();
        }

        function calculateBillTotals() {
            const roomRent = parseFloat(document.getElementById('bill-room-rent').value) || 0;
            const wPrev = parseFloat(document.getElementById('bill-water-prev').value) || 0;
            const wCurr = parseFloat(document.getElementById('bill-water-curr').value) || 0;
            const wPrice = parseFloat(document.getElementById('bill-water-price').value) || 0;
            const wDelta = Math.max(0, wCurr - wPrev);
            const wTotal = wDelta * wPrice;

            const ePrev = parseFloat(document.getElementById('bill-elec-prev').value) || 0;
            const eCurr = parseFloat(document.getElementById('bill-elec-curr').value) || 0;
            const ePrice = parseFloat(document.getElementById('bill-elec-price').value) || 0;
            const eDelta = Math.max(0, eCurr - ePrev);
            const eTotal = eDelta * ePrice;

            const extraFee = parseFloat(document.getElementById('bill-extra-fee').value) || 0;
            const arrearsFee = parseFloat(document.getElementById('bill-arrears-fee').value) || 0;
            const grandTotal = roomRent + wTotal + eTotal + extraFee + arrearsFee;

            document.getElementById('water-subtotal-txt').innerText =
                `Subtotal: ${formatRupiah(wTotal)} (${wDelta.toFixed(1)} m³)`;
            document.getElementById('elec-subtotal-txt').innerText =
                `Subtotal: ${formatRupiah(eTotal)} (${eDelta.toFixed(1)} kWh)`;
            document.getElementById('bill-arrears-subtotal-txt').innerText = formatRupiah(arrearsFee);
            document.getElementById('bill-grand-total').innerText = formatRupiah(grandTotal);

            return {
                wTotal,
                eTotal,
                arrearsFee,
                grandTotal,
                wDelta,
                eDelta
            };
        }

        function handleSaveBill(e) {
            e.preventDefault();
            const tenantId = document.getElementById('bill-tenant-id').value;
            const tenant = tenants.find(t => t.id === tenantId);
            if (!tenant) {
                showToast('Silakan pilih pelanggan terlebih dahulu!', 'error');
                return;
            }

            const calc = calculateBillTotals();
            const id = document.getElementById('bill-id').value || 'b-' + Date.now();
            const existingBill = bills.find(b => b.id === id);

            const currentPaid = existingBill ? (existingBill.paidAmount || 0) : 0;
            const autoStatus = (currentPaid >= calc.grandTotal && calc.grandTotal > 0) ? 'Lunas' : 'Belum Lunas';

            const billObj = {
                id,
                tenantId: tenant.id,
                noReg: tenant.noReg,
                roomNo: tenant.roomNo,
                tenantName: tenant.name,
                phone: tenant.phone,
                monthKey: document.getElementById('bill-month-picker').value,
                period: document.getElementById('bill-period').value.trim(),
                waterPrev: parseFloat(document.getElementById('bill-water-prev').value) || 0,
                waterCurr: parseFloat(document.getElementById('bill-water-curr').value) || 0,
                waterPrice: parseFloat(document.getElementById('bill-water-price').value) || 0,
                waterTotal: calc.wTotal,
                elecPrev: parseFloat(document.getElementById('bill-elec-prev').value) || 0,
                elecCurr: parseFloat(document.getElementById('bill-elec-curr').value) || 0,
                elecPrice: parseFloat(document.getElementById('bill-elec-price').value) || 0,
                elecTotal: calc.eTotal,
                roomRent: parseFloat(document.getElementById('bill-room-rent').value) || 0,
                extraFee: parseFloat(document.getElementById('bill-extra-fee').value) || 0,
                extraNote: document.getElementById('bill-extra-note').value.trim(),
                arrearsFee: parseFloat(document.getElementById('bill-arrears-fee').value) || 0,
                
                totalAmount: calc.grandTotal,
                paidAmount: currentPaid,
                payments: existingBill ? (existingBill.payments || []) : [],
                status: autoStatus,
                dueDate: document.getElementById('bill-due-date').value,
                createdAt: new Date().toISOString()
            };

            const existingIdx = bills.findIndex(b => b.id === id);
            if (existingIdx >= 0) bills[existingIdx] = billObj;
            else bills.unshift(billObj);

            if (billObj.waterCurr > (tenant.lastWater || 0)) tenant.lastWater = billObj.waterCurr;
            if (billObj.elecCurr > (tenant.lastElec || 0)) tenant.lastElec = billObj.elecCurr;

            saveState();
            closeModal('modal-bill');
            renderBillsTable();
            renderDashboard();
            renderTenantsGrid();
            renderHistoryView();
            showToast('Tagihan berhasil disimpan & tersinkronisasi!');
        }

        function deleteBill(id) {
            const bill = bills.find(b => b.id === id);
            if (!bill) return;
            openConfirmModal('Hapus Tagihan?', `Yakin ingin menghapus catatan tagihan ${bill.noReg} (${bill.period})?`,
                function() {
                    bills = bills.filter(b => b.id !== id);
                    if (isOnlineConnected && db && auth.currentUser) db.collection('artifacts').doc(appId).collection(
                        'public').doc('data').collection('bills').doc(id).delete();
                    saveState();
                    renderBillsTable();
                    renderDashboard();
                    renderHistoryView();
                    showToast('Tagihan dihapus!');
                });
        }

        function openWaPreview(billId) {
            const bill = bills.find(b => b.id === billId);
            if (!bill) return;
            const formattedPhone = formatWaPhone(bill.phone);
            const currentBillRemaining = Math.max(0, bill.totalAmount - (bill.paidAmount || 0));

            document.getElementById('wa-recipient-info').innerText =
                `${bill.tenantName} (${bill.noReg} / ${bill.roomNo}) ➔ WA: +${formattedPhone}`;

            let msg = settings.waTemplate;
            msg = msg.replace(/{noreg}/g, bill.noReg || '-').replace(/{nama}/g, bill.tenantName).replace(/{kamar}/g, bill
                    .roomNo).replace(/{periode}/g, bill.period)
                .replace(/{sewa}/g, bill.roomRent.toLocaleString('id-ID')).replace(/{air_awal}/g, bill.waterPrev).replace(
                    /{air_akhir}/g, bill.waterCurr).replace(/{air_m3}/g, (bill.waterCurr - bill.waterPrev).toFixed(1))
                .replace(/{air}/g, bill.waterTotal.toLocaleString('id-ID'))
                .replace(/{listrik_awal}/g, bill.elecPrev).replace(/{listrik_akhir}/g, bill.elecCurr).replace(
                    /{listrik_kwh}/g, (bill.elecCurr - bill.elecPrev).toFixed(1)).replace(/{listrik}/g, bill.elecTotal
                    .toLocaleString('id-ID'))
                .replace(/{biaya_lain}/g, (bill.extraFee || 0).toLocaleString('id-ID')).replace(/{total_periode}/g, (bill
                    .roomRent + bill.waterTotal + bill.elecTotal + (bill.extraFee || 0)).toLocaleString('id-ID'))
                .replace(/{tunggakan}/g, (bill.arrearsFee || 0).toLocaleString('id-ID')).replace(/{total}/g,
                    currentBillRemaining.toLocaleString('id-ID')).replace(/{jatuh_tempo}/g, bill.dueDate || 'Segera')
                .replace(/{bank}/g, settings.bankName).replace(/{rekening}/g, settings.accountNumber).replace(/{pemilik}/g,
                    settings.accountHolder);

            document.getElementById('wa-preview-text').value = msg;
            document.getElementById('wa-send-btn').setAttribute('href',
                `https://wa.me/${formattedPhone}?text=${encodeURIComponent(msg)}`);
            openModal('modal-wa-preview');
        }

        function copyWaText() {
            const copyText = document.getElementById('wa-preview-text');
            copyText.select();
            document.execCommand('copy');
            showToast('Teks pesan WhatsApp berhasil disalin!');
        }

        function openInvoiceModal(billId) {
            const b = bills.find(x => x.id === billId);
            if (!b) return;

            document.getElementById('inv-owner-name').innerText = settings.ownerName.toUpperCase();
            document.getElementById('inv-date').innerText = `Dibuat: ${new Date(b.createdAt).toLocaleDateString('id-ID')}`;
            document.getElementById('inv-no-reg').innerText = b.noReg || 'REG-000';
            document.getElementById('inv-tenant-name').innerText = b.tenantName;
            document.getElementById('inv-room-no').innerText = b.roomNo;
            document.getElementById('inv-phone').innerText = `WA: ${b.phone}`;
            document.getElementById('inv-period').innerText = b.period;
            document.getElementById('inv-due-date').innerText = `Jatuh Tempo: ${b.dueDate || '-'}`;

            const isActuallyLunas = (b.paidAmount >= b.totalAmount && b.totalAmount > 0);
            const badge = document.getElementById('inv-status-badge');
            badge.innerText = isActuallyLunas ? 'LUNAS' : 'BELUM LUNAS';
            badge.className = isActuallyLunas ?
                'px-3 py-1 text-xs font-bold rounded-full bg-emerald-100 text-emerald-800' :
                'px-3 py-1 text-xs font-bold rounded-full bg-amber-100 text-amber-800';

            let itemsHtml = `
            <tr><td class="p-2.5 font-medium">Sewa Kamar (${b.period})</td><td class="p-2.5 text-center text-slate-500">1 Bulan</td><td class="p-2.5 text-right font-bold">${formatRupiah(b.roomRent)}</td></tr>
            <tr><td class="p-2.5 font-medium">Pemakaian Air (${b.waterPrev} &rarr; ${b.waterCurr})</td><td class="p-2.5 text-center text-slate-500">${(b.waterCurr - b.waterPrev).toFixed(1)} m³</td><td class="p-2.5 text-right font-bold">${formatRupiah(b.waterTotal)}</td></tr>
            <tr><td class="p-2.5 font-medium">Pemakaian Listrik (${b.elecPrev} &rarr; ${b.elecCurr})</td><td class="p-2.5 text-center text-slate-500">${(b.elecCurr - b.elecPrev).toFixed(1)} kWh</td><td class="p-2.5 text-right font-bold">${formatRupiah(b.elecTotal)}</td></tr>
        `;
            if (b.extraFee > 0) itemsHtml +=
                `<tr><td class="p-2.5 font-medium">Biaya Tambahan (${b.extraNote || 'Lain-lain'})</td><td class="p-2.5 text-center text-slate-500">1x</td><td class="p-2.5 text-right font-bold">${formatRupiah(b.extraFee)}</td></tr>`;
            if (b.arrearsFee > 0) itemsHtml +=
                `<tr class="bg-amber-50 text-amber-900 font-semibold"><td class="p-2.5"><div class="font-bold flex items-center gap-1.5"><i class="fa-solid fa-clock-rotate-left text-amber-600"></i> Sisa Tunggakan Bulan-bulan Lalu</div></td><td class="p-2.5 text-center text-amber-700 font-bold">Akumulasi</td><td class="p-2.5 text-right font-extrabold text-amber-900">${formatRupiah(b.arrearsFee)}</td></tr>`;

            document.getElementById('inv-items-tbody').innerHTML = itemsHtml;

            const payBox = document.getElementById('inv-payment-history-box');
            const payItems = document.getElementById('inv-payment-history-items');
            if (b.payments && b.payments.length > 0) {
                payBox.classList.remove('hidden');
                payItems.innerHTML = '';
                b.payments.forEach((p, idx) => {
                    payItems.innerHTML +=
                        `<div class="flex justify-between items-center text-[11px]"><span>#${idx+1} ${p.date} (${p.method}) ${p.note ? `- ${p.note}` : ''}</span><span class="font-bold text-emerald-700">+ ${formatRupiah(p.amount)}</span></div>`;
                });
            } else {
                payBox.classList.add('hidden');
            }

            document.getElementById('inv-total-amount').innerText = formatRupiah(b.totalAmount);
            document.getElementById('inv-paid-amount').innerText = formatRupiah(b.paidAmount || 0);
            document.getElementById('inv-remaining-amount').innerText = formatRupiah(Math.max(0, b.totalAmount - (b
                .paidAmount || 0)));
            document.getElementById('inv-payment-bank').innerText = `Bank: ${settings.bankName}`;
            document.getElementById('inv-payment-no').innerText = `No. Rek: ${settings.accountNumber}`;
            document.getElementById('inv-payment-holder').innerText = `A.n: ${settings.accountHolder}`;

            openModal('modal-invoice');
        }

        function loadSettingsForm() {
            document.getElementById('cfg-owner-name').value = settings.ownerName;
            document.getElementById('cfg-bank-name').value = settings.bankName;
            document.getElementById('cfg-account-no').value = settings.accountNumber;
            document.getElementById('cfg-account-holder').value = settings.accountHolder;
            document.getElementById('cfg-rate-water').value = settings.defaultRateWater;
            document.getElementById('cfg-rate-elec').value = settings.defaultRateElec;
            document.getElementById('cfg-wa-template').value = settings.waTemplate;
        }

        function saveSettings() {
            settings.ownerName = document.getElementById('cfg-owner-name').value.trim();
            settings.bankName = document.getElementById('cfg-bank-name').value.trim();
            settings.accountNumber = document.getElementById('cfg-account-no').value.trim();
            settings.accountHolder = document.getElementById('cfg-account-holder').value.trim();
            settings.defaultRateWater = parseFloat(document.getElementById('cfg-rate-water').value) || 0;
            settings.defaultRateElec = parseFloat(document.getElementById('cfg-rate-elec').value) || 0;
            settings.waTemplate = document.getElementById('cfg-wa-template').value;
            saveState();
            showToast('Pengaturan & Templat WhatsApp berhasil disimpan!');
        }

        function resetWaTemplate() {
            document.getElementById('cfg-wa-template').value = DEFAULT_SETTINGS.waTemplate;
        }

        function insertTag(tag) {
            const textarea = document.getElementById('cfg-wa-template');
            const start = textarea.selectionStart,
                end = textarea.selectionEnd;
            textarea.value = textarea.value.substring(0, start) + tag + textarea.value.substring(end);
            textarea.selectionStart = textarea.selectionEnd = start + tag.length;
            textarea.focus();
        }

        window.onload = function() {
             @if(session('success_admin') || session('error_admin'))
            setTimeout(() => switchTab('admins'), 100);
            @endif
            initOnlineCloudSync();
            renderDashboard();
           
        };
    </script>
@endpush