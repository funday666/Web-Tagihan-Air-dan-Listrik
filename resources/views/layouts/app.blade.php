<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'PANDE MESARI Billpro')</title>

    <!-- Tailwind CSS & FontAwesome -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap"
        rel="stylesheet">

    <!-- Firebase SDKs -->
    <script src="https://www.gstatic.com/firebasejs/11.6.1/firebase-app-compat.js"></script>
    <script src="https://www.gstatic.com/firebasejs/11.6.1/firebase-auth-compat.js"></script>
    <script src="https://www.gstatic.com/firebasejs/11.6.1/firebase-firestore-compat.js"></script>

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'sans-serif']
                    },
                    colors: {
                        brand: {
                            500: '#16a34a',
                            600: '#15803d'
                        },
                        wa: {
                            light: '#25D366'
                        }
                    }
                }
            }
        }
    </script>
    @stack('styles')
</head>

<body class="bg-slate-50 text-slate-800 min-h-screen flex selection:bg-blue-500 selection:text-white relative">

    <!-- Elemen latar belakang gelap untuk HP -->
    <div id="mobile-overlay" class="sidebar-overlay"></div>

    <!-- SIDEBAR KIRI (Desktop & Mobile) -->
    <!-- PENAMBAHAN ID: id="main-sidebar" -->
    <aside id="main-sidebar"
        class="w-64 bg-white border-r border-slate-200 hidden md:flex flex-col h-screen fixed top-0 left-0 z-40">
        <!-- Logo Area -->
        <div class="h-20 flex items-center px-6 border-b border-slate-100">
            <i class="fa-solid fa-house-chimney text-emerald-600 text-2xl"></i>
            <div class="ml-3 flex flex-col leading-tight">
                <span class="font-extrabold text-emerald-700 text-base tracking-wide">PANDE</span>
                <span class="font-extrabold text-emerald-700 text-base tracking-wide">MESARI</span>
            </div>
        </div>

        <!-- Navigation Menu -->
        <nav class="flex-1 px-4 py-6 space-y-1.5 overflow-y-auto">
            <button onclick="switchTab('dashboard')" id="nav-dashboard"
                class="w-full text-left px-4 py-3 bg-blue-600 text-white rounded-xl flex items-center gap-3 font-bold transition shadow-md shadow-blue-500/20">
                <i class="fa-solid fa-table-columns w-5 text-center"></i> Dashboard
            </button>
            <button onclick="switchTab('bills')" id="nav-bills"
                class="w-full text-left px-4 py-3 text-slate-500 hover:bg-slate-50 hover:text-slate-800 rounded-xl flex items-center gap-3 font-medium transition">
                <i class="fa-solid fa-file-invoice-dollar w-5 text-center"></i> Data Tagihan
            </button>
            <button onclick="switchTab('history')" id="nav-history"
                class="w-full text-left px-4 py-3 text-slate-500 hover:bg-slate-50 hover:text-slate-800 rounded-xl flex items-center gap-3 font-medium transition">
                <i class="fa-solid fa-clock-rotate-left w-5 text-center"></i> Histori Transaksi
            </button>
            <button onclick="switchTab('tenants')" id="nav-tenants"
                class="w-full text-left px-4 py-3 text-slate-500 hover:bg-slate-50 hover:text-slate-800 rounded-xl flex items-center gap-3 font-medium transition">
                <i class="fa-solid fa-users w-5 text-center"></i> Data Pelanggan
            </button>
            <!-- Hanya tampil jika yang login adalah admin -->
            @if (Auth::user()->role === 'admin')
                <button onclick="switchTab('admins')" id="nav-admins"
                    class="w-full text-left px-4 py-3 text-slate-500 hover:bg-slate-50 hover:text-slate-800 rounded-xl flex items-center gap-3 font-medium transition">
                    <i class="fa-solid fa-user-shield w-5 text-center"></i> Kelola Admin
                </button>
            @endif
            <button onclick="switchTab('settings')" id="nav-settings"
                class="w-full text-left px-4 py-3 text-slate-500 hover:bg-slate-50 hover:text-slate-800 rounded-xl flex items-center gap-3 font-medium transition">
                <i class="fa-solid fa-gear w-5 text-center"></i> Pengaturan
            </button>
        </nav>

        <!-- User Profile & Status (Bottom) -->
        <div class="p-4 border-t border-slate-100 space-y-4">
            <div id="online-sync-indicator"
                class="hidden items-center justify-center gap-2 px-3 py-2 rounded-lg bg-emerald-50 border border-emerald-100 text-emerald-600 text-xs font-bold">
                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span> Online Mode
            </div>
            <div class="flex items-center gap-3 px-2">
                <div
                    class="w-10 h-10 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center font-bold text-lg">
                    <i class="fa-solid fa-user"></i>
                </div>
                <div class="flex flex-col">
                    <span class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">Admin</span>
                    <!-- Mengambil nama asli user dari database -->
                    <span class="text-sm font-extrabold text-slate-800">{{ Auth::user()->name }}</span>
                </div>
            </div>
            <a href="{{ route('logout') }}"
                class="w-full flex justify-center items-center gap-2 py-2.5 rounded-xl bg-red-50 text-red-600 hover:bg-red-100 font-bold text-xs transition">
                <i class="fa-solid fa-arrow-right-from-bracket"></i> Keluar (Logout)
            </a>
        </div>
    </aside>

    <!-- HEADER MOBILE (Hanya tampil di HP) -->
    <header
        class="md:hidden fixed top-0 w-full bg-white border-b border-slate-200 h-16 flex items-center justify-between px-4 z-40">
        <div class="flex items-center gap-2">
            <i class="fa-solid fa-house-chimney text-emerald-600 text-xl"></i>
            <span class="font-extrabold text-emerald-700 text-base">PANDE MESARI</span>
        </div>
        <!-- PENAMBAHAN ID: id="mobile-menu-btn" -->
        <button id="mobile-menu-btn" class="text-slate-600 text-xl"><i class="fa-solid fa-bars"></i></button>
    </header>

    <!-- KONTEN UTAMA KANAN -->
    <main class="flex-1 md:ml-64 w-full min-h-screen pt-20 md:pt-6 px-4 md:px-8 pb-10 relative">
        <div id="toast-container" class="fixed top-6 right-8 z-50 flex flex-col gap-2 pointer-events-none"></div>
        @yield('content')
    </main>

    @stack('modals')
    @stack('scripts')

    <!-- KODE CSS DAN JAVASCRIPT UNTUK ANIMASI MENU HP -->
    <style>
        /* Animasi khusus tampilan HP (di bawah 768px) */
        @media (max-width: 768px) {
            #main-sidebar {
                position: fixed;
                top: 0;
                left: -300px;
                /* Sembunyikan di luar layar kiri */
                width: 260px;
                height: 100vh;
                background-color: #ffffff;
                z-index: 1050;
                transition: left 0.3s ease;
                box-shadow: 2px 0 15px rgba(0, 0, 0, 0.2);
                display: flex !important;
                /* Timpa class hidden dari tailwind */
            }

            /* Class ini akan dipanggil oleh JS untuk memunculkan menu */
            #main-sidebar.show-sidebar {
                left: 0;
            }

            /* Pengaturan latar belakang gelap (overlay) */
            .sidebar-overlay {
                position: fixed;
                top: 0;
                left: 0;
                width: 100vw;
                height: 100vh;
                background: rgba(0, 0, 0, 0.5);
                z-index: 1040;
                display: none;
            }

            .sidebar-overlay.show {
                display: block;
            }
        }

        /* Sembunyikan overlay jika dibuka di komputer */
        @media (min-width: 769px) {
            .sidebar-overlay {
                display: none !important;
            }
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const menuBtn = document.getElementById('mobile-menu-btn');
            const sidebar = document.getElementById('main-sidebar');
            const overlay = document.getElementById('mobile-overlay');

            if (menuBtn && sidebar) {
                // Jika tombol garis tiga diklik
                menuBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    sidebar.classList.toggle('show-sidebar');
                    overlay.classList.toggle('show');
                });

                // Jika area gelap di luar menu diklik, tutup sidebar
                overlay.addEventListener('click', function() {
                    sidebar.classList.remove('show-sidebar');
                    overlay.classList.remove('show');
                });

                // Tambahan: Tutup sidebar otomatis jika salah satu menu diklik (pindah halaman)
                const navButtons = sidebar.querySelectorAll('button');
                navButtons.forEach(btn => {
                    btn.addEventListener('click', function() {
                        if (window.innerWidth <= 768) {
                            sidebar.classList.remove('show-sidebar');
                            overlay.classList.remove('show');
                        }
                    });
                });
            }
        });
    </script>
</body>

</html>
