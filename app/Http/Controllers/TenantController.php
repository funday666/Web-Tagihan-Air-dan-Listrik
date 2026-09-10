<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use Illuminate\Http\Request;

class TenantController extends Controller
{
    // Mengambil semua data pelanggan (Pengganti getItem localStorage)
    public function index()
    {
        $tenants = Tenant::all();
        return response()->json($tenants);
    }

    // Menyimpan pelanggan baru (Pengganti setItem localStorage)
    public function store(Request $request)
    {
        $tenant = new Tenant();
        $tenant->no_reg = $request->noReg;
        $tenant->room_no = $request->roomNo;
        $tenant->name = $request->name;
        $tenant->phone = $request->phone;
        $tenant->base_rent = $request->baseRent;
        $tenant->last_water = $request->lastWater ?? 0;
        $tenant->last_elec = $request->lastElec ?? 0;
        $tenant->save();

        return response()->json(['message' => 'Pelanggan berhasil disimpan', 'data' => $tenant]);
    }
}