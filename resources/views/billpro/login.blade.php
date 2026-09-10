<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - PANDE MESARI Billpro</title>
    <!-- Memuat Tailwind CSS untuk desain (wajib ada) -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .font-luxury { font-family: 'Cinzel', serif; }
    </style>
</head>
<body class="bg-slate-100 min-h-screen flex items-center justify-center p-4">

    <!-- Kotak Putih Utama -->
    <div class="bg-white max-w-md w-full rounded-3xl shadow-2xl border border-slate-200 p-8 space-y-6">
        
        <!-- Logo / Header -->
        <div class="text-center space-y-2">
            <div class="w-16 h-16 bg-blue-600 rounded-2xl mx-auto flex items-center justify-center shadow-lg shadow-blue-500/30">
                <i class="fa-solid fa-building text-3xl text-white"></i>
            </div>
            <h1 class="text-2xl font-luxury font-extrabold text-slate-900 tracking-wider mt-4">PANDE MESARI</h1>
            <p class="text-xs font-bold text-slate-500 tracking-widest uppercase">Billpro Management</p>
        </div>

        <!-- Alert Error (Jika Password Salah) -->
        <!-- Alert Success -->
        @if(session('success'))
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-600 px-4 py-3 rounded-xl text-xs font-bold flex items-center gap-2">
            <i class="fa-solid fa-circle-check"></i>
            {{ session('success') }}
        </div>
        @endif

        <!-- Form Login -->
        <form action="{{ route('login.process') }}" method="POST" class="space-y-5 mt-4">
            @csrf
            
            <div>
                <label class="block text-xs font-bold text-slate-600 mb-1.5">Alamat Email</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                        <i class="fa-solid fa-envelope text-slate-400"></i>
                    </div>
                    <!-- Form dikosongkan (tanpa value bawaan) -->
                    <input type="email" name="email" placeholder="Masukkan email" required
                        class="w-full pl-10 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold text-slate-900 focus:bg-white focus:ring-2 focus:ring-blue-500 focus:outline-none transition">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-600 mb-1.5">Password</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                        <i class="fa-solid fa-lock text-slate-400"></i>
                    </div>
                    <!-- Form dikosongkan (tanpa value bawaan) -->
                    <input type="password" name="password" placeholder="Masukkan password" required
                        class="w-full pl-10 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold text-slate-900 focus:bg-white focus:ring-2 focus:ring-blue-500 focus:outline-none transition">
                </div>
            </div>

            <button type="submit" class="w-full py-3.5 bg-blue-600 hover:bg-blue-700 text-white font-extrabold rounded-xl shadow-md shadow-blue-500/20 transition flex justify-center items-center gap-2">
                Masuk ke Dashboard <i class="fa-solid fa-arrow-right"></i>
            </button>
        </form>

        <p class="text-center text-xs text-slate-500 font-semibold mt-6">
            Belum punya akun? <a href="{{ route('register') }}" class="text-blue-600 font-bold hover:underline">Daftar sekarang</a>
        </p>

    </div>

</body>
</html>