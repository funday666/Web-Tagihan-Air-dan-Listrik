<div id="view-admins" class="hidden space-y-6">
    <div class="flex justify-between items-end">
        <div>
            <h2 class="text-2xl font-black text-slate-900 tracking-tight">Kelola Akses Pengguna</h2>
            <p class="text-sm text-slate-500 mt-1 font-medium">Setujui atau hapus orang yang mendaftar ke sistem Billpro.</p>
        </div>
    </div>

    <!-- Notifikasi Sukses/Error -->
    @if(session('success_admin'))
    <div class="bg-emerald-50 border border-emerald-200 text-emerald-600 px-4 py-3 rounded-xl text-xs font-bold flex items-center gap-2">
        <i class="fa-solid fa-circle-check"></i> {{ session('success_admin') }}
    </div>
    @endif
    @if(session('error_admin'))
    <div class="bg-red-50 border border-red-200 text-red-600 px-4 py-3 rounded-xl text-xs font-bold flex items-center gap-2">
        <i class="fa-solid fa-circle-exclamation"></i> {{ session('error_admin') }}
    </div>
    @endif

    <div class="bg-white rounded-3xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 text-slate-500 text-[10px] uppercase tracking-wider font-bold">
                        <th class="px-5 py-4">Nama & Email</th>
                        <th class="px-5 py-4">Hak Akses</th>
                        <th class="px-5 py-4 text-center">Status</th>
                        <th class="px-5 py-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm">
                    @foreach($users as $user)
                    <tr class="hover:bg-slate-50 transition">
                        <td class="px-5 py-4">
                            <div class="font-bold text-slate-900">{{ $user->name }}</div>
                            <div class="text-xs text-slate-500">{{ $user->email }}</div>
                        </td>
                        <td class="px-5 py-4">
                            @if($user->role === 'admin')
                                <span class="px-2.5 py-1 bg-purple-100 text-purple-700 rounded-lg text-xs font-bold"><i class="fa-solid fa-crown mr-1"></i> Admin Utama</span>
                            @else
                                <span class="px-2.5 py-1 bg-slate-100 text-slate-600 rounded-lg text-xs font-bold">Kasir / User</span>
                            @endif
                        </td>
                        <td class="px-5 py-4 text-center">
                            @if($user->is_approved)
                                <span class="px-2.5 py-1 bg-emerald-100 text-emerald-700 rounded-full text-[10px] font-extrabold"><i class="fa-solid fa-check mr-1"></i> AKTIF</span>
                            @else
                                <span class="px-2.5 py-1 bg-amber-100 text-amber-700 rounded-full text-[10px] font-extrabold"><i class="fa-solid fa-clock mr-1"></i> MENUNGGU</span>
                            @endif
                        </td>
                        <td class="px-5 py-4 text-right flex justify-end gap-2">
                            <!-- Tombol Setujui (Muncul jika statusnya menunggu) -->
                            @if(!$user->is_approved)
                            <form action="{{ route('users.approve', $user->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-xs font-bold shadow-sm flex items-center gap-1.5">
                                    <i class="fa-solid fa-check"></i> Setujui
                                </button>
                            </form>
                            @endif
                            
                            <!-- Tombol Hapus (Tidak bisa menghapus diri sendiri) -->
                            @if($user->id !== Auth::id())
                            <form action="{{ route('users.delete', $user->id) }}" method="POST" onsubmit="return confirm('Yakin ingin menolak/menghapus akun ini?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="px-3 py-1.5 bg-red-50 hover:bg-red-100 text-red-600 rounded-lg text-xs font-bold shadow-sm">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </form>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>