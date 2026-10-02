<?php

namespace App\Http\Controllers;

use App\Models\Bill;
use Illuminate\Http\Request;

class BillController extends Controller
{
    public function index()
    {
        $bills = Bill::orderBy('created_at', 'desc')->get();
        return response()->json($bills);
    }

    public function store(Request $request)
    {
        $bill = Bill::create([
            'tenant_id' => $request->tenant_id,
            'tenant_name' => $request->tenant_name,
            'no_reg' => $request->no_reg,
            'period_key' => $request->period_key,
            'period' => $request->period,
            'water_end' => $request->water_end,
            'elec_end' => $request->elec_end,
            'water_usage' => $request->water_usage,
            'elec_usage' => $request->elec_usage,
            'grand_total' => $request->grand_total,
            'paid_amount' => 0,
            'status' => 'Belum Lunas'
        ]);

        return response()->json(['message' => 'Tagihan sukses disimpan', 'bill' => $bill], 200);
    }

    // FUNGSI BARU UNTUK EDIT/UPDATE TAGIHAN
    public function update(Request $request, $id)
    {
        $bill = Bill::findOrFail($id);
        
        // Kalkulasi ulang status lunas berdasarkan perubahan grand_total
        $newGrandTotal = $request->grand_total;
        $status = $bill->paid_amount >= $newGrandTotal ? 'Lunas' : 'Belum Lunas';
        
        $bill->update([
            'tenant_id' => $request->tenant_id,
            'tenant_name' => $request->tenant_name,
            'period_key' => $request->period_key,
            'period' => $request->period,
            'water_end' => $request->water_end,
            'elec_end' => $request->elec_end,
            'water_usage' => $request->water_usage,
            'elec_usage' => $request->elec_usage,
            'grand_total' => $newGrandTotal,
            'status' => $status
        ]);

        return response()->json(['message' => 'Tagihan berhasil diperbarui', 'bill' => $bill], 200);
    }

    public function pay(Request $request, $id)
    {
        $bill = Bill::findOrFail($id);
        $paymentAmount = $request->amount;
        
        $allBills = Bill::where('tenant_id', $bill->tenant_id)->get();
        
        foreach ($allBills as $b) {
            $b->paid_amount += $paymentAmount;
            
            if ($b->paid_amount >= $b->grand_total) {
                $b->paid_amount = $b->grand_total; 
                $b->status = 'Lunas';
            } else {
                $b->status = 'Belum Lunas';
            }
            $b->save();
        }
        
        return response()->json(['message' => 'Pembayaran berhasil dicatat & disinkronisasi!', 'bill' => $bill], 200);
    }

    public function destroy($id)
    {
        Bill::findOrFail($id)->delete();
        return response()->json(['message' => 'Tagihan terhapus'], 200);
    }
}