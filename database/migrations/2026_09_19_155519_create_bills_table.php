<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bills', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->string('tenant_name');
            $table->string('no_reg');
            $table->string('period_key');
            $table->string('period');
            // Menambahkan kolom angka absolut meteran
            $table->float('water_end')->default(0); 
            $table->float('elec_end')->default(0);
            
            $table->float('water_usage')->default(0);
            $table->float('elec_usage')->default(0);
            $table->bigInteger('grand_total')->default(0);
            $table->bigInteger('paid_amount')->default(0);
            $table->string('status')->default('Belum Lunas');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bills');
    }
};