<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use Illuminate\Http\Request;

class TenantController extends Controller
{
    // 1. Mengambil semua data pelanggan untuk dikirim ke Javascript
    public function index()
    {
        $tenants = Tenant::orderBy('created_at', 'desc')->get();
        return response()->json($tenants);
    }

    // 2. Menyimpan data dari form modal dengan AUTO NO REGISTRASI
    public function store(Request $request)
    {
        // Validasi form (noReg tidak lagi wajib karena akan dibuat otomatis)
        $request->validate([
            'name'  => 'required',
            'phone' => 'required',
        ]);

        // ==========================================
        // LOGIKA AUTO-GENERATE NO REGISTRASI (AL-001)
        // ==========================================
        // Cari pelanggan terakhir yang nomornya berawalan AL-
        $lastTenant = Tenant::where('no_reg', 'LIKE', 'AL-%')
                            ->orderBy('id', 'desc')
                            ->first();

        if ($lastTenant) {
            // Jika sudah ada (misal AL-001), ambil angka '001', ubah ke integer, lalu tambah 1
            $lastNumber = (int) substr($lastTenant->no_reg, 3);
            $nextNumber = $lastNumber + 1;
        } else {
            // Jika database masih kosong / belum ada awalan AL-, mulai dari 1
            $nextNumber = 1;
        }

        // Format angka menjadi 3 digit (contoh: 1 menjadi 001)
        $newNoReg = 'AL-' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);


        // ==========================================
        // SIMPAN KE DATABASE
        // ==========================================
        $tenant = new Tenant();
        $tenant->no_reg     = $newNoReg; // Gunakan nomor otomatis yang baru dibuat
        $tenant->room_no    = $request->roomNo ?? '-';
        $tenant->name       = $request->name;
        $tenant->phone      = $request->phone;
        $tenant->base_rent  = $request->baseRent ?? 0;
        $tenant->last_water = $request->lastWater ?? 0;
        $tenant->last_elec  = $request->lastElec ?? 0;
        $tenant->save();

        return back()->with('success_tenant', 'Pelanggan berhasil ditambahkan dengan No Reg: ' . $newNoReg);
    }

    // 3. Menghapus data pelanggan
    public function destroy($id)
    {
        $tenant = Tenant::find($id);
        
        if ($tenant) {
            $tenant->delete();
        }
        
        return back()->with('success_tenant', 'Pelanggan berhasil dihapus!');
    }
}